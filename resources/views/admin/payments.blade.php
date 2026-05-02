@extends('layouts.admin')

@section('page_title', 'Transaction History')

@section('content')
<div class="card p-6">
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-lg font-bold">Payments & Top-ups</h3>
        <div class="flex gap-4">
            <button class="bg-white/5 border border-white/10 text-gray-400 px-4 py-2 rounded-xl text-sm transition">Filter by Status</button>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="text-gray-500 text-xs uppercase tracking-wider border-b border-white/5">
                    <th class="pb-4">Transaction ID</th>
                    <th class="pb-4">User</th>
                    <th class="pb-4">Amount</th>
                    <th class="pb-4">Type</th>
                    <th class="pb-4">Status</th>
                    <th class="pb-4">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @forelse($transactions as $tx)
                <tr>
                    <td class="py-4 font-mono text-xs text-indigo-400">{{ $tx->payment_id ?? 'N/A' }}</td>
                    <td class="py-4">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-bold text-white">{{ $tx->user->name }}</span>
                        </div>
                    </td>
                    <td class="py-4">
                        <span class="text-sm font-bold text-white">₹{{ number_format($tx->amount, 2) }}</span>
                    </td>
                    <td class="py-4">
                        <span class="text-[10px] bg-white/5 text-gray-400 font-bold px-2 py-1 rounded uppercase border border-white/10">
                            {{ str_replace('_', ' ', $tx->type) }}
                        </span>
                    </td>
                    <td class="py-4">
                        @if($tx->status == 'success')
                            <span class="text-emerald-500 flex items-center gap-1 text-xs font-bold"><i class="fas fa-check-circle"></i> Success</span>
                        @elseif($tx->status == 'pending')
                            <span class="text-amber-500 flex items-center gap-1 text-xs font-bold"><i class="fas fa-clock"></i> Pending</span>
                        @else
                            <span class="text-rose-500 flex items-center gap-1 text-xs font-bold"><i class="fas fa-times-circle"></i> Failed</span>
                        @endif
                    </td>
                    <td class="py-4 text-xs text-gray-500">{{ $tx->created_at->format('M d, Y H:i') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="py-20 text-center">
                        <i class="fas fa-receipt text-4xl text-gray-800 mb-4"></i>
                        <p class="text-gray-600">No transactions recorded yet.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $transactions->links() }}
    </div>
</div>
@endsection
