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
$datos = []; 

try {
    // 1. OBTENER DATOS ACTUALES
    $stmt = $db_connection->prepare("SELECT * FROM persona WHERE id_persona = ?");
    $stmt->execute([$id_persona]);
    $datos = $stmt->fetch();

    if (!$datos) {
        throw new Exception("No se encontraron datos del usuario.");
    }
    
    // Asignar valores por defecto (si no existen)
    $datos['telefono'] = $datos['telefono'] ?? '';
    $datos['email'] = $datos['email'] ?? '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nombres_raw = trim($_POST['nombres']);
        $apellidos_raw = trim($_POST['apellidos']);
        $telefono_raw = trim($_POST['telefono']);
        $email_raw = trim($_POST['email']);
        
        $errores = [];

        // --- VALIDACIÓN 1: NOMBRES Y APELLIDOS (Solo letras y MÁX. 15 caracteres)
        // Se incluyen letras, acentos y espacios.
        if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚ\s]+$/', $nombres_raw) || strlen($nombres_raw) > 15) {
            // ¡CAMBIO CLAVE AQUÍ! Se cambió el límite de 50 a 15.
            $errores[] = "Nombres solo deben contener letras (Máx. 15 caracteres).";
        }
        if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚ\s]+$/', $apellidos_raw) || strlen($apellidos_raw) > 15) {
            // ¡CAMBIO CLAVE AQUÍ! Se cambió el límite de 50 a 15.
            $errores[] = "Apellidos solo deben contener letras (Máx. 15 caracteres).";
        }

        // --- VALIDACIÓN 2: TELÉFONO (Solo números y longitud EXACTA de 8 dígitos)
        if (!preg_match('/^\d{8}$/', $telefono_raw)) {
            $errores[] = "Teléfono debe contener exactamente 8 dígitos numéricos.";
        }
        
        // --- VALIDACIÓN 3: EMAIL (@gmail.com requerido)
        $email_sanitized = filter_var($email_raw, FILTER_SANITIZE_EMAIL);
        if (!filter_var($email_sanitized, FILTER_VALIDATE_EMAIL)) {
            $errores[] = "El formato del correo electrónico es inválido.";
        } else if (!str_ends_with(strtolower($email_sanitized), '@gmail.com')) {
            $errores[] = "Solo se permiten correos electrónicos de dominio @gmail.com.";
        }
        
        if (empty($errores)) {
            // Saneamiento final después de la validación
            $nombres = htmlspecialchars($nombres_raw);
            $apellidos = htmlspecialchars($apellidos_raw);
            $telefono = htmlspecialchars($telefono_raw);
            $email = htmlspecialchars($email_sanitized); 

            $update = $db_connection->prepare("
                UPDATE persona 
                SET nombres = ?, apellidos = ?, telefono = ?, email = ? 
                WHERE id_persona = ?
            ");
            $update->execute([$nombres, $apellidos, $telefono, $email, $id_persona]);

            // Actualizar la sesión y array de datos
            $_SESSION['nombre'] = "$nombres $apellidos";
            $mensaje = '<div class="bg-green-100 text-green-800 px-4 py-2 rounded-lg text-center mb-4">✅ Datos actualizados con éxito.</div>';
            
            $datos['nombres'] = $nombres;
            $datos['apellidos'] = $apellidos;
            $datos['telefono'] = $telefono;
            $datos['email'] = $email;

        } else {
             // Mostrar errores y mantener datos introducidos
            $mensaje = '<div class="bg-red-100 text-red-800 px-4 py-2 rounded-lg text-center mb-4">❌ Por favor, corrija los siguientes errores:<br>' . implode('<br>', $errores) . '</div>';
            $datos['nombres'] = $nombres_raw;
            $datos['apellidos'] = $apellidos_raw;
            $datos['telefono'] = $telefono_raw;
            $datos['email'] = $email_raw;
        }
    }
} catch (Exception $e) {
    $mensaje = '<div class="bg-red-100 text-red-800 px-4 py-2 rounded-lg text-center mb-4">❌ Error de Base de Datos: ' . htmlspecialchars($e->getMessage()) . '</div>';
}
?>

<?php require_once '../includes/cabecera.php'; ?>

<div class="min-h-screen flex justify-center items-center bg-gradient-to-b from-emerald-50 to-emerald-100 py-10 px-4">
    <div class="w-full max-w-3xl bg-white shadow-xl rounded-2xl p-10">

        <h2 class="text-4xl font-bold text-emerald-700 text-center mb-2">Actualizar Datos Personales</h2>
        <p class="text-gray-600 text-center mb-6">Modifica tu información de contacto para mantener tus datos al día.</p>

        <?php echo $mensaje; ?>

        <form method="POST" class="space-y-6">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Nombres (Solo letras, Máx. 15)</label>
                    <input 
                        type="text" 
                        name="nombres" 
                        value="<?php echo htmlspecialchars($datos['nombres']); ?>" 
                        required 
                        class="w-full p-3 rounded-lg border border-gray-300 bg-green-50 text-gray-800 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-emerald-400" 
                        placeholder="Máx. 15 caracteres"
                        pattern="[a-zA-ZáéíóúÁÉÍÓÚ\s]+"
                        maxlength="15">
                        </div>

                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Apellidos (Solo letras, Máx. 15)</label>
                    <input 
                        type="text" 
                        name="apellidos" 
                        value="<?php echo htmlspecialchars($datos['apellidos']); ?>" 
                        required 
                        class="w-full p-3 rounded-lg border border-gray-300 bg-green-50 text-gray-800 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-emerald-400" 
                        placeholder="Máx. 15 caracteres"
                        pattern="[a-zA-ZáéíóúÁÉÍÓÚ\s]+"
                        maxlength="15">
                        </div>
            </div>

            <div>
                <label class="block text-gray-700 font-semibold mb-2">Teléfono (8 dígitos)</label>
                <input 
                    type="tel" 
                    name="telefono" 
                    value="<?php echo htmlspecialchars($datos['telefono']); ?>" 
                    class="w-full p-3 rounded-lg border border-blue-300 bg-blue-50 text-gray-800 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-400" 
                    placeholder="Ej. 71234567"
                    pattern="\d{8}"
                    maxlength="8">
            </div>

            <div>
                <label class="block text-gray-700 font-semibold mb-2">Correo Electrónico (Solo @gmail.com)</label>
                <input 
                    type="email" 
                    name="email" 
                    value="<?php echo htmlspecialchars($datos['email']); ?>" 
                    required 
                    class="w-full p-3 rounded-lg border border-blue-300 bg-blue-50 text-gray-800 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-400" 
                    placeholder="Ej. tunombre@gmail.com">
            </div>

            <button 
                type="submit" 
                class="w-full py-3 rounded-lg bg-emerald-600 text-white font-semibold shadow-md hover:bg-emerald-700 transition">
                💾 Guardar Cambios
            </button>
        </form>

        <div class="mt-6 text-center">
            <a href="panel.php" class="text-emerald-600 hover:text-emerald-800 font-medium transition">
                ← Volver al Panel
            </a>
        </div>
    </div>
</div>

<?php require_once '../includes/pie_pagina.php'; ?>