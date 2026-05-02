@extends('layouts.admin')

@section('page_title', 'System Settings')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <div class="card p-8">
        <div class="flex items-center gap-4 mb-8">
            <div class="w-12 h-12 bg-indigo-500/10 rounded-2xl flex items-center justify-center text-indigo-500">
                <i class="fas fa-credit-card text-xl"></i>
            </div>
            <div>
                <h3 class="text-xl font-bold">Razorpay Integration</h3>
                <p class="text-gray-500 text-sm">Configure your payment gateway credentials</p>
            </div>
        </div>

        <form action="/admin/settings" method="POST" class="space-y-6">
            @csrf
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Razorpay Key ID</label>
                <input type="text" name="razorpay_key_id" value="{{ $settings['razorpay_key_id'] ?? '' }}" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-indigo-500" placeholder="rzp_test_..." />
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Razorpay Key Secret</label>
                <input type="password" name="razorpay_key_secret" value="{{ $settings['razorpay_key_secret'] ?? '' }}" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-indigo-500" placeholder="••••••••••••••••" />
            </div>

            <div class="pt-4 border-t border-white/5">
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-4 rounded-2xl shadow-lg shadow-indigo-500/20 transition-all active:scale-95">
                    Save Gateway Credentials
                </button>
            </div>
        </form>
    </div>

    <div class="card p-8">
        <div class="flex items-center gap-4 mb-8">
            <div class="w-12 h-12 bg-emerald-500/10 rounded-2xl flex items-center justify-center text-emerald-500">
                <i class="fas fa-cog text-xl"></i>
            </div>
            <div>
                <h3 class="text-xl font-bold">General Configuration</h3>
                <p class="text-gray-500 text-sm">Manage app-wide settings and limits</p>
            </div>
        </div>

        <form action="/admin/settings" method="POST" class="space-y-6">
            @csrf
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">App Name</label>
                <input type="text" name="app_name" value="{{ $settings['app_name'] ?? 'Astromind' }}" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-indigo-500" />
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Consultation Base Fee (₹)</label>
                <input type="number" name="consultation_fee" value="{{ $settings['consultation_fee'] ?? '200' }}" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-indigo-500" />
            </div>

            <div class="pt-4 border-t border-white/5">
                <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-4 rounded-2xl shadow-lg shadow-emerald-500/20 transition-all active:scale-95">
                    Update General Settings
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
