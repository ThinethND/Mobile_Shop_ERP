<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Forbidden</title>

    @vite('resources/css/app.css')

    <style>
        .shape { position: absolute; opacity: 0.9; }
        .shape-yellow { width:120px;height:120px;background:#FDE68A;top:60px;left:50%;transform:translateX(-200px) rotate(45deg);clip-path:polygon(0 0,100% 0,100% 100%);border-radius:12px; }
        .shape-blue { width:70px;height:70px;background:#BFDBFE;bottom:140px;left:200px;transform:rotate(45deg);border-radius:10px; }
        .shape-dark { width:150px;height:30px;background:#111827;top:200px;right:180px;transform:rotate(-40deg);border-radius:6px; }
        .big-number { font-size:33vw;font-weight:800;  color: rgba(56, 189, 248, 0.04);position:absolute;bottom:-50px;left:50%;transform:translateX(-50%);pointer-events:none;user-select:none; }
    </style>
</head>

<body class="bg-white flex flex-col items-center justify-center min-h-screen overflow-hidden relative">

    <div class="text-center z-10 px-4">
        <h1 class="text-2xl sm:text-3xl font-semibold text-gray-900 mb-2">
            Access Denied
        </h1>

        <p class="text-gray-500 mb-6 text-base sm:text-lg">
            You don’t have permission to view this page.
        </p>

        <a href="{{ url('/') }}"
           class="inline-block bg-black text-white text-base font-medium px-6 py-3 rounded-full shadow hover:bg-gray-800 transition">
            Go back home
        </a>
    </div>

    <div class="big-number">403</div>

</body>
</html>
