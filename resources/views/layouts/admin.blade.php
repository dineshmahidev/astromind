<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Astromind Admin - Premium Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Outfit', sans-serif; background-color: #050510; color: #fff; }
        .sidebar { background: rgba(255, 255, 255, 0.03); backdrop-filter: blur(10px); border-right: 1px solid rgba(255, 255, 255, 0.1); }
        .card { background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 20px; transition: all 0.3s; }
        .card:hover { transform: translateY(-5px); border-color: #6c5ce7; }
        .nav-link { transition: all 0.3s; border-radius: 12px; }
        .nav-link:hover, .nav-link.active { background: rgba(108, 92, 231, 0.15); color: #a29bfe; }
    </style>
</head>
<body class="flex min-h-screen">

    <!-- Sidebar -->
    <aside class="w-64 sidebar p-6 flex flex-col">
        <div class="flex items-center gap-3 mb-10 px-2">
            <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center">
                <i class="fas fa-sparkles text-white"></i>
            </div>
            <span class="text-xl font-bold tracking-tight">Astromind</span>
        </div>

        <nav class="flex-1 space-y-2">
            <a href="/admin/dashboard" class="nav-link flex items-center gap-3 p-3 text-gray-400 active">
                <i class="fas fa-th-large"></i> Dashboard
            </a>
            <a href="/admin/users" class="nav-link flex items-center gap-3 p-3 text-gray-400">
                <i class="fas fa-users"></i> Users
            </a>
            <a href="/admin/astrologers" class="nav-link flex items-center gap-3 p-3 text-gray-400">
                <i class="fas fa-user-tie"></i> Astrologers
            </a>
            <a href="/admin/plans" class="nav-link flex items-center gap-3 p-3 text-gray-400">
                <i class="fas fa-gem"></i> Pricing Plans
            </a>
            <a href="/admin/consultations" class="nav-link flex items-center gap-3 p-3 text-gray-400">
                <i class="fas fa-comments"></i> Consultations
            </a>
            <a href="/admin/payments" class="nav-link flex items-center gap-3 p-3 text-gray-400">
                <i class="fas fa-wallet"></i> Payments
            </a>
            <a href="/admin/settings" class="nav-link flex items-center gap-3 p-3 text-gray-400">
                <i class="fas fa-cog"></i> Settings
            </a>
        </nav>

        <div class="mt-auto pt-6 border-t border-white/10">
            <a href="/logout" class="flex items-center gap-3 p-3 text-red-400 hover:bg-red-500/10 rounded-xl transition">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 p-8 overflow-y-auto">
        <header class="flex justify-between items-center mb-10">
            <div>
                <h2 class="text-2xl font-bold">@yield('page_title', 'Dashboard')</h2>
                <p class="text-gray-500 text-sm">Welcome back, Admin</p>
            </div>
            <div class="flex items-center gap-4">
                <button class="w-10 h-10 rounded-full bg-white/5 border border-white/10 flex items-center justify-center text-gray-400">
                    <i class="fas fa-bell"></i>
                </button>
                <div class="flex items-center gap-3 bg-white/5 border border-white/10 p-1.5 pr-4 rounded-full">
                    <img src="https://i.pravatar.cc/100?u=admin" class="w-8 h-8 rounded-full border border-indigo-500" />
                    <span class="text-sm font-semibold">Admin</span>
                </div>
            </div>
        </header>

        @if(session('success'))
            <div class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 p-4 rounded-xl mb-6 flex items-center gap-3">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        @yield('content')
    </main>

</body>
</html>
