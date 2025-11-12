<?php
session_start();
require_once '../config/conexion_bd.php';
require_once '../includes/autenticacion.php';
redirectIfNotLoggedIn();

// 1. Verificación de Rol
if ($_SESSION['rol'] !== 'medico') {
    header('Location: ../index.php');
    exit;
}

if (!isset($_SESSION['id_persona'])) {
    die("<div class='alert-danger'>Error de sesión. <a href='../cerrar_sesion.php'>Volver a iniciar</a></div>");
}
// Obtener ID del médico desde la sesión
$id_medico = $_SESSION['id_persona'];

// 2. Cargar lista de pacientes atendidos por este médico
try {
    $pacientes_stmt = $db_connection->prepare("
        SELECT DISTINCT p.id_persona, p.nombres, p.apellidos 
        FROM cita c 
        JOIN paciente pa ON c.id_paciente = pa.id_persona
        JOIN persona p ON pa.id_persona = p.id_persona
        WHERE c.id_doctor = ? AND c.estado = 'atendida'
        ORDER BY p.nombres
    ");
    $pacientes_stmt->execute([$id_medico]);
    $pacientes = $pacientes_stmt->fetchAll();
} catch (PDOException $e) {
    // Manejo de error de base de datos
    $pacientes = [];
    $mensaje = "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative' role='alert'>
                    <strong class='font-bold'>Error!</strong>
                    <span class='block sm-inline'>Error al cargar pacientes: " . htmlspecialchars($e->getMessage()) . "</span>
                </div>";
}


$historial = [];
$id_paciente_seleccionado = null;

// 3. Cargar historial del paciente seleccionado
if (isset($_GET['id_paciente']) && is_numeric($_GET['id_paciente'])) {
    $id_paciente_seleccionado = (int)$_GET['id_paciente'];
    try {
        $stmt = $db_connection->prepare("
            SELECT c.fecha_hora_cita, a.diagnostico, a.recetas 
            FROM cita c 
            LEFT JOIN atencion a ON c.id_atencion = a.id_atencion 
            WHERE c.id_paciente = ? AND c.id_doctor = ? AND c.estado = 'atendida'
            ORDER BY c.fecha_hora_cita DESC
        ");
        $stmt->execute([$id_paciente_seleccionado, $id_medico]);
        $historial = $stmt->fetchAll();
    } catch (PDOException $e) {
        $mensaje = "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative' role='alert'>
                    <strong class='font-bold'>Error!</strong>
                    <span class='block sm-inline'>Error al cargar el historial: " . htmlspecialchars($e->getMessage()) . "</span>
                </div>";
    }
}
?>
<?php require_once '../includes/cabecera.php'; ?>
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-cyan-50 py-8">
    <div class="max-w-6xl mx-auto px-4">
        
        <div class="bg-gradient-to-r from-blue-100 to-cyan-200 rounded-xl shadow-lg p-6 mb-8 text-gray-800 flex items-center">
            <img src="https://z-cdn-media.chatglm.cn/files/d311805a-20de-4b21-97bc-eef0e1278945_pasted_image_1761817530851.png?auth_key=1793353918-0a8e7b1a5d4149f6b6b2b9f06598bb2e-0-9848d7140f17061551ae7bb41b1798ff" 
                alt="Doctor" 
                class="w-16 h-16 rounded-full border-3 border-white shadow-lg mr-6 border-gray-100">
            <div>
                <h2 class="text-3xl font-bold mb-1 text-cyan-800">Expedientes Médicos</h2>
                <p class="text-gray-600">Hola, Dr. <strong><?php echo htmlspecialchars($_SESSION['nombre']); ?></strong></p>
                <a href="panel.php" class="mt-1 inline-block bg-white bg-opacity-70 hover:bg-opacity-90 px-3 py-1 rounded text-sm transition shadow text-gray-800 border border-gray-200">Volver al panel</a>
            </div>
        </div>

        <?php if (isset($mensaje)) echo $mensaje; // Mostrar mensaje de error si existe ?>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            
            <div class="lg:col-span-1 bg-white rounded-xl shadow-md p-6 border border-gray-100 h-fit">
                <h3 class="font-semibold text-lg text-gray-800 mb-4 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-cyan-600" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                    </svg>
                    Seleccionar Paciente
                </h3>
                <form method="GET" class="space-y-3">
                    <select name="id_paciente" required onchange="this.form.submit()" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-transparent">
                        <option value="">-- Seleccionar --</option>
                        <?php foreach ($pacientes as $p): ?>
                            <option value="<?php echo $p['id_persona']; ?>" <?php echo (isset($id_paciente_seleccionado) && $id_paciente_seleccionado == $p['id_persona']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($p['nombres'] . ' ' . $p['apellidos']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>

            <div class="lg:col-span-3">
                <?php if ($id_paciente_seleccionado && empty($historial)): ?>
                    <div class="bg-white rounded-xl shadow-md p-12 text-center border border-gray-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 mb-1">No hay historial médico</h3>
                        <p class="text-gray-500">No se encontraron registros de atención para el paciente seleccionado.</p>
                    </div>
                <?php elseif ($historial): ?>
                    <div class="space-y-4">
                        <h3 class="text-2xl font-bold text-cyan-800 mb-4">Historial de Consultas</h3>
                        <?php foreach ($historial as $h): ?>
                        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-cyan-500 hover:shadow-lg transition-shadow border border-gray-100">
                            <div class="flex justify-between items-start mb-3">
                                <p class="font-semibold text-gray-800 text-lg">Consulta: <?php echo date('d/m/Y H:i', strtotime($h['fecha_hora_cita'])); ?></p>
                                <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded">Atendida</span>
                            </div>
                            
                            <?php if ($h['diagnostico']): ?>
                                <div class="mb-4 pt-4 border-t border-gray-100">
                                    <h4 class="text-sm font-semibold text-cyan-700 mb-1 flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 text-cyan-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        Diagnóstico
                                    </h4>
                                    <p class="text-gray-700 bg-gray-50 p-3 rounded-lg border border-gray-100"><?php echo nl2br(htmlspecialchars($h['diagnostico'])); ?></p>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($h['recetas']): ?>
                                <div class="pt-4 border-t border-gray-100">
                                    <h4 class="text-sm font-semibold text-cyan-700 mb-1 flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 text-cyan-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                                        </svg>
                                        Receta
                                    </h4>
                                    <p class="text-gray-700 bg-gray-50 p-3 rounded-lg border border-gray-100"><?php echo nl2br(htmlspecialchars($h['recetas'])); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="bg-white rounded-xl shadow-md p-12 text-center border border-gray-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 mb-1">Selecciona un paciente</h3>
                        <p class="text-gray-500">Elige un paciente del menú de la izquierda para ver su historial médico.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="mt-8 grid grid-cols-2 md:grid-cols-4 gap-4">
            <?php
            $acciones = [
                // Degradado azul más intenso solicitado por el usuario
                ['Imprimir Informe', 'imprimir_informe.php' . ($id_paciente_seleccionado ? '?id_paciente=' . $id_paciente_seleccionado : ''), 'bg-gradient-to-r from-blue-600 to-indigo-500', '<i class="fas fa-print text-3xl"></i>'],
            ];
            foreach ($acciones as $a): ?>
            <a href="<?php echo $a[1]; ?>" class="group">
                <div class="bg-white rounded-xl shadow-md overflow-hidden transform transition-all duration-300 hover:scale-105 hover:shadow-xl border border-gray-100">
                    <div class="<?php echo $a[2]; ?> p-5 text-center">
                        <span class="block mb-2 text-white"><?php echo $a[3]; ?></span>
                        <h3 class="font-bold text-sm text-white"><?php echo $a[0]; ?></h3>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/pie_pagina.php'; ?>