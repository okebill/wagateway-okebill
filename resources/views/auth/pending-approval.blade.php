<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
        <div class="flex items-center justify-center mb-6">
            <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center">
                <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
        
        <h2 class="text-2xl font-bold text-center text-gray-900 dark:text-gray-100 mb-4">
            Menunggu Persetujuan
        </h2>
        
        <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">
                        Akun Anda Sedang Menunggu Persetujuan
                    </h3>
                    <div class="mt-2 text-sm text-yellow-700">
                        <p>
                            Terima kasih telah mendaftar! Akun Anda telah berhasil dibuat, namun masih perlu disetujui oleh administrator sebelum dapat digunakan.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-4 text-gray-600 dark:text-gray-400">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-blue-500 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div>
                    <strong>Apa yang terjadi selanjutnya?</strong>
                    <p class="text-sm mt-1">Administrator akan meninjau pendaftaran Anda dan memberikan persetujuan dalam 1-2 hari kerja.</p>
                </div>
            </div>

            <div class="flex items-start">
                <svg class="w-5 h-5 text-green-500 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
                <div>
                    <strong>Notifikasi Email</strong>
                    <p class="text-sm mt-1">Anda akan menerima email notification ketika akun Anda telah disetujui dan dapat digunakan.</p>
                </div>
            </div>

            <div class="flex items-start">
                <svg class="w-5 h-5 text-purple-500 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div>
                    <strong>Butuh Bantuan?</strong>
                    <p class="text-sm mt-1">Jika Anda memiliki pertanyaan atau memerlukan bantuan, silakan hubungi administrator melalui email atau kontak yang tersedia.</p>
                </div>
            </div>
        </div>

        <div class="mt-8 text-center">
            <a href="{{ route('login') }}" 
               class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12"></path>
                </svg>
                Kembali ke Halaman Login
            </a>
        </div>
    </div>
</x-guest-layout> 