<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-100">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Sistema">
    <meta name="author" content="SakCode">
    <title>Sistema - @yield('title', 'Inicio')</title>

    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">


    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/styles.css') }}" rel="stylesheet">

    @stack('css-datatable')
    @stack('css')

    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>

    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body class="d-flex flex-column h-100 sb-nav-fixed">
    <div id="loading" style="display: none;">
        <div class="spinner-border" role="status">
            <span class="visually-hidden">Cargando...</span>
        </div>
    </div>

    <x-navigation-header />

    <div id="layoutSidenav" class="flex-grow-1">
        <x-navigation-menu />

        <div id="layoutSidenav_content">
            <main class="flex-shrink-0">
                <!-- Contenedor principal con padding -->
                <div class="container-fluid py-4">
                    @yield('content')
                </div>
            </main>

            <x-footer />
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/scripts.js') }}"></script>

    @stack('js')

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const links = document.querySelectorAll('a');
            const forms = document.querySelectorAll('form');

            links.forEach(link => {
                link.addEventListener('click', function() {
                    document.getElementById('loading').style.display = 'block';
                });
            });

            forms.forEach(form => {
                form.addEventListener('submit', function() {
                    document.getElementById('loading').style.display = 'block';
                });
            });
        });
    </script>
</body>
</html>

