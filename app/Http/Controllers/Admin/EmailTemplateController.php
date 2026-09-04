<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Contact;
use App\Models\EmailTemplate;
use App\Models\Lead;
use App\Services\TemplateVariableResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmailTemplateController extends Controller
{
    public function index()
    {
        return view('email_templates.index');
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

    public function update(Request $request, EmailTemplate $emailTemplate)
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        $emailTemplate->update($data);

        return response()->json(['status' => true, 'message' => 'Template updated', 'data' => $emailTemplate]);
    }

    public function destroy(EmailTemplate $emailTemplate)
    {
        if ($emailTemplate->campaigns()->exists()) {
            return response()->json([
                'status' => false,
                'message' => 'This template is used by one or more campaigns and cannot be deleted.',
            ], 422);
        }

        $emailTemplate->delete();

        return response()->json(['status' => true, 'message' => 'Template deleted']);
    }
}
