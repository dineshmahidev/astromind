@extends('layouts.admin')

@section('page_title', 'User Management')

@section('content')
<div class="card p-6">
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-lg font-bold">Platform Users</h3>
        <div class="flex gap-4">
            <input type="text" placeholder="Search users..." class="bg-white/5 border border-white/10 rounded-xl px-4 py-2 text-sm focus:outline-none focus:border-indigo-500" />
            <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl text-sm font-semibold transition">Export CSV</button>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="text-gray-500 text-xs uppercase tracking-wider border-b border-white/5">
                    <th class="pb-4">User</th>
                    <th class="pb-4">Wallet</th>
                    <th class="pb-4">Premium</th>
                    <th class="pb-4">Joined</th>
                    <th class="pb-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @foreach($users as $user)
                <tr>
                    <td class="py-4">
                        <div class="flex items-center gap-3">
                            @php
                                $avatarUrl = $user->avatar;
                                // If user is an astrologer and has a profile image, use it
                                if ($user->role === 'astrologer' && $user->astrologer && $user->astrologer->profile_image) {
                                    $avatarUrl = $user->astrologer->profile_image;
                                }

                                if ($avatarUrl && !str_starts_with($avatarUrl, 'http')) {
                                    $avatarUrl = asset('storage/' . $avatarUrl);
                                }
                            @endphp
                            <img src="{{ $avatarUrl ?? 'https://i.pravatar.cc/100?u='.$user->id }}" class="w-10 h-10 rounded-full border border-white/10 object-cover" />
                            <div>
                                <div class="flex items-center gap-2">
                                    <p class="text-sm font-bold text-white">{{ $user->name }}</p>
                                    @if($user->role === 'astrologer')
                                        <span class="bg-indigo-500/20 text-indigo-300 text-[9px] font-bold px-1.5 py-0.5 rounded border border-indigo-500/30 uppercase tracking-tighter">Astrologer</span>
                                    @elseif($user->role === 'admin')
                                        <span class="bg-rose-500/20 text-rose-300 text-[9px] font-bold px-1.5 py-0.5 rounded border border-rose-500/30 uppercase tracking-tighter">Admin</span>
                                    @else
                                        <span class="bg-cyan-500/20 text-cyan-300 text-[9px] font-bold px-1.5 py-0.5 rounded border border-cyan-500/30 uppercase tracking-tighter">User</span>
                                    @endif
                                </div>
                                <p class="text-xs text-gray-500">{{ $user->email }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="py-4">
                        <span class="text-sm font-semibold text-emerald-400">₹{{ number_format($user->wallet_balance, 2) }}</span>
                    </td>
                    <td class="py-4">
                        @if($user->is_premium)
                            <span class="bg-amber-500/10 text-amber-500 text-[10px] font-bold px-2 py-1 rounded-full border border-amber-500/20">PREMIUM</span>
                        @else
                            <span class="text-gray-600 text-[10px] font-bold">FREE</span>
                        @endif
                    </td>
                    <td class="py-4 text-sm text-gray-400">{{ $user->created_at->format('M d, Y') }}</td>
                    <td class="py-4 text-right">
                        <div class="flex justify-end gap-2">
                            <button class="p-2 hover:bg-white/10 rounded-lg text-indigo-400 transition" title="Edit User">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form action="/admin/users/{{ $user->id }}" method="POST" onsubmit="return confirm('Are you sure?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 hover:bg-red-500/10 rounded-lg text-red-400 transition" title="Delete User">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $users->links() }}
    </div>
</div>
@endsection
