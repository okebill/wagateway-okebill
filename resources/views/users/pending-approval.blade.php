<x-app-layout>
    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 flex items-center space-x-2">
                            <i class="fas fa-clock text-yellow-600"></i>
                            <span>User Pending Approval</span>
                        </h1>
                        <p class="mt-2 text-gray-600">Daftar user yang menunggu persetujuan administrator</p>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('users.index') }}" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-gray-600 to-gray-700 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:from-gray-700 hover:to-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-lg">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Kembali ke Manajemen User
                        </a>
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

            <!-- Pending Users Table -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-yellow-50 to-orange-50 border-b border-yellow-200">
                    <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                        <i class="fas fa-hourglass-half mr-2 text-yellow-600"></i>
                        User Menunggu Persetujuan ({{ $users->total() }} user)
                    </h3>
                </div>

                @if($users->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User Info</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Daftar</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($users as $user)
                                    <tr class="hover:bg-yellow-50 transition-colors duration-200">
                                        <!-- User Info -->
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="h-12 w-12 rounded-full bg-gradient-to-br from-yellow-400 to-orange-500 flex items-center justify-center">
                                                    <span class="text-white font-semibold text-lg">{{ substr($user->name, 0, 1) }}</span>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                                    <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                                    <div class="text-xs text-gray-400 mt-1">
                                                        <i class="fas fa-mobile-alt mr-1"></i>
                                                        Limit: {{ $user->limit_device }} device
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Registration Date -->
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ $user->created_at->format('d M Y') }}</div>
                                            <div class="text-sm text-gray-500">{{ $user->created_at->format('H:i') }}</div>
                                            <div class="text-xs text-gray-400">{{ $user->created_at->diffForHumans() }}</div>
                                        </td>

                                        <!-- Status -->
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="space-y-1">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    <i class="fas fa-clock mr-1"></i>
                                                    Menunggu Persetujuan
                                                </span>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                    <i class="fas fa-pause-circle mr-1"></i>
                                                    {{ ucfirst($user->status) }}
                                                </span>
                                            </div>
                                        </td>

                                        <!-- Actions -->
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex items-center justify-end space-x-3">
                                                <!-- Approve Button -->
                                                <form method="POST" action="{{ route('users.approve', $user) }}" class="inline">
                                                    @csrf
                                                    <button type="submit" 
                                                            class="inline-flex items-center px-3 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                                            onclick="return confirm('Apakah Anda yakin ingin menyetujui user {{ $user->name }}?')">
                                                        <i class="fas fa-check mr-2"></i>
                                                        Setujui
                                                    </button>
                                                </form>

                                                <!-- View Details -->
                                                <a href="{{ route('users.show', $user) }}" 
                                                   class="inline-flex items-center px-3 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                                    <i class="fas fa-eye mr-2"></i>
                                                    Detail
                                                </a>

                                                <!-- Delete User -->
                                                <form method="POST" action="{{ route('users.destroy', $user) }}" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus user {{ $user->name }}? Tindakan ini tidak dapat dibatalkan.')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="inline-flex items-center px-3 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                                        <i class="fas fa-trash mr-2"></i>
                                                        Hapus
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($users->hasPages())
                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                            {{ $users->links() }}
                        </div>
                    @endif

                @else
                    <!-- Empty State -->
                    <div class="px-6 py-12 text-center">
                        <div class="text-gray-500">
                            <div class="mx-auto h-24 w-24 bg-yellow-100 rounded-full flex items-center justify-center mb-4">
                                <i class="fas fa-check-circle text-4xl text-green-500"></i>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak Ada User Pending</h3>
                            <p class="text-sm text-gray-500 mb-4">
                                Saat ini tidak ada user yang menunggu persetujuan. Semua user telah diproses.
                            </p>
                            <a href="{{ route('users.index') }}" 
                               class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <i class="fas fa-users mr-2"></i>
                                Lihat Semua User
                            </a>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Help Information -->
            @if($users->count() > 0)
                <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-info-circle text-blue-400 text-xl"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">Petunjuk Approval User</h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <ul class="list-disc list-inside space-y-1">
                                    <li><strong>Setujui:</strong> User akan diaktifkan dan dapat login ke sistem</li>
                                    <li><strong>Detail:</strong> Lihat informasi lengkap user sebelum memberikan persetujuan</li>
                                    <li><strong>Hapus:</strong> Menghapus pendaftaran user secara permanen</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout> 