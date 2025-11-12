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
 $cita = null;

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id_cita = (int)$_GET['id'];
    try {
        $stmt = $db_connection->prepare("
            SELECT c.*, p.nombres AS paciente, m.nombres AS medico 
            FROM cita c 
            JOIN paciente pa ON c.id_paciente = pa.id_persona
            JOIN persona p ON pa.id_persona = p.id_persona
            JOIN personal_de_salud ps ON c.id_doctor = ps.id_medico
            JOIN persona m ON ps.id_medico = m.id_persona
            WHERE c.id_cita = ?
        ");
        $stmt->execute([$id_cita]);
        $cita = $stmt->fetch();
    } catch (PDOException $e) {
        $mensaje = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                        <strong class="font-bold">Error!</strong>
                        <span class="block sm:inline">' . htmlspecialchars($e->getMessage()) . '</span>
                        <span class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.style.display=\'none\'">
                            <svg class="fill-current h-6 w-6 text-red-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg>
                        </span>
                    </div>';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $cita) {
    $nueva_fecha = $_POST['fecha_hora'];
    try {
        $stmt = $db_connection->prepare("UPDATE cita SET fecha_hora_cita = ? WHERE id_cita = ?");
        $stmt->execute([$nueva_fecha, $id_cita]);
        $mensaje = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                        <strong class="font-bold">¡Éxito!</strong>
                        <span class="block sm:inline">Cita reprogramada con éxito.</span>
                        <span class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.style.display=\'none\'">
                            <svg class="fill-current h-6 w-6 text-green-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg>
                        </span>
                    </div>';
        // Recargar datos
        $stmt = $db_connection->prepare("SELECT fecha_hora_cita FROM cita WHERE id_cita = ?");
        $stmt->execute([$id_cita]);
        $cita['fecha_hora_cita'] = $stmt->fetchColumn();
    } catch (PDOException $e) {
        $mensaje = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                        <strong class="font-bold">Error!</strong>
                        <span class="block sm:inline">' . htmlspecialchars($e->getMessage()) . '</span>
                        <span class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.style.display=\'none\'">
                            <svg class="fill-current h-6 w-6 text-red-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg>
                        </span>
                    </div>';
    }
}
?>
<?php require_once '../includes/cabecera.php'; ?>

<div class="min-h-screen bg-gradient-to-br from-teal-50 to-cyan-50 py-8">
    <div class="max-w-4xl mx-auto px-4">
        <!-- Encabezado mejorado -->
        <div class="bg-gradient-to-r from-teal-600 to-cyan-500 rounded-xl shadow-lg p-6 mb-8 text-white">
            <div class="flex flex-col md:flex-row items-center justify-between">
                <div class="flex items-center">
                    <img src="https://z-cdn-media.chatglm.cn/files/87de6f90-efa6-4b27-8619-f2c5166fafa2_pasted_image_1761820097060.png?auth_key=1793354103-ec253e8b519f4245967b77afc3c2e616-0-fb19dde91b64e2a4550142cc74bfa3f5" 
                         alt="Enfermera" 
                         class="w-16 h-16 rounded-full border-3 border-white shadow-lg mr-4">
                    <div>
                        <h2 class="text-3xl font-bold mb-1">Reprogramar Cita</h2>
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

        <?php if ($cita): ?>
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="p-6">
                <div class="mb-8 p-4 bg-teal-50 rounded-lg border border-teal-100">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-teal-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        Información de la Cita
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-white p-3 rounded-lg shadow-sm">
                            <p class="text-sm text-gray-500">Paciente</p>
                            <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($cita['paciente']); ?></p>
                        </div>
                        <div class="bg-white p-3 rounded-lg shadow-sm">
                            <p class="text-sm text-gray-500">Médico</p>
                            <p class="font-semibold text-gray-800">Dr. <?php echo htmlspecialchars($cita['medico']); ?></p>
                        </div>
                        <div class="bg-white p-3 rounded-lg shadow-sm">
                            <p class="text-sm text-gray-500">Fecha Actual</p>
                            <p class="font-semibold text-gray-800"><?php echo date('d/m/Y H:i', strtotime($cita['fecha_hora_cita'])); ?></p>
                        </div>
                    </div>
                </div>

                <form method="POST" class="space-y-6">
                    <div>
                        <label for="fecha_hora" class="block text-sm font-medium text-gray-700 mb-2 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Nueva Fecha y Hora
                        </label>
                        <input type="datetime-local" 
                               id="fecha_hora" 
                               name="fecha_hora" 
                               required 
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 bg-white text-gray-800 shadow-sm"
                               min="<?php echo date('Y-m-d\TH:i'); ?>"
                               value="<?php echo date('Y-m-d\TH:i', strtotime($cita['fecha_hora_cita'])); ?>">
                        <p class="mt-1 text-xs text-gray-500">Seleccione una fecha y hora futuras</p>
                    </div>
                    
                    <div class="pt-4">
                        <button type="submit" class="w-full bg-gradient-to-r from-teal-500 to-cyan-400 hover:from-teal-600 hover:to-cyan-500 text-white font-medium py-3 px-4 rounded-lg shadow-md transition transform hover:scale-[1.02] flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Reprogramar Cita
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <?php else: ?>
        <div class="bg-white rounded-xl shadow-lg p-12 text-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-1">No se ha seleccionado ninguna cita</h3>
            <p class="text-gray-500 mb-4">Selecciona una cita desde <a href="ver_citas.php" class="text-teal-600 hover:underline font-medium">Ver Citas</a>.</p>
            <a href="ver_citas.php" class="inline-flex items-center bg-teal-600 hover:bg-teal-700 text-white px-4 py-2 rounded-lg transition">
                Ver Citas
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/pie_pagina.php'; ?>>