<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Contact;
use App\Models\EmailTemplate;
use App\Models\EmailTemplateAttachment;
use App\Models\Lead;
use App\Services\TemplateVariableResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EmailTemplateController extends Controller
{
    public function index()
    {
        return view('email_templates.index');
    }

    public function create()
    {
        return view('email_templates.form', ['template' => null]);
    }

    public function edit($id)
    {
        // Plain $id + a manual lookup, not route-model-binding — see the
        // note on update() below for why.
        $template = EmailTemplate::with('attachments')->findOrFail($id);

        return view('email_templates.form', ['template' => $template]);
    }

    public function data()
    {
        $templates = EmailTemplate::with('campaigns:id,email_template_id')
            ->orderByDesc('id')
            ->get()
            ->map(fn (EmailTemplate $template) => [
                'id' => $template->id,
                'name' => $template->name,
                'subject' => $template->subject,
                'body' => $template->body,
                'campaigns_count' => $template->campaigns->count(),
                'updated_at' => $template->updated_at,
            ]);

        return response()->json(['status' => true, 'data' => $templates]);
    }

    /**
     * The token catalog behind the "insert variable" picker in the editor,
     * plus a resolved preview against a real (or fallback sample) record so
     * users can see what a template will actually look like.
     */
    public function variables(Request $request)
    {
        $audienceType = $request->get('audience_type', 'leads');

        return response()->json([
            'status' => true,
            'tokens' => TemplateVariableResolver::tokensFor($audienceType),
        ]);
    }

    public function preview(Request $request, TemplateVariableResolver $resolver)
    {
        $request->validate([
            'subject' => 'required|string',
            'body' => 'required|string',
            'audience_type' => 'required|in:leads,contacts,companies',
        ]);

        $user = Auth::guard('web')->user();
        $elevated = $user->hasElevatedAccess();

        $sample = match ($request->audience_type) {
            'leads' => Lead::when(! $elevated, fn ($q) => $q->where('assigned_to', $user->id))->latest('id')->first(),
            'contacts' => Contact::when(! $elevated, fn ($q) => $q->where('owner_id', $user->id))->latest('id')->first(),
            'companies' => Company::when(! $elevated, fn ($q) => $q->where('owner_id', $user->id))->latest('id')->first(),
        };

        if (! $sample) {
            return response()->json([
                'status' => true,
                'subject' => $request->subject,
                'body' => $request->body,
                'note' => 'No sample record yet — showing the template with variables unresolved.',
            ]);
        }

        $context = $resolver->contextFor($sample, Auth::guard('web')->user()?->tenant);

        return response()->json([
            'status' => true,
            'subject' => $resolver->resolve($request->subject, $context),
            'body' => $resolver->resolve($request->body, $context),
        ]);
    }

    /**
     * TinyMCE's images_upload_handler target — inserting an image into the
     * editor uploads it here and gets back a plain URL, rather than the
     * user having to already have the image hosted somewhere else. Stored
     * on the public disk (not the private 'local' disk used for
     * attachments below): recipients' mail clients fetch this image over
     * plain HTTP when they open the email, so it has to be a real public
     * URL, not something only this app's own server can read.
     */
    public function uploadImage(Request $request)
    {
        $request->validate([
            'file' => 'required|image|mimes:jpg,jpeg,png,gif,webp|max:5120',
        ]);

        $file = $request->file('file');
        $fileName = Str::random(20).'.'.$file->getClientOriginalExtension();
        $directory = public_path('uploads/email-templates/images');

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $file->move($directory, $fileName);

        return response()->json([
            'status' => true,
            'url' => asset('uploads/email-templates/images/'.$fileName),
        ]);
    }

    /**
     * Adds one file (a PDF brochure, a quotation doc, ...) that gets sent
     * as a real email attachment with every send of this template — see
     * CampaignSender::send(), which reads these rows and attaches them to
     * each CampaignMail. Requires the template to already exist (needs its
     * id as the foreign key), so this route only appears on the edit page.
     */
    public function uploadAttachment(Request $request, $id)
    {
        $template = EmailTemplate::findOrFail($id);

        $request->validate([
            'file' => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png,webp,doc,docx,xls,xlsx,csv,txt',
        ]);

        $file = $request->file('file');
        $storedPath = $file->store('email-template-attachments/'.$template->tenant_id.'/'.$template->id, 'local');

        $attachment = EmailTemplateAttachment::create([
            'tenant_id' => $template->tenant_id,
            'email_template_id' => $template->id,
            'original_name' => $file->getClientOriginalName(),
            'stored_path' => $storedPath,
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Attachment added',
            'data' => [
                'id' => $attachment->id,
                'original_name' => $attachment->original_name,
                'size' => $attachment->size,
            ],
        ]);
    }

    public function destroyAttachment($attachmentId)
    {
        $attachment = EmailTemplateAttachment::findOrFail($attachmentId);
        Storage::disk('local')->delete($attachment->stored_path);
        $attachment->delete();

        return response()->json(['status' => true, 'message' => 'Attachment removed']);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        $template = EmailTemplate::create($data + ['created_by' => Auth::guard('web')->id()]);

        return response()->json(['status' => true, 'message' => 'Template created', 'data' => $template]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        // Plain $id + a manual lookup here, not route-model-binding: implicit
        // binding resolves during SubstituteBindings, which runs before
        // ActivateTenantDatabase — too early for a model whose connection is
        // only known once the tenant DB is active for this request.
        $emailTemplate = EmailTemplate::findOrFail($id);
        $emailTemplate->update($data);

        return response()->json(['status' => true, 'message' => 'Template updated', 'data' => $emailTemplate]);
    }

    public function destroy($id)
    {
        $emailTemplate = EmailTemplate::findOrFail($id);

        if ($emailTemplate->campaigns()->exists()) {
            return response()->json([
                'status' => false,
                'message' => 'This template is used by one or more campaigns and cannot be deleted.',
            ], 422);
        }

        foreach ($emailTemplate->attachments as $attachment) {
            Storage::disk('local')->delete($attachment->stored_path);
        }

        $emailTemplate->delete();

        return response()->json(['status' => true, 'message' => 'Template deleted']);
    }
}
