<?php
session_start();
require_once './includes/autenticacion.php';

// Bloque de redirección original (sin modificar)
if (isUserLoggedIn() && !isset($_GET['force'])) {
    $rol = $_SESSION['rol'];
    $redirect = match ($rol) {
        'admin' => './administrador/panel.php',
        'medico' => './medico/panel.php',
        'enfermera' => './enfermera/panel.php',
        'paciente' => './paciente/panel.php',
        default => './index.php'
    };
    header("Location: $redirect");
    exit;
}
?>
<?php require_once './includes/cabecera.php'; ?>

<div class="min-h-screen bg-gradient-to-br from-indigo-50 to-cyan-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        
        <div class="bg-white py-12 px-8 shadow-2xl rounded-xl sm:px-10 border-t-8 border-teal-500 transform transition duration-500 hover:shadow-cyan-400/50">
            <div class="text-center">
                <span class="text-6xl mb-4 block text-teal-600" role="img" aria-label="Cruz médica">➕</span>
                
                <h2 class="text-4xl font-extrabold text-gray-900 mb-4 tracking-tight">
                    Bienvenido al Portal de Salud De la comunidad de Quichina 
                </h2>
                <p class="mt-2 text-md text-gray-500 font-medium">
                    Salud es vida.
                </p>
            </div>
            
            <div class="mt-10 space-y-5">
                
                <a href="./iniciar_sesion.php" 
                   class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-lg text-xl font-bold text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-4 focus:ring-offset-2 focus:ring-indigo-500 transition duration-300 ease-in-out transform hover:scale-[1.02]">
                    Iniciar Sesión
                </a>

                <a href="./registrarse.php" 
                   class="w-full flex justify-center py-3 px-4 border-2 border-teal-500 rounded-lg shadow-sm text-xl font-bold text-teal-600 bg-white hover:bg-teal-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 transition duration-300 ease-in-out">
                    Crear una Cuenta
                </a>
            </div>
            
        </div>
    </div>
</div>

<?php require_once './includes/pie_pagina.php'; ?>