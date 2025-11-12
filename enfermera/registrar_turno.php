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
$medicos = $db_connection->query("
    SELECT ps.id_medico, p.nombres 
    FROM personal_de_salud ps 
    JOIN persona p ON ps.id_medico = p.id_persona 
    WHERE ps.rol = 'medico'
")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_medico = (int)$_POST['id_medico'];
    $nombre_paciente = trim($_POST['nombre_paciente']);
    $hora_inicio = $_POST['hora_inicio'];
    $hora_fin = $_POST['hora_fin'];

    try {
        $stmt = $db_connection->prepare("
            INSERT INTO turno (id_medico, nombre_paciente, hora_inicio, hora_fin) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$id_medico, $nombre_paciente, $hora_inicio, $hora_fin]);
        $mensaje = '<div class="alert-success">Turno registrado con éxito.</div>';
    } catch (PDOException $e) {
        $mensaje = '<div class="alert-danger">Error: ' . $e->getMessage() . '</div>';
    }
}
?>
<?php require_once '../includes/cabecera.php'; ?>

<div class="max-w-4xl mx-auto">
    <div class="card mb-6">
        <h2 class="text-3xl font-bold text-emerald-800 mb-2">Registrar Turno</h2>
        <a href="panel.php" class="text-cyan-600 hover:underline text-sm">Volver al panel</a>
    </div>

    <div class="card">
        <?php echo $mensaje; ?>
        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-gray-700 font-medium mb-2">Médico</label>
                <select name="id_medico" required class="input-custom">
                    <option value="">Seleccionar médico...</option>
                    <?php foreach ($medicos as $m): ?>
                        <option value="<?php echo $m['id_medico']; ?>">Dr. <?php echo htmlspecialchars($m['nombres']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-gray-700 font-medium mb-2">Nombre del Paciente</label>
                <input type="text" name="nombre_paciente" required class="input-custom" placeholder="Ej: Juan Pérez">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Hora Inicio</label>
                    <input type="time" name="hora_inicio" required class="input-custom">
                </div>
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Hora Fin</label>
                    <input type="time" name="hora_fin" required class="input-custom">
                </div>
            </div>
            <button type="submit" class="btn-primary w-full">Registrar Turno</button>
        </form>
    </div>
</div>

<?php require_once '../includes/pie_pagina.php'; ?>