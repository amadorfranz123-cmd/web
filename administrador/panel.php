<?php
session_start();
require_once '../config/conexion_bd.php';
require_once '../includes/autenticacion.php';
redirectIfNotLoggedIn();

if ($_SESSION['rol'] !== 'admin') { 
    header('Location: ../index.php'); 
    exit; 
}
?>
<?php require_once '../includes/cabecera.php'; ?>

<body class="bg-gradient-to-br from-blue-50 via-green-50 to-teal-50 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        
        <div class="bg-gradient-to-r from-green-500 to-teal-600 rounded-3xl shadow-xl p-8 mb-10 text-white relative overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-white bg-opacity-10 rounded-full -mr-16 -mt-16"></div>
            <div class="absolute bottom-0 left-0 w-40 h-40 bg-white bg-opacity-10 rounded-full -ml-20 -mb-20"></div>
            
            <div class="relative z-10">
                <h1 class="text-3xl md:text-4xl font-bold mb-3">Bienvenido al Panel de Administración</h1>
                <p class="text-lg md:text-xl opacity-90">Administrador: <strong><?php echo htmlspecialchars($_SESSION['nombre']); ?></strong></p>
                <p class="text-sm md:text-base opacity-75 mt-2"><?php echo date('l, j F Y'); ?></p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-10">
            
            <a href="gestionar_citas.php" class="group">
                <div class="bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 border-t-4 border-blue-500 overflow-hidden">
                    <div class="p-6">
                        <div class="flex items-center mb-4">
                            <div class="bg-blue-100 p-3 rounded-lg mr-4">
                                <span class="text-2xl">🗓️</span>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800 group-hover:text-blue-600 transition-colors">Gestionar Citas</h3>
                                <p class="text-sm text-gray-500">Organiza y administra las citas médicas</p>
                            </div>
                        </div>
                        <div class="flex justify-between items-center mt-4">
                            <span class="text-xs text-gray-400">Ir a gestión</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </div>
                </div>
            </a>

            <a href="ver_historial_citas.php" class="group">
                <div class="bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 border-t-4 border-green-500 overflow-hidden">
                    <div class="p-6">
                        <div class="flex items-center mb-4">
                            <div class="bg-green-100 p-3 rounded-lg mr-4">
                                <span class="text-2xl">📘</span>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800 group-hover:text-green-600 transition-colors">Historial de Citas</h3>
                                <p class="text-sm text-gray-500">Consulta el registro completo de citas</p>
                            </div>
                        </div>
                        <div class="flex justify-between items-center mt-4">
                            <span class="text-xs text-gray-400">Ver historial</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </div>
                </div>
            </a>

            <a href="reportes.php" class="group">
                <div class="bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 border-t-4 border-red-500 overflow-hidden">
                    <div class="p-6">
                        <div class="flex items-center mb-4">
                            <div class="bg-red-100 p-3 rounded-lg mr-4">
                                <span class="text-2xl">📈</span>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800 group-hover:text-red-600 transition-colors">Generar Reportes</h3>
                                <p class="text-sm text-gray-500">Accede a análisis de datos y estadísticas</p>
                            </div>
                        </div>
                        <div class="flex justify-between items-center mt-4">
                            <span class="text-xs text-gray-400">Ver datos</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-500 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </div>
                </div>
            </a>
				
        </div>

        <div class="bg-white rounded-2xl shadow-md p-6 text-center">
            <p class="text-gray-600 italic text-lg">💖 "La salud no es todo, pero sin ella, todo lo demás es nada."</p>
        </div>
    </div>

    <?php require_once '../includes/pie_pagina.php'; ?>
</body>