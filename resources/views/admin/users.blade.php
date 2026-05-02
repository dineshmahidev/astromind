@extends('layouts.admin')

@section('page_title', 'User Management')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- User List -->
    <div class="lg:col-span-2 card p-6">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-bold text-white">Platform Users</h3>
            <div class="flex gap-4">
                <input type="text" placeholder="Search users..." class="bg-white/5 border border-white/10 rounded-xl px-4 py-2 text-sm text-white focus:outline-none focus:border-indigo-500" />
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
                                <button onclick="openEditModal({{ json_encode($user) }})" class="p-2 hover:bg-white/10 rounded-lg text-indigo-400 transition" title="Edit User">
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
        <div class="mt-6">{{ $users->links() }}</div>
    </div>

    <!-- Create User Form -->
    <div class="card p-6 h-fit sticky top-8">
        <h3 class="text-lg font-bold text-white mb-6">Register New User</h3>
        <form action="/admin/users" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Full Name</label>
                <input type="text" name="name" required class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-indigo-500" placeholder="John Doe" />
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Email Address</label>
                <input type="email" name="email" required class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-indigo-500" placeholder="john@example.com" />
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Initial Wallet Balance (₹)</label>
                <input type="number" step="0.01" name="wallet_balance" value="0.00" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-indigo-500" />
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Password</label>
                <input type="password" name="password" required class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-indigo-500" placeholder="Min 6 characters" />
            </div>
            <div class="pt-2">
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-4 rounded-2xl shadow-lg shadow-indigo-500/20 transition-all active:scale-95">Register User</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit User Modal -->
<div id="editModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-black/80 transition-opacity" aria-hidden="true" onclick="closeEditModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-[#0d0d1f] rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-white/10">
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="px-6 py-6">
                    <h3 class="text-lg font-bold text-white mb-6">Edit User Profile</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Full Name</label>
                            <input type="text" name="name" id="edit_name" required class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-indigo-500" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Email Address</label>
                            <input type="email" name="email" id="edit_email" required class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-indigo-500" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Wallet Balance (₹)</label>
                            <input type="number" step="0.01" name="wallet_balance" id="edit_wallet" required class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-indigo-500" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-2 text-rose-400">New Password (Optional)</label>
                            <input type="password" name="password" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-indigo-500" placeholder="Leave blank to keep current" />
                        </div>
                        <div class="flex items-center gap-3 p-4 bg-white/5 rounded-xl border border-white/10">
                            <input type="checkbox" name="is_premium" id="edit_premium" class="w-4 h-4 rounded border-white/10 bg-white/5 text-indigo-600 focus:ring-indigo-500" />
                            <label for="edit_premium" class="text-sm font-semibold text-white">Premium Account</label>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 bg-white/5 flex justify-end gap-3">
                    <button type="button" onclick="closeEditModal()" class="px-4 py-2 text-sm font-semibold text-gray-400 hover:text-white transition">Cancel</button>
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-xl text-sm font-bold transition">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openEditModal(user) {
        document.getElementById('editForm').action = '/admin/users/' + user.id;
        document.getElementById('edit_name').value = user.name;
        document.getElementById('edit_email').value = user.email;
        document.getElementById('edit_wallet').value = user.wallet_balance;
        document.getElementById('edit_premium').checked = user.is_premium == 1;
        document.getElementById('editModal').classList.remove('hidden');
    }
    function closeEditModal() { document.getElementById('editModal').classList.add('hidden'); }
</script>
@endsection
