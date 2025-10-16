<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
            @include('layouts.navigation')

            <main class="flex">
                {{-- Sidebar --}}
                <aside class="w-64 bg-white dark:bg-gray-800 shadow-md h-screen sticky top-0">
                    <div class="p-4">
                        <h3 class="font-bold text-lg text-gray-800 dark:text-gray-200 mb-4">Menu Inventaris</h3>
                        <nav class="space-y-2">
                            <x-nav-link :href="route('inventory.dashboard')" :active="request()->routeIs('inventory.dashboard')">
                                {{ __('Dashboard / Item') }}
                            </x-nav-link>
                            <x-nav-link :href="route('inventory.categories.index')" :active="request()->routeIs('inventory.categories.*')">
                                {{ __('Kelola Kategori') }}
                            </x-nav-link>
                        </nav>
                    </div>
                </aside>

                {{-- Main Content --}}
                <div class="flex-1 p-6">
                    @if (isset($header))
                        <header class="bg-white dark:bg-gray-800 shadow rounded-lg mb-6">
                            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                                {{ $header }}
                            </div>
                        </header>
                    @endif
                    
                    {{ $slot }}
                </div>
            </main>
        </div>
        @stack('scripts')
    </body>
</html>