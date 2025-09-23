{{-- <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    @vite(['resources/js/app.js', 'resources/css/app.css'])
</head>
<body>
    <div id="app">
        <dashboard :user="{{ auth()->user()->toJson() }}"></dashboard>
    </div>
</body>
</html> --}}


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    @vite('resources/css/app.css')
</head>
<body>
    {{-- Vue root element --}}
    <div id="app" data-user='@json(auth()->user())'></div>

    {{-- Include compiled JS --}}
    @vite('resources/js/app.js')
</body>
</html>

