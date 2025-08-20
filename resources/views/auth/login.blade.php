<x-guest-layout>
    <div class="mb-10">
        <h1 class="text-3xl font-bold text-gray-900">Selamat Datang</h1>
        <p class="mt-2 text-base text-gray-600">Masuk ke akun Anda untuk melanjutkan</p>
    </div>
    
    <!-- Session Status -->
    @if (session('status'))
        <div class="mb-6 bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded-lg text-sm">
            {{ session('status') }}
        </div>
    @endif
    
    <form method="POST" action="{{ route('login') }}" class="space-y-6">
        @csrf
        
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
                       autofocus 
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
                       autocomplete="current-password" 
                       class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm input-auth"
                       placeholder="Masukkan password Anda">
            </div>
            @error('password')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        
        <!-- Remember Me & Forgot Password -->
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <input id="remember_me" 
                       name="remember" 
                       type="checkbox" 
                       class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                <label for="remember_me" class="ml-2 block text-sm text-gray-700">
                    Ingat saya
                </label>
            </div>
            
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" 
                   class="text-sm font-medium text-indigo-600 hover:text-indigo-500 transition duration-150 ease-in-out">
                    Lupa password?
                </a>
            @endif
        </div>
        
        <!-- Login Button -->
        <div>
            <button type="submit" 
                    class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-150 transform hover:-translate-y-0.5 btn-primary">
                <i class="fas fa-sign-in-alt mr-2 self-center"></i>
                Masuk
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
        
        <!-- Register Link -->
        <div class="text-center">
            <p class="text-gray-600 mb-4">Belum punya akun?</p>
            <a href="{{ route('register') }}" 
               class="inline-block w-full py-3 px-4 border border-indigo-600 rounded-lg text-indigo-600 font-medium hover:bg-indigo-50 transition-colors duration-150 ease-in-out text-center">
                <i class="fas fa-user-plus mr-2"></i>
                Daftar Sekarang
            </a>
        </div>
    </form>
    
    <!-- Demo Account Info -->
    <div class="mt-8 bg-gradient-to-r from-indigo-50 to-blue-50 rounded-lg overflow-hidden">
        <div class="px-5 py-4 border border-indigo-100">
            <div class="flex items-start">
                <div class="flex-shrink-0 pt-0.5">
                    <i class="fas fa-info-circle text-indigo-500"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-indigo-800">Akun Demo</h3>
                    <div class="mt-2 text-sm text-indigo-700 space-y-1">
                        <p class="flex">
                            <span class="font-semibold w-20">Email:</span> 
                            <code class="bg-white px-1.5 py-0.5 rounded border border-indigo-100 font-mono">test@example.com</code>
                        </p>
                        <p class="flex">
                            <span class="font-semibold w-20">Password:</span> 
                            <code class="bg-white px-1.5 py-0.5 rounded border border-indigo-100 font-mono">password</code>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Security Badge -->
    <div class="mt-8 text-center">
        <div class="inline-flex items-center space-x-1 text-gray-500 text-xs">
            <i class="fas fa-shield-alt"></i>
            <span>Secured by SSL Encryption</span>
        </div>
    </div>
</x-guest-layout>

