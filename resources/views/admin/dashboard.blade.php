@extends('layouts.admin')

@section('page_title', 'Analytics Overview')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
    <div class="card p-6">
        <div class="flex justify-between items-start mb-4">
            <div class="w-12 h-12 bg-indigo-500/10 rounded-2xl flex items-center justify-center text-indigo-500">
                <i class="fas fa-users text-xl"></i>
            </div>
            <span class="text-xs font-bold text-emerald-400 bg-emerald-500/10 px-2 py-1 rounded-lg">+12%</span>
        </div>
        <h3 class="text-gray-500 text-sm font-medium mb-1">Total Users</h3>
        <p class="text-3xl font-bold">{{ $stats['users'] }}</p>
    </div>

    <div class="card p-6">
        <div class="flex justify-between items-start mb-4">
            <div class="w-12 h-12 bg-amber-500/10 rounded-2xl flex items-center justify-center text-amber-500">
                <i class="fas fa-user-tie text-xl"></i>
            </div>
        </div>
        <h3 class="text-gray-500 text-sm font-medium mb-1">Active Astrologers</h3>
        <p class="text-3xl font-bold">{{ $stats['astrologers'] }}</p>
    </div>

    <div class="card p-6">
        <div class="flex justify-between items-start mb-4">
            <div class="w-12 h-12 bg-emerald-500/10 rounded-2xl flex items-center justify-center text-emerald-500">
                <i class="fas fa-indian-rupee-sign text-xl"></i>
            </div>
            <span class="text-xs font-bold text-emerald-400 bg-emerald-500/10 px-2 py-1 rounded-lg">+24%</span>
        </div>
        <h3 class="text-gray-500 text-sm font-medium mb-1">Total Revenue</h3>
        <p class="text-3xl font-bold">₹{{ number_format($stats['revenue'], 2) }}</p>
    </div>

    <div class="card p-6">
        <div class="flex justify-between items-start mb-4">
            <div class="w-12 h-12 bg-rose-500/10 rounded-2xl flex items-center justify-center text-rose-500">
                <i class="fas fa-clock text-xl"></i>
            </div>
        </div>
        <h3 class="text-gray-500 text-sm font-medium mb-1">Pending Questions</h3>
        <p class="text-3xl font-bold">{{ $stats['pending_q'] }}</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 card p-6">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-bold">Recent User Activity</h3>
            <button class="text-indigo-400 text-sm hover:underline">View all</button>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="text-gray-500 text-xs uppercase tracking-wider">
                        <th class="pb-4">User</th>
                        <th class="pb-4">Role</th>
                        <th class="pb-4">Joined</th>
                        <th class="pb-4">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @foreach($recent_users as $user)
                    <tr>
                        <td class="py-4">
                            <div class="flex items-center gap-3">
                                <img src="{{ $user->avatar ?? 'https://i.pravatar.cc/100?u='.$user->id }}" class="w-8 h-8 rounded-full" />
                                <div>
                                    <p class="text-sm font-semibold">{{ $user->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $user->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="py-4"><span class="text-xs font-medium px-2 py-1 rounded-lg {{ $user->role == 'admin' ? 'bg-rose-500/10 text-rose-400' : 'bg-indigo-500/10 text-indigo-400' }}">{{ strtoupper($user->role) }}</span></td>
                        <td class="py-4 text-sm text-gray-400">{{ $user->created_at->format('M d, Y') }}</td>
                        <td class="py-4"><span class="w-2 h-2 rounded-full bg-emerald-500 inline-block mr-2"></span> Active</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="card p-6">
        <h3 class="text-lg font-bold mb-6">Quick Actions</h3>
        <div class="space-y-3">
            <a href="/admin/astrologers" class="flex items-center gap-4 p-4 bg-white/5 rounded-2xl hover:bg-white/10 transition">
                <div class="w-10 h-10 bg-indigo-500/20 rounded-xl flex items-center justify-center text-indigo-400">
                    <i class="fas fa-user-plus"></i>
                </div>
                <span class="font-medium">Add New Astrologer</span>
            </a>
            <a href="/admin/plans" class="flex items-center gap-4 p-4 bg-white/5 rounded-2xl hover:bg-white/10 transition">
                <div class="w-10 h-10 bg-emerald-500/20 rounded-xl flex items-center justify-center text-emerald-400">
                    <i class="fas fa-plus"></i>
                </div>
                <span class="font-medium">Create Pricing Plan</span>
            </a>
            <a href="/admin/consultations" class="flex items-center gap-4 p-4 bg-white/5 rounded-2xl hover:bg-white/10 transition">
                <div class="w-10 h-10 bg-rose-500/20 rounded-xl flex items-center justify-center text-rose-400">
                    <i class="fas fa-video"></i>
                </div>
                <span class="font-medium">Manage Live Calls</span>
            </a>
        </div>
    </div>
</div>
@endsection
