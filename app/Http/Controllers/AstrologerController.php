<?php

namespace App\Http\Controllers;

use App\Models\Astrologer;
use Illuminate\Http\Request;

class AstrologerController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => Astrologer::all()
        ]);
    }

    public function show($id)
    {
        $expert = Astrologer::find($id);
        if (!$expert) {
            return response()->json(['success' => false, 'message' => 'Astrologer not found']);
        }

        $reviews = \App\Models\Consultation::where('astrologer_id', $expert->id)
            ->whereNotNull('review')
            ->with('user:id,name,profile_image')
            ->latest()
            ->take(10)
            ->get()
            ->map(function($c) {
                return [
                    'user' => $c->user->name ?? 'Anonymous',
                    'avatar' => $c->user->profile_image ? (str_starts_with($c->user->profile_image, 'http') ? $c->user->profile_image : asset('storage/'.$c->user->profile_image)) : 'https://i.pravatar.cc/100?u='.$c->user_id,
                    'rating' => $c->rating,
                    'comment' => $c->review,
                    'date' => $c->created_at->diffForHumans()
                ];
            });

        $avgRating = \App\Models\Consultation::where('astrologer_id', $expert->id)->whereNotNull('rating')->avg('rating') ?: 4.9;
        $reviewsCount = \App\Models\Consultation::where('astrologer_id', $expert->id)->whereNotNull('review')->count();

        return response()->json([
            'success' => true,
            'data' => $expert,
            'reviews' => $reviews,
            'stats' => [
                'total_consults' => \App\Models\Consultation::where('astrologer_id', $expert->id)->count(),
                'rating' => round($avgRating, 1),
                'reviews_count' => $reviewsCount
            ]
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|integer',
            'name' => 'required|string',
            'specialization' => 'required|string',
            'experience' => 'required|integer',
            'languages' => 'required|string',
            'bio' => 'required|string',
            'price_per_minute' => 'required|numeric',
            'profile_image' => 'nullable|string'
        ]);

        $astrologer = Astrologer::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Astrologer added successfully',
            'data' => $astrologer
        ]);
    }

    public function updateProfile(Request $request)
    {
        $id = $request->id;
        $expert = Astrologer::where('user_id', $id)->first();
        
        if (!$expert) {
            return response()->json(['success' => false, 'message' => 'Expert not found']);
        }

        $data = $request->only(['name', 'specialization', 'experience', 'languages', 'bio', 'city', 'price_per_minute', 'profile_image']);
        
        if ($request->has('profile_image') && strpos($request->profile_image, 'data:image') !== false) {
            // Handle base64 image if needed, or assume it's already a URL
        }

        $expert->update(array_filter($data));

        return response()->json(['success' => true, 'data' => $expert]);
    }

    public function getStats(Request $request)
    {
        $id = $request->id;
        $expert = Astrologer::where('user_id', $id)->first();
        
        if (!$expert) {
            return response()->json(['success' => false, 'message' => 'Expert not found']);
        }

        return response()->json([
            'success' => true,
            'earnings_today' => \App\Models\Consultation::where('astrologer_id', $expert->id)->whereDate('created_at', today())->sum('amount_paid'),
            'total_consults' => \App\Models\Consultation::where('astrologer_id', $expert->id)->count(),
            'rating' => 4.9
        ]);
    }

    public function getDashboardData(Request $request)
    {
        $userId = $request->user_id;
        $expert = Astrologer::where('user_id', $userId)->first();
        
        if (!$expert) return response()->json(['success' => false]);

        $consultations = \App\Models\Consultation::where('astrologer_id', $expert->id)
            ->with(['user', 'messages' => function($q) {
                $q->latest()->take(1);
            }])
            ->latest()
            ->take(20)
            ->get();


        foreach ($consultations as $consult) {
            try {
                // Latest message indicator
                $lastMsg = $consult->messages->first();
                $consult->latest_message = $lastMsg ? $lastMsg->content : $consult->question;
                $consult->is_new = $lastMsg && $lastMsg->sender_id === $consult->user_id;

                if ($consult->user && $consult->user->dob) {
                    $consult->astro_data = [
                        'rasi' => $consult->user->rasi ?? 'Unknown',
                        'nakshatra' => $consult->user->nakshatra ?? 'Unknown',
                    ];
                }
            } catch (\Throwable $e) {
                \Log::error('Dashboard Horoscope Error: ' . $e->getMessage());
                $consult->astro_data = null;
            }
        }

        $month = $request->month ?: now()->month;
        $year = $request->year ?: now()->year;

        return response()->json([
            'success' => true,
            'expert_id' => $expert->id,
            'input_user_id' => $userId,
            'expert' => $expert,
            'stats' => [
                'wallet_balance' => $expert->wallet_balance,
                'earnings_today' => \App\Models\ExpertTransaction::where('astrologer_id', $expert->id)
                    ->where('type', 'credit')
                    ->whereMonth('created_at', $month)
                    ->whereYear('created_at', $year)
                    ->sum('amount'),
                'total_consults' => \App\Models\Consultation::where('astrologer_id', $expert->id)
                    ->whereMonth('created_at', $month)
                    ->whereYear('created_at', $year)
                    ->count(),
                'completed_count' => \App\Models\Consultation::where('astrologer_id', $expert->id)
                    ->where('status', 'closed')
                    ->whereMonth('created_at', $month)
                    ->whereYear('created_at', $year)
                    ->count(),
            ],
            'consultations' => $consultations
        ]);
    }

    public function getConsultations(Request $request)
    {
        $userId = $request->user_id;
        $expert = Astrologer::where('user_id', $userId)->first();
        
        if (!$expert) return response()->json(['success' => false]);

        $query = \App\Models\Consultation::where('astrologer_id', $expert->id)->with(['user', 'messages']);
        
        if ($request->filter && $request->filter !== 'all') {
            if ($request->filter === 'completed') {
                $query->where('status', 'closed');
            } elseif ($request->filter === 'pending') {
                $query->where('status', '!=', 'closed');
            } elseif ($request->filter === 'recent') {
                $query->where('created_at', '>=', now()->subDays(7));
            }
        }

        if ($request->month) {
            $query->whereMonth('created_at', $request->month);
            $query->whereYear('created_at', now()->year);
        }

        $consultations = $query->latest()->paginate(50);

        foreach ($consultations->items() as $consult) {
            // Add the latest message preview
            $lastMsg = $consult->messages->last();
            $consult->latest_message = $lastMsg?->content ?? $consult->question;
            $consult->message_count = $consult->messages->count();
            $consult->unread_count = $consult->messages->where('sender_id', $consult->user_id)->count();
            $consult->is_new = $lastMsg && $lastMsg->sender_id === $consult->user_id;

            try {
                if ($consult->user && $consult->user->dob) {
                    $consult->astro_data = [
                        'rasi' => $consult->user->rasi ?? 'Unknown',
                        'nakshatra' => $consult->user->nakshatra ?? 'Unknown',
                    ];
                }
            } catch (\Throwable $e) {
                \Log::error('Consultations Horoscope Error: ' . $e->getMessage());
                $consult->astro_data = null;
            }
        }

        return response()->json([
            'success' => true,
            'data' => $consultations
        ]);
    }
    public function getWallet(Request $request)
    {
        $userId = $request->user_id;
        $expert = Astrologer::where('user_id', $userId)->first();
        if (!$expert) return response()->json(['success' => false]);

        $transactions = \App\Models\ExpertTransaction::where('astrologer_id', $expert->id)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'balance' => $expert->wallet_balance,
            'transactions' => $transactions
        ]);
    }

    public function requestWithdrawal(Request $request)
    {
        $userId = $request->user_id;
        $expert = Astrologer::where('user_id', $userId)->first();
        if (!$expert) return response()->json(['success' => false]);

        $amount = $request->amount;
        if ($expert->wallet_balance < $amount) {
            return response()->json(['success' => false, 'message' => 'Insufficient balance']);
        }

        // Create a pending transaction
        \App\Models\ExpertTransaction::create([
            'astrologer_id' => $expert->id,
            'amount' => $amount,
            'type' => 'debit',
            'description' => 'Withdrawal Request',
            'status' => 'pending'
        ]);

        // Deduct balance
        $expert->decrement('wallet_balance', $amount);

        return response()->json(['success' => true, 'message' => 'Withdrawal request submitted']);
    }
}
