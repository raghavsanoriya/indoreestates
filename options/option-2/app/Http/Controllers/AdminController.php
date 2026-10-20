<?php

namespace App\Http\Controllers;

use App\Models\ContactSubmission;
use App\Models\Subscriber;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AdminController extends Controller
{
    public function users(): View
    {
        Gate::authorize('admin');

        return view('dashboard.users', ['users' => User::orderBy('name')->paginate(20)]);
    }

    public function role(Request $request, User $user): RedirectResponse
    {
        Gate::authorize('admin');
        abort_if($user->isAdmin(), 403, 'Administrator roles cannot be changed here.');
        $data = $request->validate(['role' => ['required', 'in:customer,agent']]);
        $user->role = $data['role'];
        $user->save();

        return back()->with('status', 'User access updated.');
    }

    public function inbox(): View
    {
        Gate::authorize('admin');

        return view('dashboard.inbox', ['contacts' => ContactSubmission::latest()->paginate(15), 'subscribers' => Subscriber::latest()->paginate(15, ['*'], 'subscriber_page')]);
    }

    public function handled(ContactSubmission $contact): RedirectResponse
    {
        Gate::authorize('admin');
        $contact->update(['handled' => true]);

        return back()->with('status', 'Contact submission marked handled.');
    }
}
