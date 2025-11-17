<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 font-sans antialiased">
<div class="min-h-screen bg-gray-100">

    {{-- ✅ Navigation Bar --}}
    <nav x-data="{ open: false, master: false, transaksi: false, laporan: false }" class="bg-white border-b border-gray-200 shadow-sm relative z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <!-- Logo -->
                    <div class="shrink-0 flex items-center">
                        <a href="{{ route('dashboard') }}">
                            <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                        </a>
                    </div>

                    <!-- Navigation Links -->
                    <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex items-center">

                        {{-- DASHBOARD --}}
                        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                            {{ __('Dashboard') }}
                        </x-nav-link>

                        {{-- MASTER DATA --}}
                        @if(in_array(Auth::user()->idrole, [1,2]))
                            <div class="relative" x-data="{ openMaster: false }">
                                <button @click="openMaster = ! openMaster"
                                    class="inline-flex items-center text-gray-700 hover:text-indigo-600 font-semibold focus:outline-none">
                                    Master Data
                                    <svg class="ml-1 w-4 h-4 transition-transform duration-300"
                                        :class="{'rotate-180': openMaster}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                <div x-show="openMaster" @click.outside="openMaster = false"
                                     x-transition.origin.top.left
                                     class="absolute bg-white border rounded-md shadow-md mt-2 py-2 min-w-[160px]">
                                    <x-dropdown-link :href="route('barang.index')">Barang</x-dropdown-link>
                                    <x-dropdown-link :href="route('vendor.index')">Vendor</x-dropdown-link>
                                    <x-dropdown-link :href="route('margin_penjualan.index')">Margin Penjualan</x-dropdown-link>
                                    <x-dropdown-link :href="route('satuan.index')">Satuan</x-dropdown-link>
                                    <x-dropdown-link :href="route('jenis-barang.index')">Jenis Barang</x-dropdown-link>
                                    @if(Auth::user()->idrole == 1)
                                        <x-dropdown-link :href="route('role.index')">Role</x-dropdown-link>
                                        <x-dropdown-link :href="route('user.index')">User</x-dropdown-link>
                                    @endif
                                </div>
                            </div>
                        @endif

                        {{-- TRANSAKSI --}}
                        @if(in_array(Auth::user()->idrole, [1,2,3]))
                            <div class="relative" x-data="{ openTransaksi: false }">
                                <button @click="openTransaksi = ! openTransaksi"
                                    class="inline-flex items-center text-gray-700 hover:text-indigo-600 font-semibold focus:outline-none">
                                    Transaksi
                                    <svg class="ml-1 w-4 h-4 transition-transform duration-300"
                                        :class="{'rotate-180': openTransaksi}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                <div x-show="openTransaksi" @click.outside="openTransaksi = false"
                                     x-transition.origin.top.left
                                     class="absolute bg-white border rounded-md shadow-md mt-2 py-2 min-w-[180px]">
                                    <x-dropdown-link :href="route('pengadaan.index')">Pengadaan</x-dropdown-link>
                                    <x-dropdown-link :href="route('penerimaan.index')">Penerimaan</x-dropdown-link>
                                        <x-dropdown-link href="{{ route('penjualan.index') }}">Penjualan</x-dropdown-link>
                                </div>
                            </div>
                        @endif

                        {{-- LAPORAN --}}
                        <div class="relative" x-data="{ openLaporan: false }">
                            <button @click="openLaporan = ! openLaporan"
                                class="inline-flex items-center text-gray-700 hover:text-indigo-600 font-semibold focus:outline-none">
                                Laporan
                                <svg class="ml-1 w-4 h-4 transition-transform duration-300"
                                    :class="{'rotate-180': openLaporan}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div x-show="openLaporan" @click.outside="openLaporan = false"
                                 x-transition.origin.top.left
                                 class="absolute bg-white border rounded-md shadow-md mt-2 py-2 min-w-[180px]">
                                <x-dropdown-link href="#">Laporan Stok</x-dropdown-link>
                                <x-dropdown-link href="#">Laporan Pengadaan</x-dropdown-link>
                                <x-dropdown-link href="#">Laporan Penjualan</x-dropdown-link>
                                @if(in_array(Auth::user()->idrole, [1,2]))
                                    <x-dropdown-link href="#">Aktivitas User</x-dropdown-link>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Settings Dropdown -->
                <div class="hidden sm:flex sm:items-center sm:ms-6">
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                                <div>{{ Auth::user()->username }}</div>
                                <div class="ms-1">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">{{ __('Profile') }}</x-dropdown-link>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                </div>

                <!-- Hamburger (Mobile) -->
                <div class="-me-2 flex items-center sm:hidden">
                    <button @click="open = ! open"
                            class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex"
                                  stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 6h16M4 12h16M4 18h16" />
                            <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden"
                                  stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    {{-- ✅ MAIN CONTENT --}}
    <main class="py-6 px-8">
        @yield('content')
    </main>
</div>
</body>
</html>
