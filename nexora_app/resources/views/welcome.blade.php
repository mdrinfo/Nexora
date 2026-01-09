<!DOCTYPE html>
<html lang="tr" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Nexora - Lezzet ve Keyif</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: '#3b82f6',
                        secondary: '#8b5cf6',
                    }
                }
            }
        }
    </script>
    <script>
        // Check local storage or system preference for dark mode
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark')
        } else {
            document.documentElement.classList.remove('dark')
        }
    </script>
    <link rel="manifest" href="/manifest.webmanifest">
    <meta name="theme-color" content="#111827">
    <style>
        body { font-family: system-ui, -apple-system, sans-serif; }
        .card { transition: transform 0.2s, border-color 0.2s; }
        .card:hover { transform: translateY(-4px); border-color: #3b82f6; }
        .nav-link { position: relative; }
        .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -4px;
            left: 0;
            background-color: #3b82f6;
            transition: width 0.3s;
        }
        .nav-link:hover::after, .nav-link.active::after {
            width: 100%;
        }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-100 min-h-screen flex flex-col transition-colors duration-300">

    <!-- Navigation Menu -->
    <nav class="fixed w-full z-50 bg-white/90 dark:bg-gray-900/90 backdrop-blur-sm border-b border-gray-200 dark:border-gray-800 transition-all duration-300" id="navbar">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Logo -->
                <div class="flex-shrink-0 flex items-center gap-2 cursor-pointer" onclick="window.scrollTo(0,0)">
                    <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center text-white font-bold">
                        N
                    </div>
                    <span class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-blue-600 to-purple-600 dark:from-blue-400 dark:to-purple-400">Nexora</span>
                </div>
                
                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center">
                    <div class="ml-10 flex items-baseline space-x-8">
                        <a href="#home" class="nav-link text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 px-3 py-2 rounded-md text-sm font-medium transition-colors">Ana Sayfa</a>
                        <a href="#menu" class="nav-link text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 px-3 py-2 rounded-md text-sm font-medium transition-colors">Menü</a>
                        <a href="#reservations" class="nav-link text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 px-3 py-2 rounded-md text-sm font-medium transition-colors">Rezervasyon</a>
                        <a href="#contact" class="nav-link text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 px-3 py-2 rounded-md text-sm font-medium transition-colors">İletişim</a>
                        
                        @auth
                            <a href="{{ route('admin.dashboard') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">Panel</a>
                        @else
                            <a href="{{ route('login') }}" class="bg-gray-200 dark:bg-gray-800 hover:bg-gray-300 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 px-4 py-2 rounded-lg text-sm font-medium transition-colors">Giriş</a>
                        @endauth
                        
                        <!-- Dark Mode Toggle -->
                        <button id="theme-toggle" type="button" class="text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-4 focus:ring-gray-200 dark:focus:ring-gray-700 rounded-lg text-sm p-2.5">
                            <svg id="theme-toggle-dark-icon" class="hidden w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path></svg>
                            <svg id="theme-toggle-light-icon" class="hidden w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 100 2h1z" fill-rule="evenodd" clip-rule="evenodd"></path></svg>
                        </button>
                    </div>
                </div>

                <!-- Mobile Menu Button -->
                <div class="-mr-2 flex md:hidden items-center">
                     <!-- Dark Mode Toggle Mobile -->
                     <button id="theme-toggle-mobile" type="button" class="mr-2 text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-4 focus:ring-gray-200 dark:focus:ring-gray-700 rounded-lg text-sm p-2.5">
                        <svg id="theme-toggle-dark-icon-mobile" class="hidden w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path></svg>
                        <svg id="theme-toggle-light-icon-mobile" class="hidden w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 100 2h1z" fill-rule="evenodd" clip-rule="evenodd"></path></svg>
                    </button>
                    
                    <button onclick="toggleMobileMenu()" type="button" class="inline-flex items-center justify-center p-2 rounded-md text-gray-700 dark:text-gray-200 hover:text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-800 focus:ring-white">
                        <span class="sr-only">Menüyü Aç</span>
                        <!-- Icon when menu is closed -->
                        <svg class="block h-6 w-6" id="menu-closed-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <!-- Icon when menu is open -->
                        <svg class="hidden h-6 w-6" id="menu-open-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div class="hidden md:hidden bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-gray-800" id="mobile-menu">
            <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
                <a href="#home" onclick="toggleMobileMenu()" class="block text-gray-700 dark:text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-base font-medium">Ana Sayfa</a>
                <a href="#menu" onclick="toggleMobileMenu()" class="block text-gray-700 dark:text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-base font-medium">Menü</a>
                <a href="#reservations" onclick="toggleMobileMenu()" class="block text-gray-700 dark:text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-base font-medium">Rezervasyon</a>
                <a href="#contact" onclick="toggleMobileMenu()" class="block text-gray-700 dark:text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-base font-medium">İletişim</a>
                @auth
                    <a href="{{ route('admin.dashboard') }}" class="block bg-blue-600 text-white px-3 py-2 rounded-md text-base font-medium mt-4">Panel</a>
                @else
                    <a href="{{ route('login') }}" class="block bg-gray-200 dark:bg-gray-800 text-gray-700 dark:text-gray-300 px-3 py-2 rounded-md text-base font-medium mt-4">Giriş</a>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-grow pt-16">
        
        <!-- Hero Section (Home) -->
        <section id="home" class="relative min-h-screen flex items-center justify-center px-6 overflow-hidden">
            <!-- Background Blob -->
            <div class="absolute top-0 left-1/2 w-96 h-96 bg-purple-500/30 rounded-full blur-3xl -translate-x-1/2 -translate-y-1/2"></div>
            <div class="absolute bottom-0 right-0 w-96 h-96 bg-blue-500/20 rounded-full blur-3xl translate-x-1/3 translate-y-1/3"></div>

            <div class="relative z-10 text-center max-w-4xl mx-auto">
                <h1 class="text-5xl md:text-7xl font-bold tracking-tight text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-purple-600 dark:from-blue-400 dark:to-purple-500 mb-6 animate-fade-in-up">
                    Nexora Restaurant
                </h1>
                <p class="text-xl md:text-2xl text-gray-600 dark:text-gray-300 mb-10 max-w-2xl mx-auto">
                    Lezzet, teknoloji ve konforun buluştuğu nokta. Modern restoran deneyimini keşfedin.
                </p>
                
                <div class="flex flex-col sm:flex-row gap-4 justify-center mb-16">
                    <a href="#menu" class="px-8 py-4 bg-blue-600 hover:bg-blue-700 text-white rounded-full font-bold text-lg shadow-lg hover:shadow-blue-500/30 transition-all transform hover:-translate-y-1">
                        Menüyü İncele
                    </a>
                    <a href="#reservations" class="px-8 py-4 bg-white dark:bg-gray-800 text-gray-800 dark:text-white border border-gray-200 dark:border-gray-700 hover:border-blue-500 dark:hover:border-blue-500 rounded-full font-bold text-lg shadow-lg transition-all transform hover:-translate-y-1">
                        Rezervasyon Yap
                    </a>
                </div>

                <!-- Internal Systems Links -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-left opacity-90">
                    <!-- Admin -->
                    <a href="{{ route('admin.dashboard') }}" class="group block p-6 bg-white/50 dark:bg-gray-800/50 backdrop-blur border border-gray-200 dark:border-gray-700 rounded-2xl hover:bg-white dark:hover:bg-gray-800 transition-all">
                        <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                        </div>
                        <h3 class="font-bold text-gray-900 dark:text-white">Yönetim Paneli</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Stok, finans ve personel yönetimi.</p>
                    </a>
                    
                    <!-- Waiter -->
                    <a href="{{ route('waiter.index') }}" class="group block p-6 bg-white/50 dark:bg-gray-800/50 backdrop-blur border border-gray-200 dark:border-gray-700 rounded-2xl hover:bg-white dark:hover:bg-gray-800 transition-all">
                        <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                        </div>
                        <h3 class="font-bold text-gray-900 dark:text-white">Garson Terminali</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Sipariş alma ve masa takibi.</p>
                    </a>

                    <!-- Kitchen -->
                    <a href="{{ route('kds.kitchen') }}" class="group block p-6 bg-white/50 dark:bg-gray-800/50 backdrop-blur border border-gray-200 dark:border-gray-700 rounded-2xl hover:bg-white dark:hover:bg-gray-800 transition-all">
                        <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
                        </div>
                        <h3 class="font-bold text-gray-900 dark:text-white">Mutfak Ekranı</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">KDS ve sipariş hazırlama.</p>
                    </a>
                </div>
            </div>
        </section>

        <!-- Menu Section -->
        <section id="menu" class="py-20 bg-white dark:bg-gray-800">
            <div class="max-w-7xl mx-auto px-6">
                <div class="text-center mb-12">
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">Özel Menümüz</h2>
                    <p class="text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">Şeflerimizin özenle hazırladığı eşsiz lezzetleri keşfedin.</p>
                </div>
                
                @if(isset($categories) && count($categories) > 0)
                    <div class="space-y-16">
                        @foreach($categories as $category)
                            @if($category->products->count() > 0)
                                <div>
                                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-6 border-b pb-2 border-gray-200 dark:border-gray-700">{{ $category->name }}</h3>
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                                        @foreach($category->products as $product)
                                            <div class="bg-gray-50 dark:bg-gray-700 rounded-2xl overflow-hidden shadow-sm hover:shadow-lg transition flex flex-col">
                                                @if($product->image_path)
                                                    @php $imageUrl = asset('storage/' . $product->image_path); @endphp
                                                    <div class="h-48 bg-cover bg-center" style="background-image: url('{{ $imageUrl }}')"></div>
                                                @else
                                                    <div class="h-48 bg-gray-300 dark:bg-gray-600 flex items-center justify-center text-4xl">🍽️</div>
                                                @endif
                                                <div class="p-6 flex-grow flex flex-col">
                                                    <div class="flex justify-between items-start mb-2">
                                                        <h4 class="text-xl font-bold text-gray-900 dark:text-white">{{ $product->name }}</h4>
                                                        <span class="text-lg font-bold text-blue-600 dark:text-blue-400">{{ number_format($product->price, 2) }} ₺</span>
                                                    </div>
                                                    <p class="text-gray-600 dark:text-gray-300 text-sm mb-4 flex-grow">{{ $product->description ?? '' }}</p>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @else
                    <p class="text-center text-gray-500 dark:text-gray-400 text-lg">Menü öğeleri hazırlanıyor...</p>
                    <!-- Fallback Static Categories if empty for demo -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-12 opacity-50 grayscale">
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-2xl overflow-hidden shadow-sm p-6 text-center">
                             <h3 class="text-xl font-bold mb-2">Ana Yemekler</h3>
                             <p>Örnek kategori</p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-2xl overflow-hidden shadow-sm p-6 text-center">
                             <h3 class="text-xl font-bold mb-2">İçecekler</h3>
                             <p>Örnek kategori</p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-2xl overflow-hidden shadow-sm p-6 text-center">
                             <h3 class="text-xl font-bold mb-2">Tatlılar</h3>
                             <p>Örnek kategori</p>
                        </div>
                    </div>
                @endif
            </div>
        </section>

        <!-- Reservations Section -->
        <section id="reservations" class="py-20 bg-gray-50 dark:bg-gray-900">
            <div class="max-w-4xl mx-auto px-6 text-center">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-6">Rezervasyon</h2>
                <p class="text-gray-600 dark:text-gray-400 mb-10">Özel günleriniz veya keyifli bir akşam yemeği için yerinizi şimdiden ayırtın.</p>
                
                <form action="{{ route('public.reservations.store') }}" method="POST" class="bg-white dark:bg-gray-800 p-8 rounded-2xl shadow-lg text-left">
                    @csrf
                    
                    @if(session('success'))
                        <div class="mb-4 p-4 bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300 rounded-lg">
                            {{ session('success') }}
                        </div>
                    @endif
                    
                    @if($errors->any())
                        <div class="mb-4 p-4 bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300 rounded-lg">
                            <ul class="list-disc list-inside">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Ad Soyad</label>
                            <input type="text" name="name" value="{{ old('name') }}" required class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Adınız">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Telefon</label>
                            <input type="tel" name="phone" value="{{ old('phone') }}" required class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none" placeholder="0555 555 55 55">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tarih</label>
                            <input type="date" name="event_date" value="{{ old('event_date') }}" required class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Kişi Sayısı</label>
                            <input type="number" name="guest_count" value="{{ old('guest_count', 2) }}" min="1" max="50" required class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                        <div class="md:col-span-2">
                             <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Notlar</label>
                             <textarea name="notes" rows="3" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Varsa özel istekleriniz...">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg transition-colors">
                        Rezervasyon Oluştur
                    </button>
                </form>
            </div>
        </section>

        <!-- Contact Section -->
        <section id="contact" class="py-20 bg-white dark:bg-gray-800">
            <div class="max-w-7xl mx-auto px-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
                    <div>
                        <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-6">İletişim</h2>
                        <p class="text-gray-600 dark:text-gray-400 mb-8">
                            Sorularınız, görüşleriniz veya önerileriniz için bize ulaşın. Sizi ağırlamaktan mutluluk duyarız.
                        </p>
                        
                        <div class="space-y-6">
                            <div class="flex items-start gap-4">
                                <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center text-blue-600">📍</div>
                                <div>
                                    <h4 class="font-bold text-gray-900 dark:text-white">Adres</h4>
                                    <p class="text-gray-600 dark:text-gray-400">123 Lezzet Caddesi, Gastronomi Mahallesi<br>İstanbul, Türkiye</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-4">
                                <div class="w-10 h-10 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center text-green-600">📞</div>
                                <div>
                                    <h4 class="font-bold text-gray-900 dark:text-white">Telefon</h4>
                                    <p class="text-gray-600 dark:text-gray-400">+90 (212) 123 45 67</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-4">
                                <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center text-purple-600">✉️</div>
                                <div>
                                    <h4 class="font-bold text-gray-900 dark:text-white">E-posta</h4>
                                    <p class="text-gray-600 dark:text-gray-400">info@nexora.com</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="h-80 bg-gray-200 dark:bg-gray-700 rounded-2xl flex items-center justify-center text-gray-500 dark:text-gray-400">
                        <!-- Placeholder for Map -->
                        <span>Harita Alanı</span>
                    </div>
                </div>
            </div>
        </section>

    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="max-w-7xl mx-auto px-6 text-center">
            <div class="flex items-center justify-center gap-2 mb-6">
                <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center font-bold">N</div>
                <span class="text-xl font-bold">Nexora</span>
            </div>
            <p class="text-gray-400 text-sm mb-8">© {{ date('Y') }} Nexora Restaurant Systems. Tüm hakları saklıdır.</p>
            <div class="flex justify-center gap-6">
                <a href="#" class="text-gray-400 hover:text-white transition-colors">Instagram</a>
                <a href="#" class="text-gray-400 hover:text-white transition-colors">Facebook</a>
                <a href="#" class="text-gray-400 hover:text-white transition-colors">Twitter</a>
            </div>
        </div>
    </footer>

    <script>
        // Mobile Menu Toggle
        function toggleMobileMenu() {
            const menu = document.getElementById('mobile-menu');
            const closedIcon = document.getElementById('menu-closed-icon');
            const openIcon = document.getElementById('menu-open-icon');
            
            if (menu.classList.contains('hidden')) {
                menu.classList.remove('hidden');
                closedIcon.classList.add('hidden');
                openIcon.classList.remove('hidden');
            } else {
                menu.classList.add('hidden');
                closedIcon.classList.remove('hidden');
                openIcon.classList.add('hidden');
            }
        }

        // Active Link Highlight on Scroll
        window.addEventListener('scroll', () => {
            let current = '';
            const sections = document.querySelectorAll('section');
            const navLinks = document.querySelectorAll('.nav-link');

            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                if (pageYOffset >= sectionTop - 100) {
                    current = section.getAttribute('id');
                }
            });

            navLinks.forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href').includes(current)) {
                    link.classList.add('active');
                }
            });
        });

        // Service Worker
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js');
        }

        // Dark Mode Toggle Logic
        var themeToggleDarkIcon = document.getElementById('theme-toggle-dark-icon');
        var themeToggleLightIcon = document.getElementById('theme-toggle-light-icon');
        var themeToggleDarkIconMobile = document.getElementById('theme-toggle-dark-icon-mobile');
        var themeToggleLightIconMobile = document.getElementById('theme-toggle-light-icon-mobile');

        // Change the icons inside the button based on previous settings
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            themeToggleLightIcon.classList.remove('hidden');
            themeToggleLightIconMobile.classList.remove('hidden');
        } else {
            themeToggleDarkIcon.classList.remove('hidden');
            themeToggleDarkIconMobile.classList.remove('hidden');
        }

        var themeToggleBtn = document.getElementById('theme-toggle');
        var themeToggleBtnMobile = document.getElementById('theme-toggle-mobile');

        function toggleTheme() {
            // toggle icons inside button
            themeToggleDarkIcon.classList.toggle('hidden');
            themeToggleLightIcon.classList.toggle('hidden');
            themeToggleDarkIconMobile.classList.toggle('hidden');
            themeToggleLightIconMobile.classList.toggle('hidden');

            // if set via local storage previously
            if (localStorage.getItem('theme')) {
                if (localStorage.getItem('theme') === 'light') {
                    document.documentElement.classList.add('dark');
                    localStorage.setItem('theme', 'dark');
                } else {
                    document.documentElement.classList.remove('dark');
                    localStorage.setItem('theme', 'light');
                }
            } else {
                if (document.documentElement.classList.contains('dark')) {
                    document.documentElement.classList.remove('dark');
                    localStorage.setItem('theme', 'light');
                } else {
                    document.documentElement.classList.add('dark');
                    localStorage.setItem('theme', 'dark');
                }
            }
        }

        themeToggleBtn.addEventListener('click', toggleTheme);
        themeToggleBtnMobile.addEventListener('click', toggleTheme);
    </script>
</body>
</html>
