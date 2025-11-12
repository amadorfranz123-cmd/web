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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_cita = (int)$_POST['id_cita'];
    $metodo = $_POST['metodo']; // 'email' o 'sms'

    try {
        $stmt = $db_connection->prepare("
            SELECT c.id_cita, p.nombres AS paciente, p.email, p.telefono, c.fecha_hora_cita 
            FROM cita c 
            JOIN paciente pa ON c.id_paciente = pa.id_persona
            JOIN persona p ON pa.id_persona = p.id_persona
            WHERE c.id_cita = ? AND c.estado = 'pendiente'
        ");
        $stmt->execute([$id_cita]);
        $cita = $stmt->fetch();

        if ($cita) {
            $fecha = date('d/m/Y H:i', strtotime($cita['fecha_hora_cita']));
            $texto = "Recordatorio: Tienes cita médica el $fecha. ¡No olvides asistir!";

            if ($metodo === 'email' && $cita['email']) {
                // Simulación de envío
                $mensaje = '<div class="alert-success">Recordatorio enviado por email a ' . htmlspecialchars($cita['email']) . '</div>';
            } elseif ($metodo === 'sms' && $cita['telefono']) {
                $mensaje = '<div class="alert-success">Recordatorio enviado por SMS a ' . htmlspecialchars($cita['telefono']) . '</div>';
            } else {
                $mensaje = '<div class="alert-danger">No hay ' . ($metodo === 'email' ? 'email' : 'teléfono') . ' registrado.</div>';
            }
        } else {
            $mensaje = '<div class="alert-danger">Cita no encontrada o ya cancelada.</div>';
        }
    } catch (PDOException $e) {
        $mensaje = '<div class="alert-danger">Error: ' . $e->getMessage() . '</div>';
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
    die("<div class='alert-danger'>Error: " . $e->getMessage() . "</div>");
}
?>
<?php require_once '../includes/cabecera.php'; ?>

<div class="max-w-6xl mx-auto">
    <div class="card mb-6">
        <h2 class="text-3xl font-bold text-emerald-800 mb-2">Enviar Recordatorio</h2>
        <a href="panel.php" class="text-cyan-600 hover:underline text-sm">Volver al panel</a>
    </div>

    <?php echo $mensaje; ?>

    <div class="card">
        <?php if (empty($citas)): ?>
            <p class="text-center py-10 text-gray-500">No hay citas pendientes.</p>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($citas as $c): ?>
                <div class="p-4 bg-white rounded-lg shadow">
                    <p class="font-semibold"><?php echo htmlspecialchars($c['paciente']); ?> - Dr. <?php echo htmlspecialchars($c['medico']); ?></p>
                    <p class="text-sm text-gray-600"><?php echo date('d/m/Y H:i', strtotime($c['fecha_hora_cita'])); ?></p>
                    <form method="POST" class="mt-2 flex gap-2">
                        <input type="hidden" name="id_cita" value="<?php echo $c['id_cita']; ?>">
                        <button type="submit" name="metodo" value="email" class="btn-sm bg-blue-600 text-white">Email</button>
                        <button type="submit" name="metodo" value="sms" class="btn-sm bg-green-600 text-white">SMS</button>
                    </form>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/pie_pagina.php'; ?>