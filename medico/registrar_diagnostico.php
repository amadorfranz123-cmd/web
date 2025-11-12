<?php
session_start();
require_once '../config/conexion_bd.php';
require_once '../includes/autenticacion.php';
redirectIfNotLoggedIn();

if ($_SESSION['rol'] !== 'medico') {
    header('Location: ../index.php');
    exit;
}

if (!isset($_SESSION['id_persona'])) {
    die("<div class='alert-danger'>Error de sesión. <a href='../cerrar_sesion.php'>Volver a iniciar</a></div>");
}
$id_medico = $_SESSION['id_persona'];

$mensaje = '';
$cita = null;

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id_cita = (int)$_GET['id'];
    try {
        $stmt = $db_connection->prepare("
            SELECT c.*, p.nombres AS paciente 
            FROM cita c 
            JOIN paciente pa ON c.id_paciente = pa.id_persona
            JOIN persona p ON pa.id_persona = p.id_persona
            WHERE c.id_cita = ? AND c.id_doctor = ? AND c.estado = 'pendiente'
        ");
        $stmt->execute([$id_cita, $id_medico]);
        $cita = $stmt->fetch();
    } catch (PDOException $e) {
        $mensaje = '<div class="alert-danger">Error: ' . $e->getMessage() . '</div>';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $cita) {
    $diagnostico = trim($_POST['diagnostico']);
    $recetas = trim($_POST['recetas']);

    try {
        $db_connection->beginTransaction();

        $stmt = $db_connection->prepare("INSERT INTO atencion (id_medico, diagnostico, recetas) VALUES (?, ?, ?)");
        $stmt->execute([$id_medico, $diagnostico, $recetas]);
        $id_atencion = $db_connection->lastInsertId();

        $stmt = $db_connection->prepare("UPDATE cita SET id_atencion = ?, estado = 'atendida' WHERE id_cita = ?");
        $stmt->execute([$id_atencion, $id_cita]);

        $db_connection->commit();
        $mensaje = '<div class="alert-success">Diagnóstico registrado y cita atendida.</div>';
        $cita = null; // Evita volver a mostrar el formulario
    } catch (PDOException $e) {
        $db_connection->rollBack();
        $mensaje = '<div class="alert-danger">Error: ' . $e->getMessage() . '</div>';
    }
}
?>
<?php require_once '../includes/cabecera.php'; ?>

<div class="max-w-4xl mx-auto">
    <div class="card mb-6">
        <h2 class="text-3xl font-bold text-emerald-800 mb-2">Registrar Diagnóstico</h2>
        <a href="ver_citas.php" class="text-cyan-600 hover:underline text-sm">Volver a citas</a>
    </div>

    <?php echo $mensaje; ?>

    <?php if ($cita): ?>
    <div class="card p-6">
        <div class="mb-6">
            <p><strong>Paciente:</strong> <?php echo htmlspecialchars($cita['paciente']); ?></p>
            <p><strong>Fecha:</strong> <?php echo date('d/m/Y H:i', strtotime($cita['fecha_hora_cita'])); ?></p>
            <p><strong>Motivo:</strong> <?php echo htmlspecialchars($cita['motivo_cita'] ?: '—'); ?></p>
        </div>

        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-gray-700 font-medium mb-2">Diagnóstico</label>
                <textarea name="diagnostico" required class="input-custom h-32" placeholder="Describa el diagnóstico..."></textarea>
            </div>
            <div>
                <label class="block text-gray-700 font-medium mb-2">Receta Médica</label>
                <textarea name="recetas" class="input-custom h-32" placeholder="Medicamentos, dosis, indicaciones..."></textarea>
            </div>
            <button type="submit" class="btn-primary w-full">Guardar y Atender</button>
        </form>
    </div>
    <?php else: ?>
    <div class="card text-center py-12">
        <p class="text-gray-500">Selecciona una cita desde <a href="ver_citas.php" class="text-cyan-600 underline">Ver Citas</a>.</p>
    </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/pie_pagina.php'; ?>