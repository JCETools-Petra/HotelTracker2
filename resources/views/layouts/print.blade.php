<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Cetak Dokumen' }}</title>
    @vite(['resources/css/app.css'])
    <style>
        body {
            background-color: #ffffff !important;
        }
    </style>
</head>
<body>
    <div class="p-8">
        {{ $slot }}
    </div>
</body>
</html>