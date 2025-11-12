<body class="bg-blue-50 font-sans text-gray-900 min-h-screen" style="font-family: 'Inter', sans-serif;">
    ```


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema Médico</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
</head>

    <body class="bg-blue-100 font-sans text-gray-900 min-h-screen" style="font-family: 'Inter', sans-serif;">

    <header class="bg-gradient-to-r from-blue-700 to-blue-900 shadow-xl border-b-4 border-blue-400 sticky top-0 z-50 backdrop-blur-md">
        <div class="container mx-auto px-6 py-6 flex justify-between items-center">
            
            <div class="flex items-center space-x-3">
                <div class="bg-white text-blue-700 w-11 h-11 rounded-full flex items-center justify-center font-extrabold text-2xl shadow-md animate-pulse">+</div>
                <h1 class="text-2xl font-extrabold tracking-wide text-white">Centro de Salud</h1>
            </div>

            <?php if (isUserLoggedIn()): ?>
                <div class="flex items-center space-x-4">
                    <span class="hidden md:inline text-sm bg-white/10 px-3 py-1 rounded-lg backdrop-blur-sm text-gray-100">👋 Hola, 
                        <strong><?php echo htmlspecialchars($_SESSION['nombre']); ?></strong>
                    </span>
                    
                    <a href="../cerrar_sesion.php" class="bg-gradient-to-r from-blue-400 to-cyan-500 hover:scale-105 transform px-4 py-2 rounded-xl text-white shadow-lg transition duration-300 ease-in-out">
                        Cerrar Sesión
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </header>

 
</body>
</html>