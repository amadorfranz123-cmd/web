<?php
session_start();
require_once './config/conexion_bd.php';
require_once './includes/autenticacion.php';

$register_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_usuario = trim($_POST['nombre_usuario'] ?? '');
    $contrasena     = trim($_POST['contrasena'] ?? '');
    $nombres        = trim($_POST['nombres'] ?? '');
    $apellidos      = trim($_POST['apellidos'] ?? '');
    $email          = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $rol            = $_POST['rol'] ?? '';

    // Validaciones
    $errores = [];
    if (empty($nombre_usuario)) $errores[] = "Usuario es obligatorio.";
    if (empty($contrasena)) $errores[] = "Contraseña es obligatoria.";
    if (empty($nombres)) $errores[] = "Nombres son obligatorios.";
    if (empty($apellidos)) $errores[] = "Apellidos son obligatorios.";
    if (!$email) $errores[] = "Email inválido.";
    if (!in_array($rol, ['paciente', 'medico', 'enfermera', 'admin'])) {
        $errores[] = "Rol no válido.";
    }

    if (empty($errores)) {
        try {
            $db_connection->beginTransaction();

            // Límite por rol
            if (in_array($rol, ['medico', 'enfermera', 'admin'])) {
                $campo = $rol === 'admin' ? 'admin' : $rol;
                $stmt = $db_connection->prepare("SELECT COUNT(*) FROM personal_de_salud WHERE rol = ?");
                $stmt->execute([$campo]);
                $count = $stmt->fetchColumn();
                $max = match ($campo) { 'admin' => 2, 'medico', 'enfermera' => 1 };
                if ($count >= $max) {
                    throw new Exception("Límite alcanzado: solo $max " . ($max == 1 ? $campo : $campo . "s") . ".");
                }
            }

            // Verificar duplicados
            $check = $db_connection->prepare("SELECT 1 FROM usuario WHERE nombre_usuario = ? OR correo = ?");
            $check->execute([$nombre_usuario, $email]);
            if ($check->fetch()) {
                throw new Exception("El usuario o email ya existe.");
            }

            // Insertar persona
            $stmt = $db_connection->prepare("INSERT INTO persona (nombres, apellidos, email) VALUES (?, ?, ?)");
            $stmt->execute([$nombres, $apellidos, $email]);
            $id_persona = $db_connection->lastInsertId();

            // Insertar rol
            if ($rol === 'paciente') {
                $db_connection->prepare("INSERT INTO paciente (id_persona) VALUES (?)")->execute([$id_persona]);
            } else {
                $db_connection->prepare("INSERT INTO personal_de_salud (id_persona, rol) VALUES (?, ?)")
                    ->execute([$id_persona, $rol]);
            }

            // Insertar usuario
            $hash = password_hash($contrasena, PASSWORD_DEFAULT);
            $db_connection->prepare("
                INSERT INTO usuario (id_persona, contrasena, nombre_usuario, correo, activo) 
                VALUES (?, ?, ?, ?, 1)
            ")->execute([$id_persona, $hash, $nombre_usuario, $email]);

            $db_connection->commit();
            header('Location: ./iniciar_sesion.php?success=1');
            exit;
        } catch (Exception $e) {
            $db_connection->rollBack();
            $register_error = $e->getMessage();
        }
    } else {
        $register_error = implode('<br>', $errores);
    }
}
?>

<?php require_once './includes/cabecera.php'; ?>
<div class="max-w-md mx-auto bg-white p-6 rounded shadow">
    <h2 class="text-2xl mb-4 text-center font-bold">Registrarse</h2>
    <?php if ($register_error): ?>
        <p class="text-red-600 text-center mb-4 font-medium"><?= htmlspecialchars($register_error) ?></p>
    <?php endif; ?>
    <form method="POST" class="space-y-4">
        <input type="text" name="nombre_usuario" placeholder="Usuario" required 
               value="<?= htmlspecialchars($_POST['nombre_usuario'] ?? '') ?>"
               class="w-full border p-3 rounded text-black placeholder-gray-500">
        <input type="password" name="contrasena" placeholder="Contraseña" required 
               class="w-full border p-3 rounded text-black placeholder-gray-500">
        <input type="text" name="nombres" placeholder="Nombres" required 
               value="<?= htmlspecialchars($_POST['nombres'] ?? '') ?>"
               class="w-full border p-3 rounded text-black placeholder-gray-500">
        <input type="text" name="apellidos" placeholder="Apellidos" required 
               value="<?= htmlspecialchars($_POST['apellidos'] ?? '') ?>"
               class="w-full border p-3 rounded text-black placeholder-gray-500">
        <input type="email" name="email" placeholder="Email" required 
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
               class="w-full border p-3 rounded text-black placeholder-gray-500">
        <select name="rol" required class="w-full border p-3 rounded text-black">
            <option value="paciente" <?= ($rol ?? '') === 'paciente' ? 'selected' : '' ?>>Paciente</option>
            <option value="medico" <?= ($rol ?? '') === 'medico' ? 'selected' : '' ?>>Médico</option>
            <option value="enfermera" <?= ($rol ?? '') === 'enfermera' ? 'selected' : '' ?>>Enfermera</option>
        </select>
        <button type="submit" class="w-full bg-blue-900 text-white p-3 rounded font-bold hover:bg-blue-800 transition">
            Registrarse
        </button>
    </form>
    <p class="mt-4 text-center text-gray-700">
        <a href="./iniciar_sesion.php" class="text-blue-600 hover:underline font-medium">¿Ya tienes cuenta? Inicia sesión</a>
    </p>
</div>
<?php require_once './includes/pie_pagina.php'; ?>