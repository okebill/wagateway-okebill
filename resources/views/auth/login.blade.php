<x-guest-layout>
    <div class="mb-10">
        <h1 class="text-3xl font-bold text-gray-900"><center>Eglobaltechno</center></h1>
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
            </div>
        </div>
    </form>
    
    <!-- Security Badge -->
    <div class="mt-8 text-center">
        <div class="inline-flex items-center space-x-1 text-gray-500 text-xs">
            <i class="fas fa-shield-alt"></i>
            <span>Secured by SSL Encryption</span>
        </div>
    </div>
</x-guest-layout>

