@extends('layouts.admin')

@section('page_title', 'Astrologer Management')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <div class="lg:col-span-2 card p-6">
        <h3 class="text-lg font-bold mb-6">Registered Astrologers</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($astrologers as $astro)
            <div class="p-4 bg-white/5 rounded-2xl border border-white/10 flex items-center gap-4">
                <img src="{{ $astro->avatar ?? 'https://i.pravatar.cc/100?u='.$astro->id }}" class="w-14 h-14 rounded-2xl border-2 border-indigo-500/30" />
                <div class="flex-1">
                    <h4 class="font-bold text-white">{{ $astro->name }}</h4>
                    <p class="text-xs text-gray-500">{{ $astro->email }}</p>
                    <div class="flex gap-2 mt-2">
                        <span class="text-[10px] bg-emerald-500/10 text-emerald-400 font-bold px-2 py-0.5 rounded uppercase">Online</span>
                        <span class="text-[10px] bg-indigo-500/10 text-indigo-400 font-bold px-2 py-0.5 rounded uppercase">Verified</span>
                    </div>
                </div>
                <button class="p-2 text-gray-500 hover:text-white"><i class="fas fa-ellipsis-v"></i></button>
            </div>
            @endforeach
        </div>
    </div>

    <div class="card p-6 h-fit sticky top-8">
        <h3 class="text-lg font-bold mb-6">Add New Astrologer</h3>
        <form action="/admin/astrologers" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div class="flex flex-col items-center gap-4 p-6 bg-white/5 rounded-2xl border border-dashed border-white/20 mb-4">
                <div id="image-preview-container" class="w-24 h-24 rounded-3xl border-2 border-indigo-500/30 overflow-hidden bg-black/20 flex items-center justify-center">
                    <i class="fas fa-user text-gray-700 text-3xl"></i>
                </div>
                <div class="w-full">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2 text-center">Profile Picture</label>
                    <input type="file" name="profile_image" id="profile_image_input" onchange="previewImage(this)" class="w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-indigo-600 file:text-white hover:file:bg-indigo-700" />
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Full Name</label>
                    <input type="text" name="name" required class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-indigo-500" placeholder="Dr. Arjun Sharma" />
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Email Address</label>
                    <input type="email" name="email" required class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-indigo-500" placeholder="arjun@astromind.com" />
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Private Phone Number</label>
                    <input type="text" name="phone" required class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-indigo-500" placeholder="+91 9876543210" />
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">City</label>
                    <input type="text" name="city" required class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-indigo-500" placeholder="Chennai" />
                </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Specialization</label>
                <input type="text" name="specialization" required class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-indigo-500" placeholder="Vedic, Vastu, Numerology" />
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Experience (Yrs)</label>
                    <input type="number" name="experience" required class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-indigo-500" placeholder="10" />
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Price/Min (₹)</label>
                    <input type="number" name="price_per_minute" required class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-indigo-500" placeholder="25" />
                </div>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Languages</label>
                <input type="text" name="languages" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-indigo-500" placeholder="English, Tamil, Hindi" />
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">About / Bio</label>
                <textarea name="bio" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-indigo-500 h-24" placeholder="Describe the astrologer's expertise and background..."></textarea>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Login Password</label>
                <input type="password" name="password" required class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-indigo-500" placeholder="Min 6 characters" />
            </div>
            <div class="pt-4">
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-4 rounded-2xl shadow-lg shadow-indigo-500/20 transition-all active:scale-95">
                    Register Astrologer
                </button>
            </div>
            <p class="text-[10px] text-gray-500 text-center mt-4">
                Credentials will be automatically sent to the astrologer's email for app login.
            </p>
        </form>
    </div>
</div>

<script>
    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('image-preview-container').innerHTML = 
                    `<img src="${e.target.result}" class="w-full h-full object-cover" />`;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endsection
