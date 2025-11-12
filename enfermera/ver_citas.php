<?php
session_start();
require_once '../config/conexion_bd.php';
require_once '../includes/autenticacion.php';
redirectIfNotLoggedIn();

if ($_SESSION['rol'] !== 'enfermera') {
    header('Location: ../index.php');
    exit;
}

try {
    $stmt = $db_connection->prepare("
        SELECT c.id_cita, p.nombres AS paciente, m.nombres AS medico, c.fecha_hora_cita, c.estado 
        FROM cita c 
        JOIN paciente pa ON c.id_paciente = pa.id_persona
        JOIN persona p ON pa.id_persona = p.id_persona
        JOIN personal_de_salud ps ON c.id_doctor = ps.id_medico
        JOIN persona m ON ps.id_medico = m.id_persona
        ORDER BY c.fecha_hora_cita DESC
    ");
    $stmt->execute();
    $citas = $stmt->fetchAll();
} catch (PDOException $e) {
    die("<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative' role='alert'>
            <strong class='font-bold'>Error!</strong>
            <span class='block sm:inline'>" . htmlspecialchars($e->getMessage()) . "</span>
          </div>");
}
?>
<?php require_once '../includes/cabecera.php'; ?>

<div class="min-h-screen bg-gradient-to-br from-teal-50 to-cyan-50 py-8">
    <div class="max-w-7xl mx-auto px-4">
        <!-- Encabezado mejorado -->
        <div class="bg-gradient-to-r from-teal-600 to-cyan-500 rounded-xl shadow-lg p-6 mb-8 text-white">
            <div class="flex flex-col md:flex-row items-center">
                <img src="https://z-cdn-media.chatglm.cn/files/87de6f90-efa6-4b27-8619-f2c5166fafa2_pasted_image_1761820097060.png?auth_key=1793354103-ec253e8b519f4245967b77afc3c2e616-0-fb19dde91b64e2a4550142cc74bfa3f5" 
                     alt="Enfermera" 
                     class="w-20 h-20 rounded-full border-3 border-white shadow-lg mb-4 md:mb-0 md:mr-6">
                <div class="text-center md:text-left">
                    <h2 class="text-3xl font-bold mb-1">Citas Programadas</h2>
                    <p class="text-teal-100">Hola, <strong><?php echo htmlspecialchars($_SESSION['nombre']); ?></strong></p>
                    <p class="text-teal-100 text-sm mt-1"><?php echo date('l, j F Y'); ?></p>
                </div>
            </div>
        </div>

        <!-- Contenido principal -->
        <?php if ($citas): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($citas as $c): ?>
            <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                <div class="p-6">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800"><?php echo htmlspecialchars($c['paciente']); ?></h3>
                            <p class="text-sm text-gray-600">Dr. <?php echo htmlspecialchars($c['medico']); ?></p>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-medium
                            <?php echo $c['estado'] === 'atendida' ? 'bg-green-100 text-green-800' : 
                                   ($c['estado'] === 'cancelada' ? 'bg-red-100 text-red-800' : 'bg-amber-100 text-amber-800'); ?>">
                            <?php echo ucfirst($c['estado']); ?>
                        </span>
                    </div>
                    
                    <div class="mb-4">
                        <div class="flex items-center text-gray-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span><?php echo date('d/m/Y H:i', strtotime($c['fecha_hora_cita'])); ?></span>
                        </div>
                    </div>
                    
                    <?php if ($c['estado'] === 'pendiente'): ?>
                    <div class="flex space-x-3">
                        <a href="reprogramar_cita.php?id=<?php echo $c['id_cita']; ?>" 
                           class="flex-1 bg-blue-500 hover:bg-blue-600 text-white text-center py-2 px-3 rounded-lg text-sm font-medium transition transform hover:scale-105">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Reprogramar
                        </a>
                        <a href="cancelar_cita.php?id=<?php echo $c['id_cita']; ?>" 
                           class="flex-1 bg-red-100 hover:bg-red-200 text-red-800 text-center py-2 px-3 rounded-lg text-sm font-medium transition"
                           onclick="return confirm('¿Cancelar esta cita?');">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Cancelar
                        </a>
                    </div>
                    <?php else: ?>
                    <div class="text-center text-sm text-gray-500 py-2">
                        <?php echo $c['estado'] === 'atendida' ? 'Cita atendida' : 'Cita cancelada'; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="bg-white rounded-xl shadow-lg p-12 text-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-1">No hay citas programadas</h3>
            <p class="text-gray-500">Actualmente no hay citas en el sistema.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/pie_pagina.php'; ?>