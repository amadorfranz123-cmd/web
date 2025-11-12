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

$mensaje = '';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id_cita = (int)$_GET['id'];
    try {
        $stmt = $db_connection->prepare("
            UPDATE cita SET estado = 'cancelada' 
            WHERE id_cita = ? AND id_paciente = ? AND estado = 'pendiente'
        ");
        $stmt->execute([$id_cita, $id_persona]);
        $affected = $stmt->rowCount();
        $mensaje = $affected > 0 
            ? '<div class="alert-success">Cita cancelada con éxito.</div>'
            : '<div class="alert-danger">No se pudo cancelar: cita no encontrada o ya cancelada.</div>';
    } catch (PDOException $e) {
        $mensaje = '<div class="alert-danger">Error: ' . $e->getMessage() . '</div>';
    }
}

try {
    $stmt = $db_connection->prepare("
        SELECT c.id_cita, m.nombres AS medico, c.fecha_hora_cita 
        FROM cita c 
        JOIN personal_de_salud ps ON c.id_doctor = ps.id_medico 
        JOIN persona m ON ps.id_medico = m.id_persona 
        WHERE c.id_paciente = ? AND c.estado = 'pendiente'
        ORDER BY c.fecha_hora_cita ASC
    ");
    $stmt->execute([$id_persona]);
    $citas = $stmt->fetchAll();
} catch (PDOException $e) {
    die("<div class='alert-danger'>Error al cargar citas: " . $e->getMessage() . "</div>");
}
?>
<?php require_once '../includes/cabecera.php'; ?>

<div class="max-w-6xl mx-auto">
    <div class="card mb-8 text-center">
        <h2 class="text-3xl font-bold text-emerald-800 mb-4">Cancelar Cita</h2>
        <a href="panel.php" class="text-emerald-600 hover:underline flex items-center justify-center">
            Volver
        </a>
    </div>

    <?php echo $mensaje; ?>

    <div class="card">
        <?php if (empty($citas)): ?>
            <p class="text-center py-10 text-gray-500">No tienes citas pendientes.</p>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($citas as $c): ?>
                <div class="p-4 bg-white rounded-lg shadow flex justify-between items-center">
                    <div>
                        <p class="font-semibold">Dr. <?php echo htmlspecialchars($c['medico']); ?></p>
                        <p class="text-gray-600 text-sm"><?php echo date('d/m/Y H:i', strtotime($c['fecha_hora_cita'])); ?></p>
                    </div>
                    <a href="?id=<?php echo $c['id_cita']; ?>" 
                       class="text-red-600 hover:underline text-sm" 
                       onclick="return confirm('¿Cancelar esta cita?');">
                        Cancelar
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/pie_pagina.php'; ?>