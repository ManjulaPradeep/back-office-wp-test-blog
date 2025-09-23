<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- @todo [2025-09 MP]: Plsease intall dependancy except from CDN --}}
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@7.x/css/materialdesignicons.min.css" rel="stylesheet">

    <title>Dashboard</title>
    @vite('resources/css/app.css')
</head>
<body>
    <div id="app" data-user='@json(auth()->user())'></div>

    @vite('resources/js/app.js')
</body>
</html>

