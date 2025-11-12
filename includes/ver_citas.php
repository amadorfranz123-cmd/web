<?php
session_start();
require_once '../config/conexion_bd.php';
require_once '../includes/autenticacion.php';
redirectIfNotLoggedIn();
if ($_SESSION['rol'] !== 'medico') { header('Location: ../index.php'); exit; }

$stmt = $db_connection->prepare("
    SELECT c.id_cita, p.nombres AS paciente, c.fecha_hora_cita, c.motivo_cita 
    FROM cita c 
    JOIN persona p ON c.id_paciente = p.id_persona 
    JOIN personal_de_salud ps ON c.id_doctor = ps.id_medico 
    WHERE ps.id_medico = ?
");
$stmt->execute([$_SESSION['user_id']]);
$citas = $stmt->fetchAll();
?>
<?php require_once '../includes/cabecera.php'; ?>
<h2 class="text-2xl text-center mb-6">Mis Citas</h2>
<?php if ($citas): ?>
<table class="w-full border">
    <thead class="bg-gray-200">
        <tr><th>Paciente</th><th>Fecha</th><th>Motivo</th></tr>
    </thead>
    <tbody>
        <?php foreach ($citas as $c): ?>
        <tr>
            <td class="p-2"><?php echo htmlspecialchars($c['paciente']); ?></td>
            <td class="p-2"><?php echo htmlspecialchars($c['fecha_hora_cita']); ?></td>
            <td class="p-2"><?php echo htmlspecialchars($c['motivo_cita'] ?: '—'); ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php else: ?>
<p class="text-center">No tienes citas programadas.</p>
<?php endif; ?>
<?php require_once '../includes/pie_pagina.php'; ?>