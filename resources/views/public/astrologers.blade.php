<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expert Astrologers - Astromind</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Outfit', sans-serif; background-color: #050510; color: #fff; }
        .glass { background: rgba(255, 255, 255, 0.03); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.1); }
        .hero-gradient { background: radial-gradient(circle at 50% 50%, rgba(108, 92, 231, 0.15) 0%, transparent 50%); }
        .card { transition: all 0.3s; }
        .card:hover { transform: translateY(-10px); border-color: #6c5ce7; box-shadow: 0 20px 40px rgba(108, 92, 231, 0.1); }
    </style>
</head>
<body class="min-h-screen">

    <nav class="p-6 flex justify-between items-center max-w-7xl mx-auto">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center">
                <i class="fas fa-sparkles text-white"></i>
            </div>
            <span class="text-xl font-bold tracking-tight">Astromind</span>
        </div>
        <div class="flex gap-8 text-gray-400 font-medium">
            <a href="/" class="hover:text-white transition">Home</a>
            <a href="/astrologers" class="text-white">Astrologers</a>
            <a href="/horoscope" class="hover:text-white transition">Horoscope</a>
        </div>
        <button class="bg-indigo-600 text-white px-6 py-2.5 rounded-full font-bold hover:bg-indigo-700 transition">Get App</button>
    </nav>

    <header class="py-20 text-center hero-gradient">
        <h1 class="text-5xl font-bold mb-4 tracking-tight">Consult Best Astrologers</h1>
        <p class="text-gray-400 text-lg max-w-2xl mx-auto">Connect with verified experts for Vedic astrology, Vastu, Numerology and more. Get answers to your life's deepest questions.</p>
    </header>

    <main class="max-w-7xl mx-auto px-6 pb-20">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($astrologers as $astro)
            <div class="glass card p-8 rounded-[32px] border border-white/10">
                <div class="flex items-start gap-6 mb-6">
                    <div class="relative">
                        <img src="{{ $astro->profile_image }}" class="w-24 h-24 rounded-3xl border-2 border-indigo-500/30 object-cover" />
                        <div class="absolute -bottom-2 -right-2 bg-emerald-500 w-6 h-6 rounded-full border-4 border-[#050510]"></div>
                    </div>
                    <div class="flex-1">
                        <div class="flex justify-between">
                            <h3 class="text-xl font-bold">{{ $astro->name }}</h3>
                            <div class="flex items-center gap-1 text-amber-500">
                                <i class="fas fa-star text-xs"></i>
                                <span class="text-sm font-bold">4.9</span>
                            </div>
                        </div>
                        <p class="text-indigo-400 text-sm font-semibold mb-2">{{ $astro->specialization }}</p>
                        <p class="text-gray-500 text-xs line-clamp-2 mb-4 leading-relaxed">{{ $astro->bio }}</p>
                        <div class="flex items-center gap-4 text-gray-500 text-xs flex-wrap">
                            <span><i class="fas fa-location-dot mr-1 text-rose-500"></i> {{ $astro->city }}</span>
                            <span><i class="fas fa-language mr-1 text-indigo-400"></i> {{ $astro->languages }}</span>
                            <span><i class="fas fa-history mr-1 text-amber-500"></i> {{ $astro->experience }} yrs Exp</span>
                        </div>
                    </div>
                </div>

                <div class="flex justify-between items-center pt-6 border-t border-white/5">
                    <div>
                        <p class="text-gray-500 text-xs uppercase font-bold tracking-widest">Starts from</p>
                        <p class="text-2xl font-bold text-white">₹25<span class="text-sm font-normal text-gray-500">/min</span></p>
                    </div>
                    <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-3 rounded-2xl font-bold transition shadow-lg shadow-indigo-500/20">
                        Chat Now
                    </button>
                </div>
            </div>
            @endforeach
        </div>
    </main>

    <footer class="border-t border-white/5 py-10 text-center text-gray-600">
        <p>&copy; 2026 Astromind. Trusted by 1M+ users worldwide.</p>
    </footer>

</body>
</html>
