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
    die("<div class='alert-danger text-center mt-10'>Error de sesiÃ³n. <a href='../cerrar_sesion.php' class='text-blue-600 underline'>Volver a iniciar</a></div>");
}

$id_persona = $_SESSION['id_persona'];
$mensaje = '';

$medicos = $db_connection->query("
    SELECT ps.id_medico, p.nombres 
    FROM personal_de_salud ps 
    JOIN persona p ON ps.id_medico = p.id_persona 
    WHERE ps.rol = 'medico'
")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_doctor = (int)$_POST['id_doctor'];
    $fecha_hora = $_POST['fecha_hora'];
    $motivo = trim($_POST['motivo']);

    try {
        $stmt = $db_connection->prepare("
            INSERT INTO cita (id_doctor, id_paciente, fecha_hora_cita, motivo_cita, estado) 
            VALUES (?, ?, ?, ?, 'pendiente')
        ");
        $stmt->execute([$id_doctor, $id_persona, $fecha_hora, $motivo]);
        $mensaje = '<div class="bg-green-100 text-green-700 border border-green-300 p-3 rounded mb-4 text-center font-medium shadow-sm">âœ… Cita solicitada con Ã©xito.</div>';
    } catch (PDOException $e) {
        $mensaje = '<div class="bg-red-100 text-red-700 border border-red-300 p-3 rounded mb-4 text-center font-medium shadow-sm">âš ï¸ Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}
?>
<?php require_once '../includes/cabecera.php'; ?>

<body class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-100 via-green-50 to-emerald-100 font-sans text-gray-900">

    <div class="w-full max-w-5xl bg-white/90 backdrop-blur-lg shadow-2xl rounded-3xl border border-emerald-200 p-10 mx-4">

        <!-- Encabezado -->
        <div class="text-center mb-10">
            <h2 class="text-4xl font-bold text-emerald-800 mb-2">Solicitar Cita MÃ©dica</h2>
            <p class="text-gray-700">Bienvenido, <strong class="text-emerald-900"><?php echo htmlspecialchars($_SESSION['nombre']); ?></strong></p>
            <a href="panel.php" class="text-emerald-700 hover:underline font-medium text-sm"> Volver al Panel</a>
        </div>

        <!-- Mensaje del sistema -->
        <?php echo $mensaje; ?>

        <!-- Formulario -->
        <form method="POST" class="space-y-6">

            <!-- MÃ©dico -->
            <div>
                <label class="block text-gray-800 font-semibold mb-2"> Medico</label>
                <select name="id_doctor" required 
                    class="w-full border border-gray-300 p-3 rounded-lg focus:ring-2 focus:ring-emerald-400 focus:outline-none text-gray-900 bg-white placeholder-gray-400">
                    <option value="">Seleccionar medico...</option>
                    <?php foreach ($medicos as $m): ?>
                        <option value="<?php echo $m['id_medico']; ?>">Dr. <?php echo htmlspecialchars($m['nombres']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Fecha y hora -->
            <div>
                <label class="block text-gray-800 font-semibold mb-2"> Fecha y Hora</label>
                <input type="datetime-local" name="fecha_hora" required
                    class="w-full border border-gray-300 p-3 rounded-lg focus:ring-2 focus:ring-emerald-400 focus:outline-none text-gray-900 placeholder-gray-400 bg-white">
            </div>

            <!-- Motivo -->
            <div>
                <label class="block text-gray-800 font-semibold mb-2"> Motivo de la cita</label>
                <textarea name="motivo" required
                    class="w-full border border-gray-300 p-3 rounded-lg h-28 focus:ring-2 focus:ring-emerald-400 focus:outline-none text-gray-900 placeholder-gray-400 bg-white"
                    placeholder="Describe brevemente el motivo de tu consulta..."></textarea>
            </div>

            <!-- BotÃ³n principal -->
            <button type="submit"
                class="w-full bg-gradient-to-r from-emerald-500 to-green-600 text-white font-semibold py-3 rounded-lg hover:scale-105 transition transform duration-300 shadow-lg">
                 Solicitar Cita
            </button>
        </form>

        <!-- SecciÃ³n inferior con accesos -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mt-12 text-center">

            <!-- Historial mÃ©dico -->
            <a href="ver_historial_medico.php"
                class="bg-white border border-emerald-200 rounded-2xl p-6 shadow-md hover:shadow-xl transition transform hover:-translate-y-1">
                <div class="bg-emerald-100 text-emerald-600 w-16 h-16 rounded-full flex items-center justify-center text-3xl mx-auto mb-3">
                    
                </div>
                <h3 class="font-bold text-emerald-800">Historial MÃ©dico</h3>
                <p class="text-gray-600 text-sm mt-2">Consulta tus atenciones anteriores y diagnÃ³sticos.</p>
            </a>

            <!-- Mis datos -->
            <a href="actualizar_datos_personales.php"
                class="bg-white border border-cyan-200 rounded-2xl p-6 shadow-md hover:shadow-xl transition transform hover:-translate-y-1">
                <div class="bg-cyan-100 text-cyan-600 w-16 h-16 rounded-full flex items-center justify-center text-3xl mx-auto mb-3">
                    
                </div>
                <h3 class="font-bold text-cyan-800">Mis Datos</h3>
                <p class="text-gray-600 text-sm mt-2">Actualiza tu informaciÃ³n personal de manera segura.</p>
            </a>

            <!-- Cancelar cita -->
            <a href="cancelar_cita.php"
                class="bg-white border border-red-200 rounded-2xl p-6 shadow-md hover:shadow-xl transition transform hover:-translate-y-1">
                <div class="bg-red-100 text-red-600 w-16 h-16 rounded-full flex items-center justify-center text-3xl mx-auto mb-3">
                    
                </div>
                <h3 class="font-bold text-red-700">Cancelar Cita</h3>
                <p class="text-gray-600 text-sm mt-2">Cancela tus citas programadas si no podrÃ¡s asistir.</p>
            </a>

        </div>

        <!-- Frase motivacional -->
        <div class="mt-12 text-center">
            <p class="text-sm italic text-gray-700">
                ðŸŒ¿ â€œUna cita mÃ©dica a tiempo es una inversiÃ³n en tu bienestar.â€
            </p>
        </div>

    </div>

</body>

<?php require_once '../includes/pie_pagina.php'; ?>

