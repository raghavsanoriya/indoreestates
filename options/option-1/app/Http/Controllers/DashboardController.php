<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\Property;
use App\Models\PropertyView;
use App\Models\Review;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'keyword' => ['nullable', 'string', 'max:160'],
            'status' => ['nullable', 'in:draft,pending,published,rejected'],
            'from' => ['nullable', 'date', 'before_or_equal:today'],
            'to' => ['nullable', 'date', 'before_or_equal:today'],
            'period' => ['nullable', 'in:day,week,month,year'],
            'chart_from' => ['nullable', 'date', 'after_or_equal:'.today()->subYears(5)->toDateString(), 'before_or_equal:today'],
            'chart_to' => ['nullable', 'date', 'after_or_equal:'.today()->subYears(5)->toDateString(), 'before_or_equal:today'],
        ]);
        foreach (['from' => 'to', 'chart_from' => 'chart_to'] as $from => $to) {
            if (! empty($filters[$from]) && ! empty($filters[$to]) && Carbon::parse($filters[$from])->gt(Carbon::parse($filters[$to]))) {
                throw ValidationException::withMessages([$to => 'The end date must be on or after the start date.']);
            }
        }
        $user = $request->user();
        $listings = Property::query()->when(! $user->isAdmin(), fn ($query) => $query->where('user_id', $user->id));
        $filteredListings = (clone $listings)->with(['photos', 'owner']);
        if (! empty($filters['keyword'])) {
            $filteredListings->where('title', 'like', '%'.$filters['keyword'].'%');
        }
        if (! empty($filters['status'])) {
            $filteredListings->where('status', $filters['status']);
        }
        if (! empty($filters['from'])) {
            $filteredListings->whereDate('created_at', '>=', $filters['from']);
        }
        if (! empty($filters['to'])) {
            $filteredListings->whereDate('created_at', '<=', $filters['to']);
        }
        $reviews = Review::query()->when(! $user->isAdmin(), fn ($query) => $query->where(fn ($query) => $query->where('user_id', $user->id)->orWhereHas('property', fn ($query) => $query->where('user_id', $user->id))));
        $period = $filters['period'] ?? 'day';
        $chartTo = Carbon::parse($filters['chart_to'] ?? today())->startOfDay();
        $chartFrom = ! empty($filters['chart_from']) ? Carbon::parse($filters['chart_from'])->startOfDay() : match ($period) {
            'week' => $chartTo->copy()->startOfWeek()->subWeeks(11),
            'month' => $chartTo->copy()->startOfMonth()->subMonths(11),
            'year' => $chartTo->copy()->startOfYear()->subYears(4),
            default => $chartTo->copy()->subDays(13),
        };
        if ($chartFrom->gt($chartTo)) {
            throw ValidationException::withMessages(['chart_to' => 'The end date must be on or after the start date.']);
        }
        $chart = PropertyView::whereIn('property_id', (clone $listings)->select('id'))->whereBetween('day', [$chartFrom->toDateString(), $chartTo->toDateString()])->select('day')->selectRaw('sum(views) as total')->groupBy('day')->pluck('total', 'day');
        $cursor = match ($period) {
            'week' => $chartFrom->copy()->startOfWeek(),
            'month' => $chartFrom->copy()->startOfMonth(),
            'year' => $chartFrom->copy()->startOfYear(),
            default => $chartFrom->copy(),
        };
        $days = collect();
        while ($cursor->lte($chartTo)) {
            $next = match ($period) {
                'week' => $cursor->copy()->addWeek(),
                'month' => $cursor->copy()->addMonth(),
                'year' => $cursor->copy()->addYear(),
                default => $cursor->copy()->addDay(),
            };
            $start = $cursor->toDateString();
            $end = $next->toDateString();
            $days->push(['day' => $cursor->format(match ($period) {
                'year' => 'Y', 'month' => 'M Y', default => 'd M'
            }), 'total' => (int) $chart->filter(fn ($total, $day) => $day >= $start && $day < $end)->sum()]);
            $cursor = $next;
        }

        return view('dashboard.index', [
            'counts' => ['Listings' => (clone $listings)->count(), 'Pending approval' => (clone $listings)->where('status', 'pending')->count(), 'Favorites' => $user->isAdmin() ? DB::table('favorites')->count() : $user->favorites()->count(), 'Reviews' => (clone $reviews)->count(), 'Property views' => (int) PropertyView::whereIn('property_id', (clone $listings)->select('id'))->sum('views')],
            'properties' => $filteredListings->latest()->orderByDesc('id')->paginate(5)->withQueryString(),
            'conversations' => Conversation::forUser($user)->with(['property', 'customer', 'agent', 'messages' => fn ($query) => $query->latest()->orderByDesc('id')->limit(1)])->latest('updated_at')->orderByDesc('id')->limit(4)->get(),
            'recentReviews' => (clone $reviews)->with(['author', 'property'])->latest()->limit(5)->get(),
            'days' => $days, 'period' => $period, 'chartFrom' => $chartFrom, 'chartTo' => $chartTo,
        ]);
    }

    public function unread(Request $request): JsonResponse
    {
        return response()->json(['unread' => Message::whereHas('conversation', fn ($q) => $q->where(fn ($q) => $q->where('customer_id', $request->user()->id)->orWhere('agent_id', $request->user()->id)))->where('user_id', '!=', $request->user()->id)->whereNull('read_at')->count()]);
    }
}
