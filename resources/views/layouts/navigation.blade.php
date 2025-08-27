<nav x-data="{ open: false }" class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    {{-- Arahkan logo ke dashboard yang sesuai berdasarkan peran atau ke halaman utama jika belum login --}}
                    <a href="{{ Auth::check() ? (Auth::user()->role === 'admin' ? route('admin.dashboard') : (Auth::user()->role === 'pengguna_properti' ? route('property.dashboard') : route('dashboard'))) : url('/') }}">
                        <img src="{{ asset('images/GHM.png') }}" alt="Logo" style="width: 80px; height: auto;">
                    </a>
                </div>

                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    @auth {{-- Tampilkan link ini hanya jika pengguna sudah login --}}
                        @if(Auth::user()->role === 'admin')
                            <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">
                                {{ __('Dashboard Admin') }}
                            </x-nav-link>
                            <x-nav-link :href="route('admin.kpi.analysis')" :active="request()->routeIs('admin.kpi.analysis')">
                                {{ __('Pusat Analisis Kinerja') }}
                            </x-nav-link>
                            {{-- !! LINK BARU UNTUK MANAJEMEN TARGET !! --}}
                            <x-nav-link :href="route('admin.revenue-targets.index')" :active="request()->routeIs('admin.revenue-targets.*')">
                                {{ __('Manajemen Target') }}
                            </x-nav-link>
                            <x-nav-link :href="route('admin.properties.compare.form')" :active="request()->routeIs('admin.properties.compare.form') || request()->routeIs('admin.properties.compare.results')">
                                {{ __('Bandingkan Properti') }}
                            </x-nav-link>
                            <x-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.index') || request()->routeIs('admin.users.create') || request()->routeIs('admin.users.edit') || request()->routeIs('admin.users.trashed')">
                                {{ __('Manajemen Pengguna') }}
                            </x-nav-link>
                            <x-nav-link :href="route('admin.properties.index')" :active="request()->routeIs('admin.properties.index') || request()->routeIs('admin.properties.create') || request()->routeIs('admin.properties.edit') || request()->routeIs('admin.properties.show')">
                                {{ __('Manajemen Properti') }}
                            </x-nav-link>

                        @elseif(Auth::user()->role === 'pengguna_properti')
                            <x-nav-link :href="route('property.dashboard')" :active="request()->routeIs('property.dashboard')">
                                {{ __('Dashboard Properti') }}
                            </x-nav-link>
                            <x-nav-link :href="route('property.income.index')" :active="request()->routeIs('property.income.index') || request()->routeIs('property.income.create') || request()->routeIs('property.income.edit')">
                                {{ __('Riwayat Pendapatan') }}
                            </x-nav-link>
                            <x-nav-link :href="route('property.income.create')" :active="request()->routeIs('property.income.create')">
                                {{ __('+ Catat Pendapatan') }}
                            </x-nav-link>
                        @else
                            {{-- Link dashboard umum jika peran tidak spesifik atau untuk skenario lain --}}
                            <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                                {{ __('Dashboard') }}
                            </x-nav-link>
                        @endif
                    @endauth
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6">
                {{-- !! TOMBOL TOGGLE TEMA (DESKTOP) !! --}}
                <button onclick="toggleTheme()" title="Toggle light/dark mode"
                        class="mr-3 p-1.5 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-700 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6 hidden dark:block" fill="currentColor" viewBox="0 0 20 20"> {{-- Moon Icon for Light Mode Display --}}
                        <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                    </svg>
                    <svg class="h-6 w-6 dark:hidden" fill="currentColor" viewBox="0 0 20 20"> {{-- Sun Icon for Dark Mode Display --}}
                         <path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm-.464 4.95a1 1 0 100 2H3a1 1 0 100-2h1.586z"></path>
                    </svg>
                    <span class="sr-only">Toggle Theme</span>
                </button>
                {{-- !! AKHIR TOMBOL TOGGLE TEMA (DESKTOP) !! --}}

                @auth {{-- Tampilkan hanya jika pengguna sudah login --}}
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>
                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>
                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
                @else {{-- Tampilkan link Login/Register jika belum login --}}
                    <a href="{{ route('login') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">Log in</a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="ms-4 text-sm font-medium text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">Register</a>
                    @endif
                @endauth
            </div>

            <div class="-me-2 flex items-center sm:hidden">
                 {{-- !! TOMBOL TOGGLE TEMA (MOBILE) !! --}}
                 <button onclick="toggleTheme()" title="Toggle light/dark mode"
                         class="mr-1 p-1.5 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-700 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6 hidden dark:block" fill="currentColor" viewBox="0 0 20 20"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path></svg>
                    <svg class="h-6 w-6 dark:hidden" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm-.464 4.95a1 1 0 100 2H3a1 1 0 100-2h1.586z"></path></svg>
                    <span class="sr-only">Toggle Theme</span>
                </button>
                {{-- !! AKHIR TOMBOL TOGGLE TEMA (MOBILE) !! --}}

                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-700 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            {{-- Link Navigasi Responsif Disesuaikan dengan Peran --}}
            @auth
                @if(Auth::user()->role === 'admin')
                    <x-responsive-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">
                        {{ __('Dashboard Admin') }}
                    </x-responsive-nav-link>
                     {{-- !! LINK MOBILE KE PUSAT ANALISIS KINERJA !! --}}
                    <x-responsive-nav-link :href="route('admin.kpi.analysis')" :active="request()->routeIs('admin.kpi.analysis')">
                        {{ __('Pusat Analisis Kinerja') }}
                    </x-responsive-nav-link>
                    {{-- !! LINK BARU MOBILE UNTUK MANAJEMEN TARGET !! --}}
                    <x-responsive-nav-link :href="route('admin.revenue-targets.index')" :active="request()->routeIs('admin.revenue-targets.*')">
                        {{ __('Manajemen Target') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.properties.compare.form')" :active="request()->routeIs('admin.properties.compare.form') || request()->routeIs('admin.properties.compare.results')">
                        {{ __('Bandingkan Properti') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.index') || request()->routeIs('admin.users.create') || request()->routeIs('admin.users.edit') || request()->routeIs('admin.users.trashed')">
                        {{ __('Manajemen Pengguna') }}
                    </x-responsive-nav-link>
                     <x-responsive-nav-link :href="route('admin.properties.index')" :active="request()->routeIs('admin.properties.index') || request()->routeIs('admin.properties.create') || request()->routeIs('admin.properties.edit') || request()->routeIs('admin.properties.show')">
                        {{ __('Manajemen Properti') }}
                    </x-responsive-nav-link>

                @elseif(Auth::user()->role === 'pengguna_properti')
                    <x-responsive-nav-link :href="route('property.dashboard')" :active="request()->routeIs('property.dashboard')">
                        {{ __('Dashboard Properti') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('property.income.index')" :active="request()->routeIs('property.income.index') || request()->routeIs('property.income.create') || request()->routeIs('property.income.edit')">
                        {{ __('Riwayat Pendapatan') }}
                    </x-responsive-nav-link>
                     <x-responsive-nav-link :href="route('property.income.create')" :active="request()->routeIs('property.income.create')">
                        {{ __('+ Catat Pendapatan') }}
                    </x-responsive-nav-link>
                @else
                    <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-responsive-nav-link>
                @endif
            @else {{-- Jika belum login, tampilkan link login/register di menu mobile --}}
                <x-responsive-nav-link :href="route('login')">
                    {{ __('Log in') }}
                </x-responsive-nav-link>
                @if (Route::has('register'))
                    <x-responsive-nav-link :href="route('register')">
                        {{ __('Register') }}
                    </x-responsive-nav-link>
                @endif
            @endauth
        </div>

        @auth
        <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800 dark:text-gray-200">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
        @endauth
    </div>
</nav>
