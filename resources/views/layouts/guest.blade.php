<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-100">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        
        <!-- Icons -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <!-- Fallback Font Awesome -->
        <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <!-- Fallback CSS in case Vite assets don't load -->
        <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
        
        <!-- Fallback styles in case Vite assets fail to load -->
        <style>
            body {
                font-family: 'Plus Jakarta Sans', sans-serif;
            }
            .container-auth {
                max-width: 400px;
                margin: 0 auto;
                padding: 1.5rem;
            }
            .input-auth {
                width: 100%;
                padding: 0.75rem 1rem 0.75rem 2.5rem;
                border: 1px solid #d1d5db;
                border-radius: 0.5rem;
                font-size: 0.875rem;
            }
            .btn-primary {
                background: linear-gradient(to right, #4f46e5, #4338ca);
                color: white;
                padding: 0.75rem 1rem;
                border-radius: 0.5rem;
                font-weight: 500;
                width: 100%;
                display: flex;
                justify-content: center;
                align-items: center;
                transition: all 150ms;
            }
            .btn-primary:hover {
                background: linear-gradient(to right, #4338ca, #3730a3);
                transform: translateY(-1px);
            }
        </style>
    </head>
    <body class="h-full font-sans antialiased">
        <div class="min-h-screen flex flex-col justify-center py-12 sm:px-6 lg:px-8 bg-gray-50">
            <!-- Logo centered at top -->
            <div class="sm:mx-auto sm:w-full sm:max-w-md">
                <div class="flex justify-center">
                    <div class="h-12 w-12 rounded-xl bg-indigo-600 flex items-center justify-center shadow-lg">
                        <i class="fas fa-layer-group text-white text-2xl"></i>
                    </div>
                </div>
                <h1 class="mt-4 text-center text-2xl font-bold text-gray-900">{{ config('app.name', 'Laravel') }}</h1>
            </div>

            <!-- Form centered -->
            <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
                <div class="bg-white py-8 px-4 shadow-xl rounded-lg sm:px-10">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
