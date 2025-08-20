<nav x-data="{ open: false }" class="bg-white/95 backdrop-blur-sm border-b border-gray-100 shadow-sm">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center space-x-2">
                        <div class="h-8 w-8 rounded-lg bg-gradient-to-br from-indigo-600 to-purple-600 flex items-center justify-center shadow-lg">
                            <i class="fas fa-layer-group text-white text-sm"></i>
                        </div>
                        <span class="text-xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">{{ config('app.name', 'Laravel') }}</span>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" class="flex items-center space-x-2">
                        <i class="fas fa-home text-sm"></i>
                        <span>{{ __('Dashboard') }}</span>
                    </x-nav-link>
                    
                    @if(auth()->user()->isAdmin())
                        <x-nav-link :href="route('users.index')" :active="request()->routeIs('users.*')" class="flex items-center space-x-2">
                            <i class="fas fa-users text-sm"></i>
                            <span>User Management</span>
                        </x-nav-link>
                    @endif
                    
                    <x-nav-link :href="route('whatsapp.devices.index')" :active="request()->routeIs('whatsapp.devices.*')" class="flex items-center space-x-2">
                        <i class="fab fa-whatsapp text-sm"></i>
                        <span>WhatsApp Devices</span>
                    </x-nav-link>
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <!-- Role Badge -->
                @if(auth()->user()->isAdmin())
                    <div class="mr-3">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                            <i class="fas fa-crown mr-1"></i>
                            Admin
                        </span>
                    </div>
                @endif
                
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-4 py-2 border border-transparent text-sm leading-4 font-medium rounded-lg text-gray-600 bg-white hover:bg-gray-50 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm">
                            <div class="flex items-center space-x-2">
                                <div class="h-8 w-8 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center">
                                    <span class="text-white text-sm font-semibold">{{ substr(Auth::user()->name, 0, 1) }}</span>
                                </div>
                                <div class="text-left hidden md:block">
                                    <div class="text-sm font-medium">{{ Auth::user()->name }}</div>
                                </div>
                            </div>
                            <div class="ms-2">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')" class="flex items-center space-x-2">
                            <i class="fas fa-user-edit text-gray-400"></i>
                            <span>{{ __('Profile') }}</span>
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();"
                                    class="flex items-center space-x-2 text-red-600 hover:text-red-700">
                                <i class="fas fa-sign-out-alt text-red-400"></i>
                                <span>{{ __('Log Out') }}</span>
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" class="flex items-center space-x-2">
                <i class="fas fa-home text-sm"></i>
                <span>{{ __('Dashboard') }}</span>
            </x-responsive-nav-link>
            
            @if(auth()->user()->isAdmin())
                <x-responsive-nav-link :href="route('users.index')" :active="request()->routeIs('users.*')" class="flex items-center space-x-2">
                    <i class="fas fa-users text-sm"></i>
                    <span>User Management</span>
                </x-responsive-nav-link>
            @endif
            
            <x-responsive-nav-link :href="route('whatsapp.devices.index')" :active="request()->routeIs('whatsapp.devices.*')" class="flex items-center space-x-2">
                <i class="fab fa-whatsapp text-sm"></i>
                <span>WhatsApp Devices</span>
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="flex items-center space-x-3">
                    <div class="h-10 w-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center">
                        <span class="text-white font-semibold">{{ substr(Auth::user()->name, 0, 1) }}</span>
                    </div>
                    <div>
                        <div class="font-medium text-base text-gray-800 flex items-center space-x-2">
                            <span>{{ Auth::user()->name }}</span>
                            @if(auth()->user()->isAdmin())
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800">
                                    <i class="fas fa-crown mr-1"></i>
                                    Admin
                                </span>
                            @endif
                        </div>
                        <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
                    </div>
                </div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')" class="flex items-center space-x-2">
                    <i class="fas fa-user-edit text-gray-400"></i>
                    <span>{{ __('Profile') }}</span>
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();"
                            class="flex items-center space-x-2 text-red-600">
                        <i class="fas fa-sign-out-alt text-red-400"></i>
                        <span>{{ __('Log Out') }}</span>
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
