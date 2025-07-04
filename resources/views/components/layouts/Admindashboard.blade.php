<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="{{ asset('assets/js/jqery_min.js') }}"></script>
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/images/favicon.png') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
        integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    @livewireStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])


    <title>@yield('title', 'Page Title')</title>
</head>

<body>
    <livewire:inc.header />
    <main>
        <div class="container Admindashboardlayout">
            <div class="inner d-flex dashboard-main gap-4 pt-5 ">
                <livewire:inc.Admindashboardsidebar />
                {{ $slot }}
            </div>
        </div>
    </main>
    @livewireScripts
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    @stack('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>

</html>
