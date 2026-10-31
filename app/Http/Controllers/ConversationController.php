<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Property;
use App\Rules\Recaptcha;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ConversationController extends Controller
{
    public function index(Request $request): View
    {
        return view('dashboard.conversations', ['conversations' => Conversation::forUser($request->user())->with(['property', 'customer', 'agent', 'messages' => fn ($query) => $query->latest()->orderByDesc('id')->limit(1)])->withCount(['messages as unread_count' => fn ($q) => $q->where('user_id', '!=', $request->user()->id)->whereNull('read_at')])->latest('updated_at')->orderByDesc('id')->paginate(15)]);
    }

    public function store(Request $request, Property $property): RedirectResponse
    {
        abort_unless($property->isPublic(), 404);
        abort_if($request->user()->id === $property->user_id, 403, 'You cannot inquire about your own property.');
        $data = $request->validate(['body' => ['required', 'string', 'max:5000'], 'g-recaptcha-response' => [new Recaptcha]]);
        $conversation = DB::transaction(function () use ($request, $property, $data): Conversation {
            $conversation = Conversation::firstOrCreate(['property_id' => $property->id, 'customer_id' => $request->user()->id], ['agent_id' => $property->user_id]);
            $conversation->messages()->create(['user_id' => $request->user()->id, 'body' => $data['body']]);
            $conversation->touch();

            return $conversation;
        });

        return redirect()->route('conversations.show', $conversation)->with('status', 'Your inquiry was sent to the agent.');
    }

    public function show(Request $request, Conversation $conversation): View
    {
        Gate::authorize('view', $conversation);
        if (in_array($request->user()->id, [$conversation->customer_id, $conversation->agent_id], true)) {
            $conversation->messages()->where('user_id', '!=', $request->user()->id)->whereNull('read_at')->update(['read_at' => now()]);
        }

        return view('dashboard.conversation', ['conversation' => $conversation->load(['property', 'customer', 'agent']), 'messages' => $conversation->messages()->with('author')->orderByDesc('id')->paginate(30)]);
    }

    public function reply(Request $request, Conversation $conversation): RedirectResponse
    {
        Gate::authorize('reply', $conversation);
        $data = $request->validate(['body' => ['required', 'string', 'max:5000']]);
        DB::transaction(function () use ($conversation, $request, $data): void {
            $conversation->messages()->create(['user_id' => $request->user()->id, 'body' => $data['body']]);
            $conversation->touch();
        });

        return redirect()->route('conversations.show', $conversation)->with('status', 'Reply sent.');
    }
}
