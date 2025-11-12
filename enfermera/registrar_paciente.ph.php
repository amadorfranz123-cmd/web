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
    $nombres = trim($_POST['nombres']);
    $apellidos = trim($_POST['apellidos']);
    $telefono = trim($_POST['telefono']);
    $email = trim($_POST['email']);

    try {
        $db_connection->beginTransaction();

        $stmt = $db_connection->prepare("INSERT INTO persona (nombres, apellidos, telefono, email) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nombres, $apellidos, $telefono, $email]);
        $id_persona = $db_connection->lastInsertId();

        $stmt = $db_connection->prepare("INSERT INTO paciente (id_persona) VALUES (?)");
        $stmt->execute([$id_persona]);

        $db_connection->commit();
        $mensaje = '<div class="alert-success">Paciente registrado con éxito.</div>';
    } catch (PDOException $e) {
        $db_connection->rollBack();
        $mensaje = '<div class="alert-danger">Error: ' . $e->getMessage() . '</div>';
    }
}
?>
<?php require_once '../includes/cabecera.php'; ?>

<div class="max-w-4xl mx-auto">
    <div class="card mb-6">
        <h2 class="text-3xl font-bold text-emerald-800 mb-2">Registrar Paciente</h2>
        <a href="panel.php" class="text-cyan-600 hover:underline text-sm">Volver al panel</a>
    </div>

    <div class="card">
        <?php echo $mensaje; ?>
        <form method="POST" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Nombres</label>
                    <input type="text" name="nombres" required class="input-custom">
                </div>
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Apellidos</label>
                    <input type="text" name="apellidos" required class="input-custom">
                </div>
            </div>
            <div>
                <label class="block text-gray-700 font-medium mb-2">Teléfono</label>
                <input type="tel" name="telefono" class="input-custom" placeholder="Ej: 04141234567">
            </div>
            <div>
                <label class="block text-gray-700 font-medium mb-2">Email</label>
                <input type="email" name="email" required class="input-custom">
            </div>
            <button type="submit" class="btn-primary w-full">Registrar Paciente</button>
        </form>
		<a href="registrar_turno.php">registrar turno </a>
    </div>
</div>

<?php require_once '../includes/pie_pagina.php'; ?>