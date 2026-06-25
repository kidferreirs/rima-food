<!DOCTYPE html>

<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Rima Food</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

</head>

<body class="bg-gray-100">

    <x-rimafood.sidebar />

    <main class="ml-64 min-h-screen">

        {{ $slot }}

    </main>

</body>

</html>