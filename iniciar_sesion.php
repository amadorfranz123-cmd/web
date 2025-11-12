<?php
// ===============================================
// 1. INICIALIZACIÓN Y REQUERIMIENTOS
// ===============================================

// Iniciar sesión y establecer el almacenamiento de la sesión
session_start();

// Cargar archivos esenciales de configuración y funciones
require_once './config/conexion_bd.php';
require_once './includes/autenticacion.php';

// Inicializar la variable de error
$login_error = '';
$nombre_usuario = ''; 

// ===============================================
// 2. LÓGICA DE PROCESAMIENTO DEL FORMULARIO
// ===============================================

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nombre_usuario'], $_POST['contrasena'])) {
    
    // 2.1. Limpieza y saneamiento de datos de entrada
    $nombre_usuario = trim($_POST['nombre_usuario']);
    $contrasena = $_POST['contrasena']; 

    // ?? VALIDACIÓN 1: Campos vacíos
    if (empty($nombre_usuario) || empty($contrasena)) {
        $login_error = "Por favor, ingrese su nombre de usuario y contraseña.";
    }
    // ?? VALIDACIÓN 2: Nombre de usuario (15 caracteres MÁXIMO)
    else if (strlen($nombre_usuario) > 15) {
        $login_error = "El nombre de usuario no puede exceder los 15 caracteres.";
    } 
    // ?? VALIDACIÓN 3: Contraseña (15 caracteres MÁXIMO)
    else if (strlen($contrasena) > 15) {
        $login_error = "La contraseña no puede exceder los 15 caracteres.";
    }
    // ?? VALIDACIÓN 4: Patrón del Nombre de Usuario (SOLO LETRAS, PUNTOS Y GUIONES BAJOS - SIN NÚMEROS)
    else if (!preg_match('/^[a-zA-Z._]+$/', $nombre_usuario)) {
        $login_error = "El nombre de usuario solo puede contener letras, puntos y guiones bajos. No se permiten números.";
    }
    // Continuar solo si todas las validaciones pasan
    else {
        try {
            // 2.2. Preparación y ejecución de la consulta
            $stmt = $db_connection->prepare("
                SELECT u.id_usuario, u.id_persona, u.contrasena, p.nombres, p.apellidos 
                FROM usuario u 
                JOIN persona p ON u.id_persona = p.id_persona 
                WHERE u.nombre_usuario = ? AND u.activo = 1
            ");
            $stmt->execute([$nombre_usuario]);
            $row = $stmt->fetch();

            // 2.3. Verificación de credenciales y creación de Sesión
            if ($row && password_verify($contrasena, $row['contrasena'])) {
                
                // Regenerar el ID de sesión para prevenir ataques de fijación de sesión
                session_regenerate_id(true);

                // Validación de seguridad adicional: Nombres y Apellidos de la BD (Sin números)
                if (preg_match('/[0-9]/', $row['nombres']) || preg_match('/[0-9]/', $row['apellidos'])) {
                    $login_error = "Error de validación en los datos del sistema. Contacte a soporte.";
                    session_destroy();
                } else {
                    // Creación de variables de Sesión
                    $_SESSION['user_id']    = $row['id_usuario'];
                    $_SESSION['id_persona'] = $row['id_persona']; 
                    $_SESSION['nombre']     = trim($row['nombres'] . ' ' . $row['apellidos']);
                    $_SESSION['rol']        = getUserRole($db_connection, $row['id_persona']); 
                    $_SESSION['logged_in']  = true;

                    // Redirección basada en el rol (PHP 8+ match)
                    $redirect = match ($_SESSION['rol']) {
                        'admin'     => './administrador/panel.php',
                        'medico'    => './medico/panel.php',
                        'enfermera' => './enfermera/panel.php',
                        'paciente'  => './paciente/panel.php',
                        default     => './index.php'
                    };
                    
                    header("Location: $redirect");
                    exit;
                }

            } else {
                $login_error = "Usuario o contraseña incorrectos.";

            }
        } catch (PDOException $e) {
            $login_error = "Error del sistema. Intente más tarde.";
            error_log("Login PDO Error: " . $e->getMessage());
        }
    }
}
?>

<?php 
// ===============================================
// 3. SECCIÓN DE PRESENTACIÓN (HTML)
// ===============================================
require_once './includes/cabecera.php'; 
?>

<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-indigo-100 via-cyan-50 to-blue-100 font-sans p-4">
    
    <div class="bg-white bg-opacity-95 backdrop-blur-sm p-10 rounded-2xl shadow-2xl border border-gray-100 w-full max-w-md text-center transform transition duration-500 hover:shadow-blue-300/60">
        
        <div class="mb-8">
            <span class="text-5xl block mb-2 text-cyan-600"></span> <h2 class="text-3xl font-extrabold text-gray-800 tracking-tight">Por favor ingrese los siguientes datos</h2>
        </div>

        <?php if ($login_error): ?>
            <div class="bg-red-50 border border-red-300 text-red-700 p-3 rounded-lg mb-6 text-sm font-medium">
                <?php echo htmlspecialchars($login_error); ?>
            </div>
        <?php endif; ?>

      <form method="POST" class="space-y-6 text-left">
            
            <div>
                <label for="nombre_usuario" class="block text-gray-700 font-medium mb-1">Nombre de Usuario</label>
                <input type="text" name="nombre_usuario" id="nombre_usuario" required maxlength="15"
                    class="w-full border border-gray-300 p-3 rounded-lg shadow-sm placeholder-gray-400 
                            focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 
                            transition duration-300 ease-in-out text-gray-900" 
                    placeholder="Máx. 15 caracteres, sin números"
                    value="<?php echo htmlspecialchars($nombre_usuario); ?>"
                >
            </div>
            
            <div class="relative">
                <label for="contrasena" class="block text-gray-700 font-medium mb-1">Contraseña</label>
                <input type="password" name="contrasena" id="contrasena" required maxlength="15"
                    class="w-full border border-gray-300 p-3 rounded-lg shadow-sm placeholder-gray-400 
                            focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 
                            transition duration-300 ease-in-out text-gray-900"
                    placeholder="Máx. 15 caracteres"
                >
                <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 top-6 flex items-center pr-3 text-gray-400 hover:text-cyan-600 transition duration-150 focus:outline-none">
                    ???
                </button>
            </div>
            
            <button type="submit"
                class="w-full bg-gradient-to-r from-teal-500 to-cyan-600 text-white font-bold text-lg p-3 rounded-lg shadow-md hover:shadow-lg hover:scale-[1.01] transform transition duration-300 ease-in-out focus:ring-4 focus:ring-cyan-300 focus:ring-offset-2">
                Acceder al Sistema
            </button>
        </form>
        
        <p class="mt-8 text-gray-600 text-sm">
            ¿Aún no eres paciente?
            <a href="./registrarse.php" class="text-cyan-700 font-bold hover:underline hover:text-cyan-800 transition duration-150">Regístrate aquí</a>
        </p>
    </div>
</div>

<script>
    const togglePassword = document.querySelector('#togglePassword');
    const password = document.querySelector('#contrasena');

    togglePassword.addEventListener('click', function (e) {
        // Alternar el tipo de input entre 'password' y 'text'
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        
        // Opcional: Cambiar el ícono (aquí usamos emojis simples)
        this.textContent = type === 'password' ? '???' : '??'; 
    });
</script>

<?php 
require_once './includes/pie_pagina.php'; 
?>