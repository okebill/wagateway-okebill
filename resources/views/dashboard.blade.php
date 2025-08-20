<x-app-layout>
    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            <!-- Warning Messages -->
            @if(session('warning'))
                <div class="bg-yellow-50 border border-yellow-200 text-yellow-700 px-4 py-3 rounded-lg flex items-center">
                    <i class="fas fa-exclamation-triangle mr-2 text-yellow-600"></i>
                    {{ session('warning') }}
                </div>
            @endif

            @if(Auth::user()->account_expires_at && Auth::user()->account_expires_at->diffInDays(now()) <= 3)
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg flex items-center">
                    <i class="fas fa-clock mr-2 text-red-600"></i>
                    <div>
                        <strong>Akun Akan Segera Berakhir!</strong><br>
                        Akun Anda akan kedaluwarsa pada {{ Auth::user()->account_expires_at->format('d M Y') }} 
                        ({{ Auth::user()->account_expires_at->diffForHumans() }}). 
                        Silakan hubungi administrator untuk perpanjangan.
                    </div>
                </div>
            @endif

            <!-- Welcome Section -->
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-2xl shadow-xl overflow-hidden">
                <div class="px-8 py-12 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-3xl font-bold mb-2">
                                Halo, {{ Auth::user()->name }}! 👋
                            </h3>
                            <p class="text-indigo-100 text-lg">
                                Semoga hari Anda produktif dan menyenangkan
                            </p>
                        </div>
                        <div class="hidden md:block">
                            <div class="h-24 w-24 rounded-full bg-white/10 backdrop-blur-sm flex items-center justify-center">
                                <i class="fas fa-user text-4xl text-white/80"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <!-- Account Info Card -->
                <div class="bg-white rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300 overflow-hidden">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="h-12 w-12 rounded-lg bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center">
                                <i class="fas fa-user-circle text-white text-xl"></i>
                            </div>
                            @if(Auth::user()->isExpired())
                                <span class="text-xs font-medium text-red-600 bg-red-50 px-2 py-1 rounded-full">Expired</span>
                            @elseif(Auth::user()->status === 'active')
                                <span class="text-xs font-medium text-green-600 bg-green-50 px-2 py-1 rounded-full">Aktif</span>
                            @else
                                <span class="text-xs font-medium text-gray-600 bg-gray-50 px-2 py-1 rounded-full">Non-aktif</span>
                            @endif
                        </div>
                        <h4 class="font-semibold text-gray-800 mb-3">Informasi Akun</h4>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Nama:</span>
                                <span class="font-medium text-gray-900">{{ Auth::user()->name }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Email:</span>
                                <span class="font-medium text-gray-900">{{ Str::limit(Auth::user()->email, 20) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Bergabung:</span>
                                <span class="font-medium text-gray-900">{{ Auth::user()->created_at->format('M Y') }}</span>
                            </div>
                            @if(Auth::user()->account_expires_at)
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Berakhir:</span>
                                    <span class="font-medium {{ Auth::user()->isExpired() ? 'text-red-600' : 'text-gray-900' }}">
                                        {{ Auth::user()->account_expires_at->format('M Y') }}
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Quick Actions Card -->
                <div class="bg-white rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300 overflow-hidden">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="h-12 w-12 rounded-lg bg-gradient-to-br from-green-500 to-green-600 flex items-center justify-center">
                                <i class="fas fa-bolt text-white text-xl"></i>
                            </div>
                            <span class="text-xs font-medium text-green-600 bg-green-50 px-2 py-1 rounded-full">Aksi</span>
                        </div>
                        <h4 class="font-semibold text-gray-800 mb-4">Aksi Cepat</h4>
                        <div class="space-y-3">
                            <a href="{{ route('profile.edit') }}" class="flex items-center space-x-3 p-2 rounded-lg hover:bg-gray-50 transition-colors duration-200">
                                <div class="h-8 w-8 rounded-lg bg-indigo-100 flex items-center justify-center">
                                    <i class="fas fa-edit text-indigo-600 text-sm"></i>
                                </div>
                                <span class="text-sm font-medium text-gray-700">Edit Profile</span>
                            </a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full flex items-center space-x-3 p-2 rounded-lg hover:bg-red-50 transition-colors duration-200">
                                    <div class="h-8 w-8 rounded-lg bg-red-100 flex items-center justify-center">
                                        <i class="fas fa-sign-out-alt text-red-600 text-sm"></i>
                                    </div>
                                    <span class="text-sm font-medium text-red-600">Logout</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- User Management Card -->
                <div class="bg-white rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300 overflow-hidden">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="h-12 w-12 rounded-lg bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center">
                                <i class="fas fa-users text-white text-xl"></i>
                            </div>
                            <span class="text-xs font-medium text-amber-600 bg-amber-50 px-2 py-1 rounded-full">Admin</span>
                        </div>
                        <h4 class="font-semibold text-gray-800 mb-4">Manajemen User</h4>
                        <div class="space-y-3">
                            <a href="{{ route('users.index') }}" class="flex items-center space-x-3 p-2 rounded-lg hover:bg-gray-50 transition-colors duration-200">
                                <div class="h-8 w-8 rounded-lg bg-amber-100 flex items-center justify-center">
                                    <i class="fas fa-list text-amber-600 text-sm"></i>
                                </div>
                                <span class="text-sm font-medium text-gray-700">Daftar User</span>
                            </a>
                            <a href="{{ route('users.create') }}" class="flex items-center space-x-3 p-2 rounded-lg hover:bg-gray-50 transition-colors duration-200">
                                <div class="h-8 w-8 rounded-lg bg-green-100 flex items-center justify-center">
                                    <i class="fas fa-plus text-green-600 text-sm"></i>
                                </div>
                                <span class="text-sm font-medium text-gray-700">Tambah User</span>
                            </a>
                        </div>
                                    </div>
            </div>

            <!-- WhatsApp Devices Card -->
            <div class="bg-white rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300 overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="h-12 w-12 rounded-lg bg-gradient-to-br from-emerald-500 to-emerald-600 flex items-center justify-center">
                            <i class="fab fa-whatsapp text-white text-xl"></i>
                        </div>
                        <span class="text-xs font-medium text-emerald-600 bg-emerald-50 px-2 py-1 rounded-full">WhatsApp</span>
                    </div>
                    <h4 class="font-semibold text-gray-800 mb-4">WhatsApp Devices</h4>
                    <div class="space-y-3">
                        <a href="{{ route('whatsapp.devices.index') }}" class="flex items-center space-x-3 p-2 rounded-lg hover:bg-gray-50 transition-colors duration-200">
                            <div class="h-8 w-8 rounded-lg bg-emerald-100 flex items-center justify-center">
                                <i class="fas fa-mobile-alt text-emerald-600 text-sm"></i>
                            </div>
                            <span class="text-sm font-medium text-gray-700">Kelola Devices</span>
                        </a>
                        <a href="{{ route('whatsapp.devices.create') }}" class="flex items-center space-x-3 p-2 rounded-lg hover:bg-gray-50 transition-colors duration-200">
                            <div class="h-8 w-8 rounded-lg bg-green-100 flex items-center justify-center">
                                <i class="fas fa-plus text-green-600 text-sm"></i>
                            </div>
                            <span class="text-sm font-medium text-gray-700">Tambah Device</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- System Status Card -->
            <div class="bg-white rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300 overflow-hidden">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="h-12 w-12 rounded-lg bg-gradient-to-br from-purple-500 to-purple-600 flex items-center justify-center">
                                <i class="fas fa-server text-white text-xl"></i>
                            </div>
                            <span class="text-xs font-medium text-purple-600 bg-purple-50 px-2 py-1 rounded-full">Status</span>
                        </div>
                        <h4 class="font-semibold text-gray-800 mb-4">Status Sistem</h4>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Server</span>
                                <div class="flex items-center space-x-2">
                                    <div class="h-2 w-2 rounded-full bg-green-500"></div>
                                    <span class="text-xs font-medium text-green-600">Online</span>
                                </div>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Database</span>
                                <div class="flex items-center space-x-2">
                                    <div class="h-2 w-2 rounded-full bg-green-500"></div>
                                    <span class="text-xs font-medium text-green-600">Connected</span>
                                </div>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Cache</span>
                                <div class="flex items-center space-x-2">
                                    <div class="h-2 w-2 rounded-full bg-green-500"></div>
                                    <span class="text-xs font-medium text-green-600">Active</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Features Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Available Features -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                    <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                        <h4 class="font-semibold text-gray-800 flex items-center space-x-2">
                            <i class="fas fa-check-circle text-green-500"></i>
                            <span>Fitur yang Tersedia</span>
                        </h4>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 gap-3">
                            <div class="flex items-center space-x-3 p-3 bg-green-50 rounded-lg">
                                <i class="fas fa-check text-green-600"></i>
                                <span class="text-sm text-gray-700">Autentikasi dengan Email & Password</span>
                            </div>
                            <div class="flex items-center space-x-3 p-3 bg-green-50 rounded-lg">
                                <i class="fas fa-check text-green-600"></i>
                                <span class="text-sm text-gray-700">Registrasi Akun Baru</span>
                            </div>
                            <div class="flex items-center space-x-3 p-3 bg-green-50 rounded-lg">
                                <i class="fas fa-check text-green-600"></i>
                                <span class="text-sm text-gray-700">Reset Password via Email</span>
                            </div>
                            <div class="flex items-center space-x-3 p-3 bg-green-50 rounded-lg">
                                <i class="fas fa-check text-green-600"></i>
                                <span class="text-sm text-gray-700">Remember Me Functionality</span>
                            </div>
                            <div class="flex items-center space-x-3 p-3 bg-green-50 rounded-lg">
                                <i class="fas fa-check text-green-600"></i>
                                <span class="text-sm text-gray-700">Manajemen Profile</span>
                            </div>
                            <div class="flex items-center space-x-3 p-3 bg-amber-50 rounded-lg">
                                <i class="fas fa-star text-amber-600"></i>
                                <span class="text-sm text-gray-700">Manajemen User dengan Status & Limit Device</span>
                            </div>
                            <div class="flex items-center space-x-3 p-3 bg-emerald-50 rounded-lg">
                                <i class="fab fa-whatsapp text-emerald-600"></i>
                                <span class="text-sm text-gray-700">Manajemen WhatsApp Devices</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                    <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                        <h4 class="font-semibold text-gray-800 flex items-center space-x-2">
                            <i class="fas fa-clock text-blue-500"></i>
                            <span>Aktivitas Terbaru</span>
                        </h4>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <div class="flex items-start space-x-3">
                                <div class="h-8 w-8 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0 mt-1">
                                    <i class="fas fa-sign-in-alt text-green-600 text-xs"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900">Login berhasil</p>
                                    <p class="text-xs text-gray-500">{{ now()->format('H:i') }} - Dari {{ request()->ip() }}</p>
                                </div>
                            </div>
                            <div class="flex items-start space-x-3">
                                <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0 mt-1">
                                    <i class="fas fa-user text-blue-600 text-xs"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900">Akun dibuat</p>
                                    <p class="text-xs text-gray-500">{{ Auth::user()->created_at->format('d M Y, H:i') }}</p>
                                </div>
                            </div>
                            <div class="flex items-start space-x-3">
                                <div class="h-8 w-8 rounded-full bg-purple-100 flex items-center justify-center flex-shrink-0 mt-1">
                                    <i class="fas fa-shield-alt text-purple-600 text-xs"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900">Keamanan diverifikasi</p>
                                    <p class="text-xs text-gray-500">Enkripsi SSL aktif</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Info -->
            <div class="text-center py-8">
                <div class="inline-flex items-center space-x-2 px-4 py-2 bg-white rounded-full shadow-md">
                    <i class="fas fa-shield-alt text-green-500"></i>
                    <span class="text-sm text-gray-600">Secured by SSL Encryption</span>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
