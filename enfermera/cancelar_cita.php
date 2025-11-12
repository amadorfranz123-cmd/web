<?php
session_start();
require_once '../config/conexion_bd.php';
require_once '../includes/autenticacion.php';
redirectIfNotLoggedIn();

if ($_SESSION['rol'] !== 'enfermera') {
    header('Location: ../index.php');
    exit;
}

 $mensaje = '';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id_cita = (int)$_GET['id'];
    try {
        $stmt = $db_connection->prepare("UPDATE cita SET estado = 'cancelada' WHERE id_cita = ? AND estado = 'pendiente'");
        $stmt->execute([$id_cita]);
        $affected = $stmt->rowCount();
        $mensaje = $affected > 0 
            ? '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <strong class="font-bold">¡Éxito!</strong>
                    <span class="block sm:inline">Cita cancelada con éxito.</span>
                    <span class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.style.display=\'none\'">
                        <svg class="fill-current h-6 w-6 text-green-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg>
                    </span>
                </div>'
            : '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <strong class="font-bold">Error!</strong>
                    <span class="block sm:inline">No se pudo cancelar: la cita ya está cancelada o no existe.</span>
                    <span class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.style.display=\'none\'">
                        <svg class="fill-current h-6 w-6 text-red-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg>
                    </span>
                </div>';
    } catch (PDOException $e) {
        $mensaje = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <strong class="font-bold">Error!</strong>
                    <span class="block sm:inline">' . htmlspecialchars($e->getMessage()) . '</span>
                    <span class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.style.display=\'none\'">
                        <svg class="fill-current h-6 w-6 text-red-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg>
                    </span>
                </div>';
    }
}

try {
    $stmt = $db_connection->prepare("
        SELECT c.id_cita, p.nombres AS paciente, m.nombres AS medico, c.fecha_hora_cita 
        FROM cita c 
        JOIN paciente pa ON c.id_paciente = pa.id_persona
        JOIN persona p ON pa.id_persona = p.id_persona
        JOIN personal_de_salud ps ON c.id_doctor = ps.id_medico
        JOIN persona m ON ps.id_medico = m.id_persona
        WHERE c.estado = 'pendiente'
        ORDER BY c.fecha_hora_cita ASC
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
    <div class="max-w-6xl mx-auto px-4">
        <!-- Encabezado mejorado -->
        <div class="bg-gradient-to-r from-teal-600 to-cyan-500 rounded-xl shadow-lg p-6 mb-8 text-white">
            <div class="flex flex-col md:flex-row items-center justify-between">
                <div class="flex items-center">
                    <img src="https://z-cdn-media.chatglm.cn/files/87de6f90-efa6-4b27-8619-f2c5166fafa2_pasted_image_1761820097060.png?auth_key=1793354103-ec253e8b519f4245967b77afc3c2e616-0-fb19dde91b64e2a4550142cc74bfa3f5" 
                         alt="Enfermera" 
                         class="w-16 h-16 rounded-full border-3 border-white shadow-lg mr-4">
                    <div>
                        <h2 class="text-3xl font-bold mb-1">Cancelar Cita</h2>
                        <p class="text-teal-100">Hola, <strong><?php echo htmlspecialchars($_SESSION['nombre']); ?></strong></p>
                    </div>
                </div>
                <a href="ver_citas.php" class="mt-4 md:mt-0 bg-white bg-opacity-20 hover:bg-opacity-30 px-4 py-2 rounded-lg text-sm transition flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Volver a Citas
                </a>
            </div>
        </div>

        <?php echo $mensaje; ?>

        <!-- Contenido principal -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="p-6">
                <?php if (empty($citas)): ?>
                    <div class="text-center py-12">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 mb-1">No hay citas pendientes</h3>
                        <p class="text-gray-500">Actualmente no hay citas pendientes para cancelar.</p>
                    </div>
                <?php else: ?>
                    <div class="mb-6">
                        <h3 class="text-xl font-semibold text-gray-800 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-teal-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Citas Pendientes
                        </h3>
                        <p class="text-sm text-gray-500 mt-1">Selecciona una cita para cancelarla</p>
                    </div>

                    <div class="space-y-4">
                        <?php foreach ($citas as $c): ?>
                        <div class="border border-gray-200 rounded-lg p-5 hover:shadow-md transition-shadow">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="font-semibold text-gray-800 text-lg"><?php echo htmlspecialchars($c['paciente']); ?></p>
                                    <p class="text-sm text-gray-600">Dr. <?php echo htmlspecialchars($c['medico']); ?></p>
                                    <p class="text-sm text-gray-500 mt-1"><?php echo date('d/m/Y H:i', strtotime($c['fecha_hora_cita'])); ?></p>
                                </div>
                                <a href="?id=<?php echo $c['id_cita']; ?>" 
                                   class="bg-red-100 hover:bg-red-200 text-red-700 font-medium py-2 px-4 rounded-lg transition flex items-center"
                                   onclick="return confirm('¿Estás seguro de que deseas cancelar esta cita?');">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    Cancelar
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/pie_pagina.php'; ?>