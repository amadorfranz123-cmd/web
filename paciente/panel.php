<?php
session_start();
require_once '../config/conexion_bd.php';
require_once '../includes/autenticacion.php';
// Redirige si el usuario no está logueado
redirectIfNotLoggedIn();

// Verifica el rol del usuario (guardia de rol)
if ($_SESSION['rol'] !== 'paciente') { 
    header('Location: ../index.php'); 
    exit; 
}

// Definir las acciones del paciente con colores claros
$acciones = [
    // [Título, Enlace, Icono, Descripción, Color_Claro, Color_Acento]
    [
        'Solicitar Cita', 
        'solicitar_cita.php', 
        '📅', 
        'Agenda tu próxima consulta médica fácilmente.', 
        'bg-blue-50', 
        'text-blue-700'
    ],
    // Puedes añadir más acciones aquí si es necesario
];
?>

<?php require_once '../includes/cabecera.php'; ?>

<body class="min-h-screen bg-gradient-to-br from-gray-50 via-blue-50 to-indigo-100 flex items-center justify-center font-sans text-gray-900">

    <div class="max-w-4xl w-full bg-white/90 backdrop-blur-md border border-gray-200 shadow-2xl rounded-3xl p-10 text-center">

        <div class="mb-10">
            <div class="flex justify-center mb-4">
                <div class="bg-gradient-to-r from-blue-400 to-indigo-500 p-5 rounded-full shadow-lg">
                    <span class="text-white text-4xl">🧑‍⚕️</span>
                </div>
            </div>
            <h2 class="text-4xl font-extrabold text-blue-800 mb-2">Panel del Paciente</h2>
            <p class="text-lg text-gray-700">
                Bienvenido, <strong class="text-blue-900"><?php echo htmlspecialchars($_SESSION['nombre']); ?></strong>
            </p>
        </div>

        <div class="bg-blue-50 border-l-4 border-blue-400 p-5 rounded-lg shadow-sm mb-10">
            <p class="text-gray-700 text-base leading-relaxed">
                🌟 <strong>Recomendación:</strong> Mantén tu información médica actualizada y agenda tus citas regularmente.  
                Recuerda que la prevención y los controles médicos son claves para tu bienestar.  
                Si tienes alguna molestia o síntoma, solicita tu cita médica a tiempo.
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 justify-items-center">

            <?php foreach ($acciones as $a): ?>
            <a href="<?php echo htmlspecialchars($a[1]); ?>"
                class="bg-gradient-to-br from-white to-gray-50 border border-gray-200 rounded-2xl p-6 w-56 shadow-md hover:shadow-xl hover:scale-105 transform transition-all duration-300">
                
                <div class="bg-blue-100 text-blue-600 w-16 h-16 rounded-full flex items-center justify-center text-4xl mx-auto mb-4 shadow-inner">
                    <?php echo htmlspecialchars($a[2]); ?>
                </div>
                
                <h3 class="text-lg font-bold text-blue-800"><?php echo htmlspecialchars($a[0]); ?></h3>
                <p class="text-gray-600 text-sm mt-2"><?php echo htmlspecialchars($a[3]); ?></p>
            </a>
            <?php endforeach; ?>

        </div>

        <div class="mt-10">
            <p class="text-sm italic text-gray-600">
                💖 “Tu salud es tu mayor tesoro, cuídala cada día.”
            </p>
        </div>

    </div>

</body>

<?php require_once '../includes/pie_pagina.php'; ?>
