<?php
session_start();
require_once '../config/conexion_bd.php';
require_once '../includes/autenticacion.php';

redirectIfNotLoggedIn();
if ($_SESSION['rol'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

$mensaje = '';
$errores = [];

// --- ELIMINAR PERSONAL ---
if (isset($_GET['eliminar'])) {
    $id_personal = (int)$_GET['eliminar'];
    try {
        $db_connection->beginTransaction();
        
        // Obtener id_persona
        $stmt = $db_connection->prepare("SELECT id_persona FROM personal_de_salud WHERE id_personal = ?");
        $stmt->execute([$id_personal]);
        $id_persona = $stmt->fetchColumn();

        if ($id_persona) {
            // Eliminar de personal_de_salud
            $db_connection->prepare("DELETE FROM personal_de_salud WHERE id_personal = ?")->execute([$id_personal]);
            // Eliminar usuario
            $db_connection->prepare("DELETE FROM usuario WHERE id_persona = ?")->execute([$id_persona]);
            // Eliminar persona
            $db_connection->prepare("DELETE FROM persona WHERE id_persona = ?")->execute([$id_persona]);
        }
        
        $db_connection->commit();
        $mensaje = '<div class="bg-green-100 text-green-800 p-4 rounded">Personal eliminado con éxito.</div>';
    } catch (Exception $e) {
        $db_connection->rollBack();
        $mensaje = '<div class="bg-red-100 text-red-800 p-4 rounded">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}

// --- EDITAR PERSONAL ---
$editar = null;
if (isset($_GET['editar'])) {
    $id_personal = (int)$_GET['editar'];
    $stmt = $db_connection->prepare("
        SELECT ps.*, p.nombres, p.apellidos, p.telefono, p.email, u.nombre_usuario 
        FROM personal_de_salud ps 
        JOIN persona p ON ps.id_persona = p.id_persona 
        JOIN usuario u ON p.id_persona = u.id_persona 
        WHERE ps.id_personal = ?
    ");
    $stmt->execute([$id_personal]);
    $editar = $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar'])) {
    $id_personal = (int)$_POST['id_personal'];
    $nombres = trim($_POST['nombres']);
    $apellidos = trim($_POST['apellidos']);
    $telefono = trim($_POST['telefono']);
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $usuario = trim($_POST['usuario']);

    if (!$email) $errores[] = "Email inválido.";
    if (empty($nombres)) $errores[] = "Nombres obligatorios.";
    if (empty($apellidos)) $errores[] = "Apellidos obligatorios.";

    if (empty($errores)) {
        try {
            $db_connection->beginTransaction();
            
            // Actualizar persona
            $stmt = $db_connection->prepare("
                UPDATE persona p 
                JOIN personal_de_salud ps ON p.id_persona = ps.id_persona 
                SET p.nombres = ?, p.apellidos = ?, p.telefono = ?, p.email = ?
                WHERE ps.id_personal = ?
            ");
            $stmt->execute([$nombres, $apellidos, $telefono, $email, $id_personal]);

            // Actualizar usuario
            $db_connection->prepare("
                UPDATE usuario u 
                JOIN personal_de_salud ps ON u.id_persona = ps.id_persona 
                SET u.nombre_usuario = ?, u.correo = ?
                WHERE ps.id_personal = ?
            ")->execute([$usuario, $email, $id_personal]);

            $db_connection->commit();
            $mensaje = '<div class="bg-green-100 text-green-800 p-4 rounded">Datos actualizados con éxito.</div>';
            $editar = null;
        } catch (Exception $e) {
            $db_connection->rollBack();
            $mensaje = '<div class="bg-red-100 text-red-800 p-4 rounded">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    } else {
        $mensaje = '<div class="bg-yellow-100 text-yellow-800 p-4 rounded"><ul class="list-disc pl-5">' . 
                   implode('', array_map(fn($e) => "<li>$e</li>", $errores)) . '</ul></div>';
    }
}

// --- LISTAR PERSONAL ---
$stmt = $db_connection->query("
    SELECT ps.id_personal, ps.rol, p.nombres, p.apellidos, p.telefono, p.email, u.nombre_usuario 
    FROM personal_de_salud ps 
    JOIN persona p ON ps.id_persona = p.id_persona 
    JOIN usuario u ON p.id_persona = u.id_persona 
    WHERE ps.rol IN ('medico', 'enfermera')
    ORDER BY ps.rol, p.apellidos
");
$personal = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php require_once '../includes/cabecera.php'; ?>
<div class="max-w-6xl mx-auto p-6">
    <div class="bg-white shadow-lg rounded-xl p-6 mb-6">
        <h2 class="text-2xl font-bold text-emerald-700 mb-4">Gestión de Personal Médico</h2>
        <a href="panel.php" class="text-cyan-600 hover:underline text-sm">Volver al panel</a>
    </div>

    <div class="bg-white shadow-lg rounded-xl p-6">
        <?= $mensaje ?>

        <?php if ($editar): ?>
            <div class="mb-8 p-6 bg-blue-50 rounded-lg">
                <h3 class="text-lg font-bold text-blue-900 mb-4">Editar Personal</h3>
                <form method="POST" class="space-y-4">
                    <input type="hidden" name="id_personal" value="<?= $editar['id_personal'] ?>">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <input type="text" name="nombres" value="<?= htmlspecialchars($editar['nombres']) ?>" 
                               placeholder="Nombres" required class="w-full border p-3 rounded text-black">
                        <input type="text" name="apellidos" value="<?= htmlspecialchars($editar['apellidos']) ?>" 
                               placeholder="Apellidos" required class="w-full border p-3 rounded text-black">
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <input type="tel" name="telefono" value="<?= htmlspecialchars($editar['telefono']) ?>" 
                               placeholder="Teléfono" class="w-full border p-3 rounded text-black">
                        <input type="email" name="email" value="<?= htmlspecialchars($editar['email']) ?>" 
                               placeholder="Email" required class="w-full border p-3 rounded text-black">
                    </div>
                    <div>
                        <input type="text" name="usuario" value="<?= htmlspecialchars($editar['nombre_usuario']) ?>" 
                               placeholder="Usuario" required class="w-full border p-3 rounded text-black">
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" name="actualizar" 
                                class="bg-emerald-600 text-white px-6 py-2 rounded font-bold hover:bg-emerald-700">
                            Guardar Cambios
                        </button>
                        <a href="gestionar_personal.php" 
                           class="bg-gray-500 text-white px-6 py-2 rounded font-bold hover:bg-gray-600">
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-emerald-700 text-white">
                        <th class="p-3 text-left">Rol</th>
                        <th class="p-3 text-left">Nombres</th>
                        <th class="p-3 text-left">Apellidos</th>
                        <th class="p-3 text-left">Teléfono</th>
                        <th class="p-3 text-left">Email</th>
                        <th class="p-3 text-left">Usuario</th>
                        <th class="p-3 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($personal as $p): ?>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="p-3"><?= ucfirst($p['rol']) ?></td>
                            <td class="p-3"><?= htmlspecialchars($p['nombres']) ?></td>
                            <td class="p-3"><?= htmlspecialchars($p['apellidos']) ?></td>
                            <td class="p-3"><?= htmlspecialchars($p['telefono']) ?></td>
                            <td class="p-3"><?= htmlspecialchars($p['email']) ?></td>
                            <td class="p-3"><?= htmlspecialchars($p['nombre_usuario']) ?></td>
                            <td class="p-3 text-center">
                                <a href="" 
                                   class="text-blue-600 hover:underline mr-3">Editar</a>
                                <a href="" 
                                   onclick="return confirm('¿Eliminar a <?= htmlspecialchars($p['nombres'] . ' ' . $p['apellidos']) ?>?')"
                                   class="text-red-600 hover:underline">Eliminar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($personal)): ?>
                        <tr><td colspan="7" class="p-6 text-center text-gray-500">No hay personal registrado.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require_once '../includes/pie_pagina.php'; ?>