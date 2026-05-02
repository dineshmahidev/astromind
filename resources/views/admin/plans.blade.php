@extends('layouts.admin')

@section('page_title', 'Pricing Plans')

@section('content')
<div class="mb-10 flex justify-between items-end">
    <div>
        <h3 class="text-xl font-bold">Subscription Packages</h3>
        <p class="text-gray-500 text-sm">Manage user tiers and premium access pricing</p>
    </div>
    <button onclick="document.getElementById('addPlanModal').classList.remove('hidden')" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-6 py-3 rounded-2xl transition shadow-lg shadow-emerald-500/20">
        <i class="fas fa-plus mr-2"></i> Create New Plan
    </button>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
    @foreach($plans as $plan)
    <div class="card p-8 relative overflow-hidden">
        @if($plan->is_active)
            <div class="absolute top-0 right-0 bg-emerald-500 text-[10px] font-bold px-3 py-1 rounded-bl-xl uppercase">Active</div>
        @endif
        
        <h4 class="text-indigo-400 font-bold uppercase tracking-widest text-xs mb-2">{{ $plan->slug }}</h4>
        <h3 class="text-2xl font-bold mb-4">{{ $plan->name }}</h3>
        
        <div class="flex items-baseline gap-2 mb-6">
            <span class="text-4xl font-bold">₹{{ number_format($plan->price, 0) }}</span>
            <span class="text-gray-500">/ {{ $plan->duration_days }} days</span>
        </div>

        <ul class="space-y-3 mb-8">
            @if($plan->features)
                @foreach(json_decode($plan->features) as $feature)
                <li class="flex items-center gap-3 text-sm text-gray-300">
                    <i class="fas fa-check-circle text-emerald-500"></i> {{ $feature }}
                </li>
                @endforeach
            @endif
        </ul>

        <div class="flex gap-3">
            <button class="flex-1 bg-white/5 hover:bg-white/10 border border-white/10 py-3 rounded-xl font-bold transition">Edit</button>
            <button class="w-12 bg-rose-500/10 hover:bg-rose-500/20 text-rose-500 border border-rose-500/20 rounded-xl transition">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    </div>
    @endforeach
</div>

<!-- Simple Modal (Add Plan) -->
<div id="addPlanModal" class="hidden fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center p-6">
    <div class="bg-[#131326] w-full max-w-lg rounded-3xl border border-white/10 p-8 shadow-2xl">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-2xl font-bold">Create Plan</h3>
            <button onclick="document.getElementById('addPlanModal').classList.add('hidden')" class="text-gray-500 hover:text-white"><i class="fas fa-times"></i></button>
        </div>
        <form action="/admin/plans" method="POST" class="space-y-4">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Plan Name</label>
                    <input type="text" name="name" required class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm" placeholder="Premium Monthly" />
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Slug</label>
                    <input type="text" name="slug" required class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm" placeholder="monthly_pro" />
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Price (₹)</label>
                    <input type="number" name="price" required class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm" placeholder="999" />
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Duration (Days)</label>
                    <input type="number" name="duration_days" required class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm" placeholder="30" />
                </div>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Description</label>
                <textarea name="description" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm h-24" placeholder="Briefly describe the plan benefits"></textarea>
            </div>
            <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-4 rounded-2xl transition">Save Plan</button>
        </form>
    </div>
</div>
@endsection
