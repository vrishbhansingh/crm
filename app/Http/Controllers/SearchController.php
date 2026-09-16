<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\Order;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SearchController extends Controller
{
    /**
     * Cross-module lookup for the top-bar search box. Each module is
     * included only when the user has that module's own view permission,
     * and scoped to their own records the same way its full listing page
     * scopes them (hasElevatedAccess() ? everything : owned/assigned only)
     * — so search never surfaces something the module's own page would hide.
     */
    public function search(Request $request)
    {
        $term = trim((string) $request->get('q', ''));

        if (mb_strlen($term) < 2) {
            return response()->json(['results' => []]);
        }

        $user = Auth::guard('web')->user();
        $like = '%'.$term.'%';
        $results = [];
        $perType = 5;

        if ($user->can('leads.view')) {
            $query = Lead::query();
            if (! $user->hasElevatedAccess()) {
                $query->where('assigned_to', $user->id);
            }
            $query->where(function ($q) use ($like) {
                $q->where('name', 'like', $like)
                    ->orWhere('company_name', 'like', $like)
                    ->orWhere('phone', 'like', $like)
                    ->orWhere('email', 'like', $like)
                    ->orWhere('lead_number', 'like', $like);
            });

            foreach ($query->latest('id')->limit($perType)->get() as $lead) {
                $results[] = [
                    'type' => 'Lead',
                    'icon' => 'fa-user-plus',
                    'title' => $lead->name ?: ($lead->company_name ?: 'Lead #'.$lead->lead_number),
                    'subtitle' => trim(collect([$lead->company_name, $lead->phone ?: $lead->email])->filter()->implode(' · ')),
                    'url' => route('leads.show', $lead->id),
                ];
            }
        }

        if ($user->can('deals.view')) {
            $query = Deal::with('company:id,name');
            if (! $user->hasElevatedAccess()) {
                $query->where('owner_id', $user->id);
            }
            $query->where('name', 'like', $like);

            foreach ($query->latest('id')->limit($perType)->get() as $deal) {
                $results[] = [
                    'type' => 'Deal',
                    'icon' => 'fa-handshake-o',
                    'title' => $deal->name,
                    'subtitle' => trim(collect([$deal->company?->name, $deal->currency.' '.number_format((float) $deal->amount, 0)])->filter()->implode(' · ')),
                    'url' => route('deals.show', $deal->id),
                ];
            }
        }

        if ($user->can('companies.view')) {
            $query = Company::query();
            if (! $user->hasElevatedAccess()) {
                $query->where('owner_id', $user->id);
            }
            $query->where(function ($q) use ($like) {
                $q->where('name', 'like', $like)
                    ->orWhere('email', 'like', $like)
                    ->orWhere('phone', 'like', $like);
            });

            foreach ($query->latest('id')->limit($perType)->get() as $company) {
                $results[] = [
                    'type' => 'Company',
                    'icon' => 'fa-building-o',
                    'title' => $company->name,
                    'subtitle' => trim(collect([$company->city, $company->phone ?: $company->email])->filter()->implode(' · ')),
                    'url' => route('companies.show', $company->id),
                ];
            }
        }

        if ($user->can('contacts.view')) {
            $query = Contact::query();
            if (! $user->hasElevatedAccess()) {
                $query->where('owner_id', $user->id);
            }
            $query->where(function ($q) use ($like) {
                $q->where('name', 'like', $like)
                    ->orWhere('email', 'like', $like)
                    ->orWhere('phone', 'like', $like);
            });

            foreach ($query->latest('id')->limit($perType)->get() as $contact) {
                $results[] = [
                    'type' => 'Contact',
                    'icon' => 'fa-address-book-o',
                    'title' => $contact->name,
                    'subtitle' => trim(collect([$contact->designation, $contact->phone ?: $contact->email])->filter()->implode(' · ')),
                    'url' => route('contacts.index'),
                ];
            }
        }

        if ($user->can('orders.view')) {
            $query = Order::query();
            if (! $user->hasElevatedAccess()) {
                $query->where('user_id', $user->id);
            }
            $query->where('order_number', 'like', $like);

            foreach ($query->latest('id')->limit($perType)->get() as $order) {
                $results[] = [
                    'type' => 'Order',
                    'icon' => 'fa-file-text-o',
                    'title' => $order->order_number ?: ('Order #'.$order->id),
                    'subtitle' => trim(($order->currency ?: '').' '.number_format((float) $order->total_amount, 0).' · '.ucfirst((string) $order->order_status)),
                    'url' => route('orders.show', $order->id),
                ];
            }
        }

        if ($user->can('tasks.view')) {
            $query = Task::query();
            if (! $user->hasElevatedAccess() && ! $user->hasRole('Support')) {
                $query->where(fn ($q) => $q->where('assigned_to', $user->id)->orWhere('created_by', $user->id));
            }
            $query->where('title', 'like', $like);

            foreach ($query->latest('id')->limit($perType)->get() as $task) {
                $results[] = [
                    'type' => 'Task',
                    'icon' => 'fa-check-square-o',
                    'title' => $task->title,
                    'subtitle' => ucfirst((string) $task->priority).' priority · '.ucfirst(str_replace('_', ' ', (string) $task->status)),
                    'url' => route('tasks.index'),
                ];
            }
        }

        return response()->json(['results' => $results]);
    }
}
