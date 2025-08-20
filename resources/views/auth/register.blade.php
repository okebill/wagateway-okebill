<x-guest-layout>
    <div class="mb-10">
        <h1 class="text-3xl font-bold text-gray-900">Buat Akun Baru</h1>
        <p class="mt-2 text-base text-gray-600">Daftar untuk mengakses semua fitur</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-6">
        @csrf

        <!-- Name -->
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-user text-gray-400"></i>
                </div>
                <input id="name" 
                       name="name" 
                       type="text" 
                       value="{{ old('name') }}" 
                       required 
                       autofocus 
                       autocomplete="name" 
                       class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm input-auth"
                       placeholder="Masukkan nama lengkap Anda">
            </div>
            @error('name')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Email Address -->
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-envelope text-gray-400"></i>
                </div>
                <input id="email" 
                       name="email" 
                       type="email" 
                       value="{{ old('email') }}" 
                       required 
                       autocomplete="email" 
                       class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm input-auth"
                       placeholder="Masukkan email Anda">
            </div>
            @error('email')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-lock text-gray-400"></i>
                </div>
                <input id="password" 
                       name="password" 
                       type="password" 
                       required 
                       autocomplete="new-password" 
                       class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm input-auth"
                       placeholder="Buat password yang kuat">
            </div>
            @error('password')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Confirm Password -->
        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-lock text-gray-400"></i>
                </div>
                <input id="password_confirmation" 
                       name="password_confirmation" 
                       type="password" 
                       required 
                       autocomplete="new-password" 
                       class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm input-auth"
                       placeholder="Ulangi password Anda">
            </div>
            @error('password_confirmation')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Terms and Conditions -->
        <div class="flex items-start">
            <div class="flex items-center h-5">
                <input id="terms" name="terms" type="checkbox" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
            </div>
            <div class="ml-3 text-sm">
                <label for="terms" class="font-medium text-gray-700">Saya setuju dengan</label>
                <a href="#" class="font-medium text-indigo-600 hover:text-indigo-500"> Syarat & Ketentuan</a>
            </div>
        </div>

        <!-- Register Button -->
        <div>
            <button type="submit" 
                    class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-150 transform hover:-translate-y-0.5 btn-primary">
                <i class="fas fa-user-plus mr-2 self-center"></i>
                Daftar Sekarang
            </button>
        </div>
        
        <!-- Divider -->
        <div class="relative my-6">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-gray-300"></div>
            </div>
            <div class="relative flex justify-center text-sm">
                <span class="px-2 bg-white text-gray-500">atau</span>
            </div>
        </div>
        
        <!-- Login Link -->
        <div class="text-center">
            <p class="text-gray-600 mb-4">Sudah punya akun?</p>
            <a href="{{ route('login') }}" 
               class="inline-block w-full py-3 px-4 border border-indigo-600 rounded-lg text-indigo-600 font-medium hover:bg-indigo-50 transition-colors duration-150 ease-in-out text-center">
                <i class="fas fa-sign-in-alt mr-2"></i>
                Masuk Sekarang
            </a>
        </div>
    </form>
    
    <!-- Features -->
    <div class="mt-8 bg-gradient-to-r from-indigo-50 to-blue-50 rounded-lg border border-indigo-100 px-5 py-4">
        <h3 class="text-sm font-medium text-indigo-800 mb-3">Keuntungan Bergabung</h3>
        <ul class="space-y-2">
            <li class="flex text-sm">
                <i class="fas fa-check-circle text-green-500 mt-0.5 mr-2"></i>
                <span class="text-gray-700">Akses ke semua fitur manajemen</span>
            </li>
            <li class="flex text-sm">
                <i class="fas fa-check-circle text-green-500 mt-0.5 mr-2"></i>
                <span class="text-gray-700">Dashboard analitik real-time</span>
            </li>
            <li class="flex text-sm">
                <i class="fas fa-check-circle text-green-500 mt-0.5 mr-2"></i>
                <span class="text-gray-700">Dukungan teknis 24/7</span>
            </li>
        </ul>
    </div>
    
    <!-- Security Badge -->
    <div class="mt-8 text-center">
        <div class="inline-flex items-center space-x-1 text-gray-500 text-xs">
            <i class="fas fa-shield-alt"></i>
            <span>Secured by SSL Encryption</span>
        </div>
    </div>
</x-guest-layout>
