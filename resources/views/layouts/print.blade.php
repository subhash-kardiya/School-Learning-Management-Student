<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Print Result')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="{{ asset('css/menu.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    @stack('css')

    <style>
        body {
            background: #fff;
        }

        .res-print-only-bar {
            /* Only visible on screen for the print page */
        }

        @media print {
            .res-print-only-bar {
                display: none !important;
            }

            body.res-student-print .res-shell {
                zoom: 0.75;
                transform-origin: top left;
            }

            body.res-student-print .card,
            body.res-student-print .res-total-box {
                margin-bottom: 2px !important;
                padding-top: 4px !important;
                padding-bottom: 4px !important;
                break-inside: avoid !important;
            }
        }
    </style>
</head>
<body class="res-print-page">
    <div class="main-content" style="margin:0; width:100%;">
        @yield('content')
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    @stack('scripts')
</body>
</html>
