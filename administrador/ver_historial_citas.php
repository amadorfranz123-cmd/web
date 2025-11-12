<?php
session_start();
require_once '../config/conexion_bd.php';
require_once '../includes/autenticacion.php';
redirectIfNotLoggedIn();

if ($_SESSION['rol'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

try {
    $stmt = $db_connection->prepare("
        SELECT c.*, p.nombres AS paciente, p.apellidos AS paciente_apellidos, 
               m.nombres AS medico, m.apellidos AS medico_apellidos, 
               a.diagnostico, a.recetas, a.fecha_atencion
        FROM cita c 
        JOIN paciente pa ON c.id_paciente = pa.id_persona 
        JOIN persona p ON pa.id_persona = p.id_persona 
        JOIN personal_de_salud ps ON c.id_doctor = ps.id_medico 
        JOIN persona m ON ps.id_medico = m.id_persona 
        LEFT JOIN atencion a ON c.id_atencion = a.id_atencion 
        WHERE c.estado = 'atendida'
        ORDER BY c.fecha_hora_cita DESC
    ");
    $stmt->execute();
    $citas = $stmt->fetchAll();
} catch (PDOException $e) {
    die("<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative' role='alert'>
             <strong class='font-bold'>Error!</strong>
             <span class='block sm-inline'>Error al cargar el historial: " . htmlspecialchars($e->getMessage()) . "</span>
         </div>");
}
?>
<?php require_once '../includes/cabecera.php'; ?>

<div class="min-h-screen bg-gradient-to-br from-blue-50 to-cyan-50 py-8">
    <div class="max-w-7xl mx-auto px-4">
        <div class="bg-gradient-to-r from-blue-100 to-cyan-200 rounded-xl shadow-lg p-6 mb-8 text-gray-800">
            <div class="flex flex-col md:flex-row items-center justify-between">
                <div class="flex items-center">
                    <img src="https://z-cdn-media.chatglm.cn/files/d311805a-20de-4b21-97bc-eef0e1278945_pasted_image_1761817530851.png?auth_key=1793354103-ec253e8b519f4245967b77afc3c2e616-0-fb19dde91b64e2a4550142cc74bfa3f5" 
                             alt="Administrador" 
                             class="w-20 h-20 rounded-full border-3 border-white shadow-lg mr-4 border-gray-100">
                    <div>
                        <h2 class="text-3xl font-bold mb-1">Historial de Citas</h2>
                        <p class="text-gray-600">Hola, <strong><?php echo htmlspecialchars($_SESSION['nombre']); ?></strong></p>
                        <p class="text-gray-600 text-sm mt-1"><?php echo date('l, j F Y'); ?></p>
                    </div>
                </div>
                <a href="panel.php" class="mt-4 md:mt-0 bg-white bg-opacity-70 hover:bg-opacity-90 px-4 py-2 rounded-lg text-sm transition flex items-center shadow text-gray-800 border border-gray-200">
                    <i class="fas fa-arrow-left mr-2"></i> Volver
                </a>
            </div>
        </div>

        <div class="mb-10 flex flex-col md:flex-row justify-between items-start md:items-center">
            <div>
                <h2 class="text-3xl font-bold text-cyan-800 mb-2">Historial de Citas</h2>
                <p class="text-gray-600">Total: <strong><?php echo count($citas); ?></strong> atenciones registradas</p>
            </div>
        </div>

        <?php if (empty($citas)): ?>
            <div class="bg-white rounded-xl shadow-lg p-12 text-center border border-gray-100">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-1">No hay citas atendidas</h3>
                <p class="text-gray-500">Actualmente no hay citas atendidas en el sistema.</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <?php foreach ($citas as $c): ?>
                <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition-shadow border border-cyan-50">
                    <div class="p-6">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800"><?php echo htmlspecialchars($c['paciente'] . ' ' . $c['paciente_apellidos']); ?></h3>
                                <p class="text-sm text-gray-600">Dr. <?php echo htmlspecialchars($c['medico'] . ' ' . $c['medico_apellidos']); ?></p>
                            </div>
                            <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded">Atendida</span>
                        </div>
                        
                        <div class="mb-4">
                            <div class="flex items-center text-gray-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-cyan-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span><?php echo date('d/m/Y H:i', strtotime($c['fecha_hora_cita'])); ?></span>
                            </div>
                            <?php if ($c['fecha_atencion']): ?>
                            <div class="flex items-center text-gray-600 mt-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-cyan-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Atendida: <?php echo date('d/m/Y H:i', strtotime($c['fecha_atencion'])); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($c['diagnostico']): ?>
                        <div class="mb-4 border-t border-gray-100 pt-4">
                            <h4 class="text-sm font-semibold text-cyan-700 mb-1 flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 text-cyan-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Diagnóstico
                            </h4>
                            <p class="text-gray-700 bg-gray-50 p-3 rounded-lg border border-gray-100"><?php echo nl2br(htmlspecialchars($c['diagnostico'])); ?></p>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($c['recetas']): ?>
                        <div class="border-t border-gray-100 pt-4">
                            <h4 class="text-sm font-semibold text-cyan-700 mb-1 flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 text-cyan-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                                </svg>
                                Receta
                            </h4>
                            <p class="text-gray-700 bg-gray-50 p-3 rounded-lg border border-gray-100"><?php echo nl2br(htmlspecialchars($c['recetas'])); ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/pie_pagina.php'; ?>