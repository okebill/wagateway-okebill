<x-app-layout>
    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('users.index') }}" class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Kembali
                    </a>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 flex items-center space-x-2">
                            <i class="fas fa-user-plus text-indigo-600"></i>
                            <span>Tambah User Baru</span>
                        </h1>
                        <p class="mt-2 text-gray-600">Isi form berikut untuk menambahkan user baru ke sistem</p>
                    </div>
                </div>
            </div>

            <!-- Form -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                        <i class="fas fa-edit mr-2 text-indigo-600"></i>
                        Form Data User
                    </h3>
                </div>

                <form method="POST" action="{{ route('users.store') }}" class="p-6 space-y-6">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-user mr-1 text-gray-400"></i>
                                Nama Lengkap <span class="text-red-500">*</span>
                            </label>
                            <input id="name" 
                                   name="name" 
                                   type="text" 
                                   value="{{ old('name') }}" 
                                   required 
                                   autofocus 
                                   class="block w-full px-3 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('name') border-red-300 @enderror"
                                   placeholder="Masukkan nama lengkap">
                            @error('name')
                                <p class="mt-2 text-sm text-red-600 flex items-center">
                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-envelope mr-1 text-gray-400"></i>
                                Email <span class="text-red-500">*</span>
                            </label>
                            <input id="email" 
                                   name="email" 
                                   type="email" 
                                   value="{{ old('email') }}" 
                                   required 
                                   class="block w-full px-3 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('email') border-red-300 @enderror"
                                   placeholder="Masukkan alamat email">
                            @error('email')
                                <p class="mt-2 text-sm text-red-600 flex items-center">
                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-lock mr-1 text-gray-400"></i>
                                Password <span class="text-red-500">*</span>
                            </label>
                            <input id="password" 
                                   name="password" 
                                   type="password" 
                                   required 
                                   class="block w-full px-3 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('password') border-red-300 @enderror"
                                   placeholder="Masukkan password">
                            @error('password')
                                <p class="mt-2 text-sm text-red-600 flex items-center">
                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Password Confirmation -->
                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-lock mr-1 text-gray-400"></i>
                                Konfirmasi Password <span class="text-red-500">*</span>
                            </label>
                            <input id="password_confirmation" 
                                   name="password_confirmation" 
                                   type="password" 
                                   required 
                                   class="block w-full px-3 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                   placeholder="Ulangi password">
                        </div>

                        <!-- Status -->
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-toggle-on mr-1 text-gray-400"></i>
                                Status <span class="text-red-500">*</span>
                            </label>
                            <select id="status" 
                                    name="status" 
                                    required 
                                    class="block w-full px-3 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('status') border-red-300 @enderror">
                                <option value="">Pilih Status</option>
                                <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                                <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Non-aktif</option>
                            </select>
                            @error('status')
                                <p class="mt-2 text-sm text-red-600 flex items-center">
                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Limit Device -->
                        <div>
                            <label for="limit_device" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-mobile-alt mr-1 text-gray-400"></i>
                                Limit Device <span class="text-red-500">*</span>
                            </label>
                            <input id="limit_device" 
                                   name="limit_device" 
                                   type="number" 
                                   value="{{ old('limit_device', 3) }}" 
                                   required 
                                   min="1" 
                                   max="10"
                                   class="block w-full px-3 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('limit_device') border-red-300 @enderror"
                                   placeholder="Maksimal device yang bisa login">
                            <p class="mt-1 text-sm text-gray-500">Maksimal 10 device</p>
                            @error('limit_device')
                                <p class="mt-2 text-sm text-red-600 flex items-center">
                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>

                    <!-- Account Expiry -->
                    <div>
                        <label for="account_expires_at" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-calendar-alt mr-1 text-gray-400"></i>
                            Masa Aktif Akun
                        </label>
                        <input id="account_expires_at" 
                               name="account_expires_at" 
                               type="date" 
                               value="{{ old('account_expires_at') }}" 
                               min="{{ now()->addDay()->format('Y-m-d') }}"
                               class="block w-full px-3 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('account_expires_at') border-red-300 @enderror">
                        <p class="mt-1 text-sm text-gray-500">Kosongkan jika tidak ada batas waktu</p>
                        @error('account_expires_at')
                            <p class="mt-2 text-sm text-red-600 flex items-center">
                                <i class="fas fa-exclamation-circle mr-1"></i>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Submit Buttons -->
                    <div class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-200">
                        <a href="{{ route('users.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <i class="fas fa-times mr-2"></i>
                            Batal
                        </a>
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 border border-transparent rounded-lg font-semibold text-sm text-white uppercase tracking-widest hover:from-indigo-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-lg">
                            <i class="fas fa-save mr-2"></i>
                            Simpan User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout> 