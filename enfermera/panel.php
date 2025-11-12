<?php
// ===============================================
// 1. INICIALIZACIÓN Y LÓGICA DE ACCESO
// ===============================================
session_start();
require_once '../config/conexion_bd.php';
require_once '../includes/autenticacion.php';

// Redirige si el usuario no está logueado
redirectIfNotLoggedIn();

// Verifica el rol del usuario (guardia de rol)
if ($_SESSION['rol'] !== 'enfermera') {
    http_response_code(403);
    header('Location: ../index.php');
    exit;
}

// Corregir el array de acciones con tildes y emojis correctos
$acciones = [
    // [Título, Enlace, Color, Emoji (UTF-8)]
    ['Generar Historial Médico', 'generar_historial_medico.php', 'bg-gradient-to-r from-violet-600 to-fuchsia-500', '📝'],
    ['Ver Citas', 'ver_citas.php', 'bg-gradient-to-r from-pink-500 to-rose-400', '📅'],
    ['Registrar Nuevo Paciente', 'registrar_paciente.ph.php', 'bg-gradient-to-r from-purple-500 to-indigo-500', '➕'],
];

?>
<?php require_once '../includes/cabecera.php'; ?>

<div class="min-h-screen bg-gradient-to-br from-pink-50 via-purple-50 to-indigo-100 py-8">
    <div class="max-w-7xl mx-auto px-4">
        
   <div class="bg-gradient-to-r from-blue-700 to-cyan-500 rounded-2xl shadow-xl p-8 mb-10 text-white">
    <div class="flex flex-col md:flex-row items-center">
        <img src="https://z-cdn-media.chatglm.cn/files/87de6f90-efa6-4b27-8619-f2c5166fafa2_pasted_image_1761820097060.png?auth_key=1793354103-ec253e8b519f4245967b77afc3c2e616-0-fb19dde91b64e2a4550142cc74bfa3f5" 
            alt="Enfermera" 
            class="w-24 h-24 rounded-full border-4 border-white shadow-lg mb-6 md:mb-0 md:mr-8">
        <div class="text-center md:text-left">
            <h1 class="text-4xl font-bold mb-2">Panel de Enfermería</h1>
            <p class="text-blue-200 text-sm mt-1"><?php echo date('l, j F Y'); ?></p>
        </div>
    </div>
</div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($acciones as $a): ?>
            <a href="<?php echo htmlspecialchars($a[1]); ?>" class="group">
                <div class="bg-white rounded-xl shadow-lg overflow-hidden transform transition-all duration-300 hover:scale-105 hover:shadow-xl border border-gray-100">
                    
                    <div class="<?php echo htmlspecialchars($a[2]); ?> p-6 text-center">
                        <span class="text-4xl block mb-3 text-white"><?php echo htmlspecialchars($a[3]); ?></span>
                        
                        <h3 class="font-bold text-lg text-gray-900"><?php echo htmlspecialchars($a[0]); ?></h3>
                        
                    </div>
                    
                    <div class="p-4 text-center bg-gray-50">
                        <span class="text-gray-500 text-sm group-hover:text-fuchsia-600 transition-colors">Acceder →</span>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/pie_pagina.php'; ?>