@extends('layouts.expert')

@section('page_title', 'Performance Overview')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
    <div class="card p-6">
        <div class="flex justify-between items-start mb-4">
            <div class="w-12 h-12 bg-indigo-500/10 rounded-2xl flex items-center justify-center text-indigo-500">
                <i class="fas fa-video text-xl"></i>
            </div>
        </div>
        <h3 class="text-gray-500 text-sm font-medium mb-1">Total Sessions</h3>
        <p class="text-3xl font-bold">{{ $stats['total_sessions'] }}</p>
    </div>

    <div class="card p-6">
        <div class="flex justify-between items-start mb-4">
            <div class="w-12 h-12 bg-amber-500/10 rounded-2xl flex items-center justify-center text-amber-500">
                <i class="fas fa-clock text-xl"></i>
            </div>
        </div>
        <h3 class="text-gray-500 text-sm font-medium mb-1">Pending Queries</h3>
        <p class="text-3xl font-bold">{{ $stats['pending'] }}</p>
    </div>

    <div class="card p-6">
        <div class="flex justify-between items-start mb-4">
            <div class="w-12 h-12 bg-emerald-500/10 rounded-2xl flex items-center justify-center text-emerald-500">
                <i class="fas fa-check-circle text-xl"></i>
            </div>
        </div>
        <h3 class="text-gray-500 text-sm font-medium mb-1">Completed</h3>
        <p class="text-3xl font-bold">{{ $stats['completed'] }}</p>
    </div>

    <div class="card p-6">
        <div class="flex justify-between items-start mb-4">
            <div class="w-12 h-12 bg-emerald-500/10 rounded-2xl flex items-center justify-center text-emerald-500">
                <i class="fas fa-indian-rupee-sign text-xl"></i>
            </div>
        </div>
        <h3 class="text-gray-500 text-sm font-medium mb-1">My Earnings</h3>
        <p class="text-3xl font-bold">₹{{ number_format($stats['revenue'], 2) }}</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 card p-6">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-bold">Recent Consultation Requests</h3>
            <a href="/expert/consultations" class="text-indigo-400 text-sm hover:underline">View all</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="text-gray-500 text-xs uppercase tracking-wider">
                        <th class="pb-4">Client</th>
                        <th class="pb-4">Type</th>
                        <th class="pb-4">Date</th>
                        <th class="pb-4">Status</th>
                        <th class="pb-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @foreach($consultations->take(5) as $consult)
                    <tr>
                        <td class="py-4">
                            <div class="flex items-center gap-3">
                                <img src="{{ $consult->user->avatar ?? 'https://i.pravatar.cc/100?u='.$consult->user_id }}" class="w-8 h-8 rounded-full" />
                                <div>
                                    <p class="text-sm font-semibold">{{ $consult->user->name }}</p>
                                    <p class="text-xs text-gray-500">Premium User</p>
                                </div>
                            </div>
                        </td>
                        <td class="py-4 text-sm">
                            @if($consult->is_audio_call)
                                <i class="fas fa-phone text-indigo-400 mr-1 text-xs"></i> Audio
                            @else
                                <i class="fas fa-video text-indigo-400 mr-1 text-xs"></i> Video
                            @endif
                        </td>
                        <td class="py-4 text-sm text-gray-400">{{ $consult->created_at->format('M d, H:i') }}</td>
                        <td class="py-4">
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-lg border {{ $consult->status == 'completed' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-amber-500/10 text-amber-400 border-amber-500/20' }}">
                                {{ strtoupper($consult->status) }}
                            </span>
                        </td>
                        <td class="py-4 text-right">
                            <button class="text-indigo-400 hover:text-white transition">
                                <i class="fas fa-arrow-right"></i>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="card p-6">
        <h3 class="text-lg font-bold mb-6">Active Status</h3>
        <div class="flex items-center justify-between p-4 bg-white/5 rounded-2xl border border-white/10 mb-6">
            <div class="flex items-center gap-3">
                <div class="w-3 h-3 rounded-full bg-emerald-500 animate-pulse"></div>
                <span class="font-medium">Accepting Consultations</span>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" checked class="sr-only peer">
                <div class="w-11 h-6 bg-white/10 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
            </label>
        </div>

        <h3 class="text-lg font-bold mb-4">My Rating</h3>
        <div class="flex items-center gap-4 p-4 bg-indigo-600/10 rounded-2xl border border-indigo-500/20">
            <div class="text-3xl font-bold text-indigo-400">{{ number_format($expert->rating, 1) }}</div>
            <div class="flex flex-col">
                <div class="flex text-amber-500 text-xs gap-0.5">
                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                </div>
                <span class="text-[10px] text-gray-500 font-bold uppercase mt-1">Excellent Performance</span>
            </div>
        </div>
    </div>
</div>
@endsection
