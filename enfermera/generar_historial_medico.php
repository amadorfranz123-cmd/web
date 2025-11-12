<?php
session_start();
require_once '../config/conexion_bd.php';
require_once '../includes/autenticacion.php';
redirectIfNotLoggedIn();

if ($_SESSION['rol'] !== 'enfermera') {
    header('Location: ../index.php');
    exit;
}

 $pacientes = $db_connection->query("
    SELECT pa.id_persona, p.nombres, p.apellidos 
    FROM paciente pa 
    JOIN persona p ON pa.id_persona = p.id_persona
    ORDER BY p.nombres
")->fetchAll();

 $historial = [];

if (isset($_GET['id_paciente']) && is_numeric($_GET['id_paciente'])) {
    $id_paciente = (int)$_GET['id_paciente'];
    try {
        $stmt = $db_connection->prepare("
            SELECT c.*, m.nombres AS medico, a.diagnostico, a.recetas 
            FROM cita c 
            JOIN personal_de_salud ps ON c.id_doctor = ps.id_medico 
            JOIN persona m ON ps.id_medico = m.id_persona 
            LEFT JOIN atencion a ON c.id_atencion = a.id_atencion 
            WHERE c.id_paciente = ? AND c.estado = 'atendida'
            ORDER BY c.fecha_hora_cita DESC
        ");
        $stmt->execute([$id_paciente]);
        $historial = $stmt->fetchAll();
    } catch (PDOException $e) {
        $mensaje = '<div class="alert-danger">Error: ' . $e->getMessage() . '</div>';
    }
}
?>
<?php require_once '../includes/cabecera.php'; ?>

<div class="max-w-6xl mx-auto">
    <div class="card mb-6">
        <h2 class="text-3xl font-bold text-emerald-800 mb-2">Generar Historial Médico</h2>
        <a href="panel.php" class="text-cyan-600 hover:underline text-sm">Volver al panel</a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="card">
            <h3 class="font-semibold mb-4">Seleccionar Paciente</h3>
            <form method="GET" class="space-y-3">
                <select name="id_paciente" required class="input-custom" onchange="this.form.submit()">
                    <option value="">-- Seleccionar --</option>
                    <?php foreach ($pacientes as $p): ?>
                        <option value="<?php echo $p['id_persona']; ?>" <?php echo (isset($_GET['id_paciente']) && $_GET['id_paciente'] == $p['id_persona']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($p['nombres'] . ' ' . $p['apellidos']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>

        <div class="md:col-span-2 card">
            <?php if ($historial): ?>
                <div class="space-y-4">
                    <?php foreach ($historial as $h): ?>
                    <div class="p-4 border-l-4 border-emerald-500 bg-emerald-50 rounded-r">
                        <p class="font-semibold">Dr. <?php echo htmlspecialchars($h['medico']); ?> - <?php echo date('d/m/Y', strtotime($h['fecha_hora_cita'])); ?></p>
                        <?php if ($h['diagnostico']): ?>
                            <p class="mt-2"><strong>Diagnóstico:</strong> <?php echo nl2br(htmlspecialchars($h['diagnostico'])); ?></p>
                        <?php endif; ?>
                        <?php if ($h['recetas']): ?>
                            <p class="mt-1"><strong>Receta:</strong> <?php echo nl2br(htmlspecialchars($h['recetas'])); ?></p>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php elseif (isset($_GET['id_paciente'])): ?>
                <p class="text-center py-8 text-gray-500">No hay historial médico para este paciente.</p>
            <?php else: ?>
                <p class="text-center py-8 text-gray-500">Selecciona un paciente para ver su historial.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Botones mejorados -->
    <div class="mt-8 flex flex-wrap gap-4 justify-center">
        <a href="enviar_recordatorio.php" class="bg-teal-600 hover:bg-teal-700 text-white px-6 py-3 rounded-lg font-medium shadow-md transition duration-300 flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
            Enviar Recordatorio
        </a>
        <a href="actualizar_historial_medico.php" class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-3 rounded-lg font-medium shadow-md transition duration-300 flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
            Actualizar Historial Médico
        </a>
		
    </div>
</div>

<?php require_once '../includes/pie_pagina.php'; ?>