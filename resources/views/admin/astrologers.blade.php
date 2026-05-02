@extends('layouts.admin')

@section('page_title', 'Astrologer Management')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <div class="lg:col-span-2 card p-6">
        @if(session('success'))
            <div class="mb-6 p-4 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-2xl text-sm font-bold">
                <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
            </div>
        @endif
        @if($errors->any())
            <div class="mb-6 p-4 bg-rose-500/10 border border-rose-500/20 text-rose-400 rounded-2xl text-sm font-bold">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <h3 class="text-lg font-bold mb-6">Registered Astrologers</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($astrologers as $astro)
            <div class="p-4 bg-white/5 rounded-2xl border border-white/10 flex items-center gap-4">
                <img src="{{ $astro->profile_image ?? 'https://i.pravatar.cc/100?u='.$astro->id }}" class="w-14 h-14 rounded-2xl border-2 border-indigo-500/30 object-cover" />
                <div class="flex-1">
                    <h4 class="font-bold text-white">{{ $astro->name }}</h4>
                    <p class="text-[10px] text-indigo-400 font-bold uppercase">{{ $astro->specialization }}</p>
                    <div class="flex gap-2 mt-2">
                        @if($astro->is_online)
                            <span class="text-[9px] bg-emerald-500/10 text-emerald-400 font-bold px-2 py-0.5 rounded uppercase border border-emerald-500/20">Online</span>
                        @else
                            <span class="text-[9px] bg-gray-500/10 text-gray-400 font-bold px-2 py-0.5 rounded uppercase border border-gray-500/20">Offline</span>
                        @endif
                        <span class="text-[9px] bg-amber-500/10 text-amber-500 font-bold px-2 py-0.5 rounded uppercase border border-amber-500/20">₹{{ $astro->price_per_minute }}/min</span>
                    </div>
                </div>
                <button 
                    onclick="openEditModal({
                        id: {{ $astro->id }},
                        name: '{{ addslashes($astro->name) }}',
                        email: '{{ $astro->user ? $astro->user->email : '' }}',
                        specialization: '{{ addslashes($astro->specialization) }}',
                        city: '{{ addslashes($astro->city) }}',
                        experience: {{ $astro->experience }},
                        price_per_minute: {{ $astro->price_per_minute }},
                        languages: '{{ addslashes($astro->languages) }}',
                        bio: '{{ addslashes($astro->bio) }}',
                        is_online: {{ $astro->is_online }}
                    })" 
                    class="p-2 text-gray-500 hover:text-indigo-400 transition"
                >
                    <i class="fas fa-edit"></i>
                </button>
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
                    <input type="text" name="name" required class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-indigo-500" placeholder="Dr. Arjun Sharma" />
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Email Address</label>
                    <input type="email" name="email" required class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-indigo-500" placeholder="arjun@astromind.com" />
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Private Phone</label>
                    <input type="text" name="phone" required class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-indigo-500" placeholder="+91 9876543210" />
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">City</label>
                    <input type="text" name="city" required class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-indigo-500" placeholder="Chennai" />
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Specialization</label>
                    <input type="text" name="specialization" required class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-indigo-500" placeholder="Vedic, Vastu" />
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Experience (Yrs)</label>
                    <input type="number" name="experience" required class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-indigo-500" placeholder="10" />
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Price/Min (₹)</label>
                    <input type="number" name="price_per_minute" required class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-indigo-500" value="10" />
                </div>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Languages (Comma separated)</label>
                <input type="text" name="languages" required class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-indigo-500" placeholder="English, Tamil, Hindi" />
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Biography</label>
                <textarea name="bio" required class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-indigo-500 h-24" placeholder="Briefly describe the expert's background..."></textarea>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Login Password</label>
                <input type="password" name="password" required class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-indigo-500" placeholder="Min 6 characters" />
            </div>
            <div class="pt-2">
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-4 rounded-2xl shadow-lg shadow-indigo-500/20 transition-all active:scale-95">Register Astrologer</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Astrologer Modal -->
<div id="editAstroModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 py-12">
        <div class="fixed inset-0 bg-black/80" onclick="closeEditModal()"></div>
        <div class="relative bg-[#0d0d1f] rounded-3xl w-full max-w-2xl border border-white/10 shadow-2xl overflow-hidden transform transition-all">
            <form id="editAstroForm" method="POST" class="p-8">
                @csrf
                @method('PUT')
                <div class="flex justify-between items-center mb-8">
                    <h3 class="text-xl font-bold text-white">Edit Astrologer Profile</h3>
                    <button type="button" onclick="closeEditModal()" class="text-gray-500 hover:text-white"><i class="fas fa-times text-xl"></i></button>
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <div class="col-span-2">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2 tracking-widest">Full Name</label>
                        <input type="text" name="name" id="edit_name" required class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-4 text-white focus:outline-none focus:border-indigo-500" />
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2 tracking-widest text-indigo-400">Login Email (ID)</label>
                        <input type="email" name="email" id="edit_email" required class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-4 text-white focus:outline-none focus:border-indigo-500" />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2 tracking-widest">Specialization</label>
                        <input type="text" name="specialization" id="edit_spec" required class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-4 text-white focus:outline-none focus:border-indigo-500" />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2 tracking-widest">City</label>
                        <input type="text" name="city" id="edit_city" required class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-4 text-white focus:outline-none focus:border-indigo-500" />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2 tracking-widest">Experience (Yrs)</label>
                        <input type="number" name="experience" id="edit_exp" required class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-4 text-white focus:outline-none focus:border-indigo-500" />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2 tracking-widest">Price/Min (₹)</label>
                        <input type="number" name="price_per_minute" id="edit_price" required class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-4 text-white focus:outline-none focus:border-indigo-500" />
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2 tracking-widest text-rose-400">New Password (Optional)</label>
                        <input type="password" name="password" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-4 text-white focus:outline-none focus:border-indigo-500" placeholder="Leave blank to keep current" />
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2 tracking-widest">Languages</label>
                        <input type="text" name="languages" id="edit_langs" required class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-4 text-white focus:outline-none focus:border-indigo-500" />
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2 tracking-widest">Bio / Description</label>
                        <textarea name="bio" id="edit_bio" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-4 text-white focus:outline-none focus:border-indigo-500 h-32"></textarea>
                    </div>
                    <div class="col-span-2 flex items-center gap-4 p-4 bg-white/5 rounded-2xl border border-white/10">
                        <input type="checkbox" name="is_online" id="edit_online" class="w-5 h-5 rounded bg-white/5 border-white/10 text-indigo-600 focus:ring-indigo-500" />
                        <label for="edit_online" class="text-sm font-bold text-white uppercase tracking-widest">Expert is currently Online</label>
                    </div>
                </div>

                <div class="mt-10 flex gap-4">
                    <button type="button" onclick="closeEditModal()" class="flex-1 py-4 text-sm font-bold text-gray-500 hover:text-white transition">Cancel</button>
                    <button type="submit" class="flex-[2] bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-4 rounded-2xl shadow-xl shadow-indigo-500/20 transition-all active:scale-95">Update Profile</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openEditModal(astro) {
        document.getElementById('editAstroForm').action = '/admin/astrologers/' + astro.id;
        document.getElementById('edit_name').value = astro.name;
        document.getElementById('edit_email').value = astro.email;
        document.getElementById('edit_spec').value = astro.specialization;
        document.getElementById('edit_city').value = astro.city;
        document.getElementById('edit_exp').value = astro.experience;
        document.getElementById('edit_price').value = astro.price_per_minute;
        document.getElementById('edit_langs').value = astro.languages;
        document.getElementById('edit_bio').value = astro.bio;
        document.getElementById('edit_online').checked = astro.is_online == 1;
        document.getElementById('editAstroModal').classList.remove('hidden');
    }

    function closeEditModal() {
        document.getElementById('editAstroModal').classList.add('hidden');
    }

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
