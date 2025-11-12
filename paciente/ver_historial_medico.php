<?php
session_start();
require_once '../config/conexion_bd.php';
require_once '../includes/autenticacion.php';
redirectIfNotLoggedIn();

if ($_SESSION['rol'] !== 'paciente') {
    header('Location: ../index.php');
    exit;
}

if (!isset($_SESSION['id_persona'])) {
    die("<div class='alert-danger'>Error de sesión. <a href='../cerrar_sesion.php'>Volver a iniciar</a></div>");
}

$id_persona = $_SESSION['id_persona'];

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
    $stmt->execute([$id_persona]);
    $historico = $stmt->fetchAll();
} catch (PDOException $e) {
    die("<div class='alert-danger'>Error: " . $e->getMessage() . "</div>");
}
?>

<?php require_once '../includes/cabecera.php'; ?>

<div class="min-h-screen flex flex-col items-center justify-center bg-gradient-to-b from-emerald-50 to-emerald-100 py-12 px-4">
    <div class="max-w-5xl w-full bg-white shadow-lg rounded-2xl p-10">
        <div class="text-center mb-8">
            <h2 class="text-4xl font-extrabold text-emerald-700 mb-3">Mi Historial Médico</h2>
            <p class="text-gray-600 mb-4">Aquí puedes consultar tus citas atendidas, diagnósticos y recetas médicas.</p>
            <a href="panel.php" class="text-emerald-600 font-semibold hover:text-emerald-800 transition">← Volver al Panel</a>
        </div>

        <div class="divider my-6 border-t-2 border-emerald-200"></div>

        <?php if (empty($historico)): ?>
            <p class="text-center text-gray-500 text-lg py-10">No tienes historial médico registrado.</p>
        <?php else: ?>
            <div class="space-y-6">
                <?php foreach ($historico as $h): ?>
                <div class="p-6 border-l-4 border-emerald-500 bg-white rounded-r-lg shadow hover:shadow-md transition">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="text-2xl font-semibold text-emerald-800">
                                Dr. <?php echo htmlspecialchars($h['medico']); ?>
                            </h3>
                            <p class="text-gray-600 mt-1">
                                📅 <strong>Fecha:</strong> <?php echo date('d/m/Y H:i', strtotime($h['fecha_hora_cita'])); ?>
                            </p>
                        </div>
                        <span class="bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full text-sm font-medium">
                            Atendida
                        </span>
                    </div>

                    <?php if ($h['diagnostico']): ?>
                        <div class="mt-5 bg-emerald-50 p-4 rounded-lg border border-emerald-100">
                            <p class="text-gray-700">
                                <strong class="text-emerald-800">Diagnóstico:</strong><br>
                                <?php echo nl2br(htmlspecialchars($h['diagnostico'])); ?>
                            </p>
                        </div>
                    <?php endif; ?>

                    <?php if ($h['recetas']): ?>
                        <div class="mt-3 bg-blue-50 p-4 rounded-lg border border-blue-100">
                            <p class="text-gray-700">
                                <strong class="text-blue-800">Receta:</strong><br>
                                <?php echo nl2br(htmlspecialchars($h['recetas'])); ?>
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/pie_pagina.php'; ?>
