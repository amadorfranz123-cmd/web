<?php
session_start();
require_once '../config/conexion_bd.php';
require_once '../includes/autenticacion.php';
redirectIfNotLoggedIn();

if ($_SESSION['rol'] !== 'medico') {
    header('Location: ../index.php');
    exit;
}

$acciones = [
    // [Título, Enlace, Color, Emoji (UTF-8)]
    ['Ver Citas', 'ver_citas.php', 'bg-gradient-to-r from-blue-300 to-sky-300', '📅'], 
  
];
?>
<?php require_once '../includes/cabecera.php'; ?>

<div class="min-h-screen bg-indigo-900 py-8">
    <div class="max-w-7xl mx-auto px-4">
        
       <div class="bg-white rounded-xl shadow-lg p-6 mb-8 text-gray-900 flex flex-col md:flex-row items-center border border-gray-200">
    
    <div class="mb-6 md:mb-0 md:mr-8">
        <img src="https://z-cdn-media.chatglm.cn/files/d311805a-20de-4b21-97bc-eef0e1278945_pasted_image_1761817530851.png?auth_key=1793353562-534c6060a09c436baba70ad8217fdc55-0-46b4f1ff8ad7af1467cd12b7fa63a5aa" 
            alt="Doctor" 
            class="w-32 h-32 md:w-40 md:h-40 rounded-full border-4 border-white shadow-lg object-cover">
    </div>
    
    <div class="text-center md:text-left">
        <h2 class="text-3xl font-bold mb-2 text-blue-800">Panel del Médico</h2> 
        <p class="text-gray-700">Hola, <strong class="text-blue-900"><?php echo htmlspecialchars($_SESSION['nombre']); ?></strong></p>
    </div>
</div>
        <div class="bg-blue-100/90 backdrop-blur-sm p-6 rounded-xl shadow-lg mb-8 text-gray-900 border border-blue-200">
            <p class="text-base leading-relaxed flex items-start">
                <span class="text-2xl text-blue-600 mr-4 mt-0.5">💡</span>
                <span>
                    <strong>Recomendación para el día:</strong> Prioriza la revisión de las **Citas** pendientes y el acceso a los **Historiales Médicos** antes de realizar un nuevo **Diagnóstico**. La gestión eficiente de tu tiempo es clave para la calidad de la atención.
                </span>
            </p>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php foreach ($acciones as $a): ?>
            <a href="<?php echo htmlspecialchars($a[1]); ?>" class="group">
                <div class="bg-white rounded-xl shadow-md overflow-hidden transform transition-all duration-300 hover:scale-105 hover:shadow-xl border border-gray-100">
                    
                    <div class="<?php echo htmlspecialchars($a[2]); ?> p-6 text-center">
                        <span class="text-4xl block mb-3 text-gray-800"><?php echo htmlspecialchars($a[3]); ?></span> 
                        <h3 class="font-bold text-lg text-gray-900"><?php echo htmlspecialchars($a[0]); ?></h3>
                    </div>
                    
                    <div class="p-4 text-center bg-gray-50">
                        <span class="text-gray-500 text-sm group-hover:text-blue-600 transition-colors">Acceder →</span> 
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/pie_pagina.php'; ?>