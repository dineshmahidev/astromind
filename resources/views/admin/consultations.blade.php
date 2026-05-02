@extends('layouts.admin')

@section('page_title', 'Consultation Hub')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-4 gap-6 h-[calc(100vh-180px)]">
    <!-- Question List -->
    <div class="lg:col-span-1 card flex flex-col overflow-hidden">
        <div class="p-4 border-b border-white/10 bg-white/5">
            <h3 class="font-bold mb-2">Recent Queries</h3>
            <div class="flex gap-2">
                <button class="flex-1 text-[10px] bg-indigo-500 text-white py-1 rounded font-bold uppercase">All</button>
                <button class="flex-1 text-[10px] bg-white/5 text-gray-500 py-1 rounded font-bold uppercase">Pending</button>
            </div>
        </div>
        <div class="flex-1 overflow-y-auto">
            @foreach($consultations as $chat)
            <div class="p-4 border-b border-white/5 hover:bg-white/5 cursor-pointer transition {{ $loop->first ? 'bg-indigo-500/10' : '' }}">
                <div class="flex justify-between items-start mb-1">
                    <span class="text-xs font-bold text-white">{{ $chat->user->name }}</span>
                    <span class="text-[10px] text-gray-500">{{ $chat->created_at->diffForHumans() }}</span>
                </div>
                <p class="text-xs text-gray-400 truncate">{{ $chat->question }}</p>
                @if($chat->status == 'pending')
                    <span class="mt-2 inline-block w-2 h-2 rounded-full bg-rose-500"></span>
                @endif
            </div>
            @endforeach
        </div>
    </div>

    <!-- Chat/Dashboard View -->
    <div class="lg:col-span-3 card flex flex-col overflow-hidden">
        @if(count($consultations) > 0)
        @php $active = $consultations->first(); @endphp
        <div class="p-6 border-b border-white/10 bg-white/5 flex justify-between items-center">
            <div class="flex items-center gap-4">
                <img src="{{ $active->user->avatar ?? 'https://i.pravatar.cc/100?u='.$active->user->id }}" class="w-12 h-12 rounded-full" />
                <div>
                    <h3 class="font-bold text-lg">{{ $active->user->name }}</h3>
                    <p class="text-xs text-emerald-400">Paid ₹{{ number_format($active->amount_paid, 2) }}</p>
                </div>
            </div>
            <div class="flex gap-3">
                @if($active->is_video_call)
                    <button class="bg-rose-600 hover:bg-rose-700 text-white px-6 py-2 rounded-xl text-sm font-bold transition flex items-center gap-2">
                        <i class="fas fa-video"></i> Start Video Call
                    </button>
                @endif
                <button class="bg-white/10 hover:bg-white/20 text-white px-4 py-2 rounded-xl text-sm font-bold transition">
                    Assign Astrologer
                </button>
            </div>
        </div>

        <div class="flex-1 p-8 overflow-y-auto space-y-6">
            <!-- User Question -->
            <div class="flex justify-start">
                <div class="bg-white/10 rounded-2xl p-6 max-w-xl border border-white/5">
                    <p class="text-sm font-bold text-indigo-400 mb-2 uppercase tracking-tighter">Question from User</p>
                    <p class="text-white leading-relaxed">{{ $active->question }}</p>
                </div>
            </div>

            <!-- Astrologer Answer (if exists) -->
            @if($active->answer)
            <div class="flex justify-end">
                <div class="bg-indigo-600 rounded-2xl p-6 max-w-xl shadow-xl shadow-indigo-900/20">
                    <p class="text-sm font-bold text-indigo-200 mb-2 uppercase tracking-tighter">Response from {{ $active->astrologer->name ?? 'Expert' }}</p>
                    <p class="text-white leading-relaxed">{{ $active->answer }}</p>
                </div>
            </div>
            @endif
        </div>

        <div class="p-6 border-t border-white/10 bg-white/5">
            <form action="/admin/consultations/{{ $active->id }}/reply" method="POST" class="flex gap-4">
                @csrf
                <textarea class="flex-1 bg-white/5 border border-white/10 rounded-2xl px-6 py-4 text-sm focus:outline-none focus:border-indigo-500 h-20" placeholder="Type the expert's response here..."></textarea>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-4 rounded-2xl font-bold transition self-end">
                    Send Response
                </button>
            </form>
        </div>
        @else
        <div class="flex-1 flex flex-col items-center justify-center text-center p-10">
            <div class="w-20 h-20 bg-white/5 rounded-full flex items-center justify-center text-gray-700 mb-6">
                <i class="fas fa-comments text-4xl"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-500">No active consultations</h3>
            <p class="text-gray-600 max-w-sm mt-2">New user queries and video call requests will appear here in real-time.</p>
        </div>
        @endif
    </div>
</div>
@endsection
