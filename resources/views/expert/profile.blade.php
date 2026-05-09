@extends('layouts.expert')

@section('page_title', 'My Profile')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <div class="lg:col-span-2 card p-8">
        <form action="/expert/profile" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            <div class="flex items-center gap-8 mb-10 p-6 bg-white/5 rounded-3xl border border-white/10">
                <div class="relative group">
                    <img src="{{ $expert->profile_image ?? 'https://i.pravatar.cc/200?u='.Auth::id() }}" class="w-32 h-32 rounded-3xl object-cover border-4 border-indigo-500/30" />
                    <label class="absolute inset-0 flex items-center justify-center bg-black/60 opacity-0 group-hover:opacity-100 transition rounded-3xl cursor-pointer">
                        <i class="fas fa-camera text-2xl"></i>
                        <input type="file" name="profile_image" class="hidden" />
                    </label>
                </div>
                <div>
                    <h3 class="text-xl font-bold mb-1">{{ Auth::user()->name }}</h3>
                    <p class="text-gray-500 text-sm mb-4">{{ $expert->category == 'palm_reader' ? 'Professional Palm Reader' : 'Vedic Astrologer' }}</p>
                    <div class="flex items-center gap-4">
                        <span class="text-xs bg-indigo-500/20 text-indigo-400 px-3 py-1 rounded-full font-bold uppercase">{{ $expert->specialization }}</span>
                        <span class="text-xs bg-emerald-500/20 text-emerald-400 px-3 py-1 rounded-full font-bold uppercase">{{ $expert->experience }} Years Exp</span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2 tracking-widest">Public Display Name</label>
                    <input type="text" name="name" value="{{ $expert->name }}" required class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-4 text-white focus:outline-none focus:border-indigo-500" />
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2 tracking-widest">Primary Specialization</label>
                    <input type="text" name="specialization" value="{{ $expert->specialization }}" required class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-4 text-white focus:outline-none focus:border-indigo-500" />
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2 tracking-widest">Experience (Years)</label>
                    <input type="number" name="experience" value="{{ $expert->experience }}" required class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-4 text-white focus:outline-none focus:border-indigo-500" />
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2 tracking-widest">Consultation Fee (₹/Min)</label>
                    <input type="number" name="price_per_minute" value="{{ $expert->price_per_minute }}" required class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-4 text-white focus:outline-none focus:border-indigo-500" />
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2 tracking-widest">City / Location</label>
                    <input type="text" name="city" value="{{ $expert->city }}" required class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-4 text-white focus:outline-none focus:border-indigo-500" />
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2 tracking-widest">Languages Spoken</label>
                    <input type="text" name="languages" value="{{ $expert->languages }}" required class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-4 text-white focus:outline-none focus:border-indigo-500" />
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-2 tracking-widest">Professional Biography</label>
                <textarea name="bio" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-4 text-white focus:outline-none focus:border-indigo-500 h-40">{{ $expert->bio }}</textarea>
            </div>

            <div class="flex items-center gap-4 p-4 bg-indigo-600/10 rounded-2xl border border-indigo-500/20">
                <input type="checkbox" name="is_online" id="is_online" {{ $expert->is_online ? 'checked' : '' }} class="w-5 h-5 rounded bg-white/5 border-white/10 text-indigo-600 focus:ring-indigo-500" />
                <label for="is_online" class="text-sm font-bold text-white uppercase tracking-widest">Expert is currently Online & Accepting Calls</label>
            </div>

            <div class="pt-6">
                <button type="submit" class="w-full md:w-fit px-12 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-4 rounded-2xl shadow-xl shadow-indigo-500/20 transition-all active:scale-95">Update My Profile</button>
            </div>
        </form>
    </div>

    <div class="space-y-6">
        <div class="card p-6">
            <h3 class="text-lg font-bold mb-4">Account Security</h3>
            <p class="text-xs text-gray-500 mb-6">Manage your login credentials and security settings.</p>
            <button class="w-full py-4 bg-white/5 border border-white/10 rounded-2xl text-sm font-bold hover:bg-white/10 transition">Change Password</button>
        </div>

        <div class="card p-6 bg-amber-500/10 border-amber-500/20">
            <h3 class="text-lg font-bold mb-2 text-amber-500">Expert Tips</h3>
            <p class="text-xs text-amber-500/70 leading-relaxed italic">"A detailed biography and clear specialization help users trust your insights more. Keep your online status updated to receive more call requests!"</p>
        </div>
    </div>
</div>
@endsection
