<x-app-layout>
    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 flex items-center space-x-2">
                            <i class="fas fa-users text-indigo-600"></i>
                            <span>Manajemen User</span>
                        </h1>
                        <p class="mt-2 text-gray-600">Kelola semua user dalam sistem</p>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('users.pending-approval') }}" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-yellow-500 to-orange-500 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:from-yellow-600 hover:to-orange-600 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-lg">
                            <i class="fas fa-clock mr-2"></i>
                            Pending Approval ({{ $stats['pending'] }})
                        </a>
                        <a href="{{ route('users.create') }}" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:from-indigo-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-lg">
                            <i class="fas fa-plus mr-2"></i>
                            Tambah User
                        </a>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-8">
                <div class="bg-white overflow-hidden shadow-lg rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-100 rounded-md flex items-center justify-center">
                                    <i class="fas fa-users text-blue-600"></i>
                                </div>
                            </div>
                            <div class="ml-3 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Total User</dt>
                                    <dd class="text-lg font-semibold text-gray-900">{{ $stats['total'] }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-lg rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-green-100 rounded-md flex items-center justify-center">
                                    <i class="fas fa-check-circle text-green-600"></i>
                                </div>
                            </div>
                            <div class="ml-3 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Disetujui</dt>
                                    <dd class="text-lg font-semibold text-gray-900">{{ $stats['approved'] }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-lg rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-yellow-100 rounded-md flex items-center justify-center">
                                    <i class="fas fa-clock text-yellow-600"></i>
                                </div>
                            </div>
                            <div class="ml-3 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Pending</dt>
                                    <dd class="text-lg font-semibold text-gray-900">{{ $stats['pending'] }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-lg rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-indigo-100 rounded-md flex items-center justify-center">
                                    <i class="fas fa-user-check text-indigo-600"></i>
                                </div>
                            </div>
                            <div class="ml-3 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Aktif</dt>
                                    <dd class="text-lg font-semibold text-gray-900">{{ $stats['active'] }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-lg rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-purple-100 rounded-md flex items-center justify-center">
                                    <i class="fas fa-crown text-purple-600"></i>
                                </div>
                            </div>
                            <div class="ml-3 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Admin</dt>
                                    <dd class="text-lg font-semibold text-gray-900">{{ $stats['admins'] }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Success/Error Messages -->
            @if(session('success'))
                <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg flex items-center">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    {{ session('error') }}
                </div>
            @endif

            @if(session('warning'))
                <div class="mb-6 bg-yellow-50 border border-yellow-200 text-yellow-700 px-4 py-3 rounded-lg flex items-center">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    {{ session('warning') }}
                </div>
            @endif

            <!-- Users Table -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                        <i class="fas fa-table mr-2 text-indigo-600"></i>
                        Daftar User ({{ $users->total() }} total)
                    </h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role & Approval</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Limit Device</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Masa Aktif</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Terdaftar</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($users as $user)
                                <tr class="hover:bg-gray-50 transition-colors duration-200">
                                    <!-- User Info -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="h-10 w-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center">
                                                <span class="text-white font-semibold text-sm">{{ substr($user->name, 0, 1) }}</span>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                                <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Role & Approval -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="space-y-1">
                                            <!-- Role Badge -->
                                            @if($user->role === 'admin')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                    <i class="fas fa-crown mr-1"></i>
                                                    Admin
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    <i class="fas fa-user mr-1"></i>
                                                    User
                                                </span>
                                            @endif
                                            
                                            <!-- Approval Badge -->
                                            @if($user->isApproved())
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    <i class="fas fa-check mr-1"></i>
                                                    Disetujui
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    <i class="fas fa-clock mr-1"></i>
                                                    Pending
                                                </span>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Status -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            @if($user->status === 'active')
                                                @if($user->isExpired())
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                        <i class="fas fa-times-circle mr-1"></i>
                                                        Expired
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                        <i class="fas fa-check-circle mr-1"></i>
                                                        Aktif
                                                    </span>
                                                @endif
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                    <i class="fas fa-pause-circle mr-1"></i>
                                                    Non-aktif
                                                </span>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Limit Device -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <i class="fas fa-mobile-alt text-gray-400 mr-2"></i>
                                            <span class="text-sm text-gray-900">{{ $user->limit_device }} device</span>
                                        </div>
                                    </td>

                                    <!-- Account Expiry -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($user->account_expires_at)
                                            <div class="text-sm">
                                                <div class="text-gray-900">{{ $user->account_expires_at->format('d M Y') }}</div>
                                                <div class="text-gray-500 text-xs">
                                                    @if($user->account_expires_at->isPast())
                                                        <span class="text-red-600">Sudah expired</span>
                                                    @else
                                                        {{ $user->account_expires_at->diffForHumans() }}
                                                    @endif
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-sm text-gray-500 italic">Tidak ada batas</span>
                                        @endif
                                    </td>

                                    <!-- Registration Date -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $user->created_at->format('d M Y') }}</div>
                                        <div class="text-sm text-gray-500">{{ $user->created_at->format('H:i') }}</div>
                                    </td>

                                    <!-- Actions -->
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end space-x-2">
                                            <!-- Approval/Rejection Actions -->
                                            @if($user->role !== 'admin')
                                                @if($user->isApproved())
                                                    <form method="POST" action="{{ route('users.reject', $user) }}" class="inline">
                                                        @csrf
                                                        <button type="submit" class="text-orange-600 hover:text-orange-900 transition-colors duration-200" title="Cabut Persetujuan" onclick="return confirm('Apakah Anda yakin ingin mencabut persetujuan user ini?')">
                                                            <i class="fas fa-times-circle"></i>
                                                        </button>
                                                    </form>
                                                @else
                                                    <form method="POST" action="{{ route('users.approve', $user) }}" class="inline">
                                                        @csrf
                                                        <button type="submit" class="text-green-600 hover:text-green-900 transition-colors duration-200" title="Setujui User">
                                                            <i class="fas fa-check-circle"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            @endif

                                            <!-- View Button -->
                                            <a href="{{ route('users.show', $user) }}" class="text-indigo-600 hover:text-indigo-900 transition-colors duration-200" title="Lihat Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>

                                            <!-- Edit Button -->
                                            <a href="{{ route('users.edit', $user) }}" class="text-green-600 hover:text-green-900 transition-colors duration-200" title="Edit User">
                                                <i class="fas fa-edit"></i>
                                            </a>

                                            <!-- Toggle Status -->
                                            @if($user->id !== auth()->id())
                                                <form method="POST" action="{{ route('users.toggle-status', $user) }}" class="inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="text-blue-600 hover:text-blue-900 transition-colors duration-200" title="{{ $user->status === 'active' ? 'Nonaktifkan' : 'Aktifkan' }}">
                                                        <i class="fas fa-{{ $user->status === 'active' ? 'pause' : 'play' }}"></i>
                                                    </button>
                                                </form>
                                            @endif

                                            <!-- Delete Button -->
                                            @if($user->id !== auth()->id())
                                                <form method="POST" action="{{ route('users.destroy', $user) }}" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus user ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900 transition-colors duration-200" title="Hapus User">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-8 text-center">
                                        <div class="text-gray-500">
                                            <i class="fas fa-users text-4xl mb-4 text-gray-300"></i>
                                            <p class="text-lg">Belum ada user</p>
                                            <p class="text-sm">Tambahkan user pertama dengan klik tombol "Tambah User"</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($users->hasPages())
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                        {{ $users->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout> 