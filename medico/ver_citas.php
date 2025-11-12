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
    // Corregido: codificación
    die("<div class='alert-danger text-center mt-10'>Error de sesión. <a href='../cerrar_sesion.php' class='text-blue-600 underline'>Volver a iniciar</a></div>");
}
$id_medico = $_SESSION['id_persona'];

try {
    $stmt = $db_connection->prepare("
        SELECT c.id_cita, p.nombres AS paciente, c.fecha_hora_cita, c.motivo_cita, c.estado 
        FROM cita c 
        JOIN paciente pa ON c.id_paciente = pa.id_persona
        JOIN persona p ON pa.id_persona = p.id_persona
        WHERE c.id_doctor = ?
        ORDER BY c.fecha_hora_cita ASC
    ");
    $stmt->execute([$id_medico]);
    $citas = $stmt->fetchAll();
} catch (PDOException $e) {
    die("<div class='alert-danger'>Error: " . $e->getMessage() . "</div>");
}
?>
<?php require_once '../includes/cabecera.php'; ?>

<div class="max-w-6xl mx-auto py-10">
    <div class="bg-gradient-to-r from-blue-200 to-cyan-300 rounded-xl shadow-lg p-6 mb-8 text-gray-800">
        <div class="flex flex-col md:flex-row justify-between items-center">
            <div class="flex items-center mb-4 md:mb-0">
                <img src="https://z-cdn-media.chatglm.cn/files/d311805a-20de-4b21-97bc-eef0e1278945_pasted_image_1761817530851.png?auth_key=1793353693-604df8800ee04de89f96b0d39fb82713-0-e7c3b31b166a82dd511ee06978a7057a" 
                    alt="Doctor" 
                    class="w-16 h-16 rounded-full border-3 border-white shadow-lg mr-4">
                <div>
                    <h2 class="text-3xl font-bold mb-1">Mis Citas 📅</h2>
                    <p class="text-gray-600">Hola, Dr. <strong><?php echo htmlspecialchars($_SESSION['nombre']); ?></strong></p>
                </div>
            </div>
            <div class="flex items-center">
                <span class="bg-white bg-opacity-40 px-3 py-1 rounded-full text-sm mr-4 text-gray-700 font-medium">
                    <?php echo count($citas); ?> cita(s)
                </span>
                <a href="panel.php" class="bg-white bg-opacity-40 hover:bg-opacity-60 px-4 py-2 rounded-lg text-sm transition text-gray-800">Volver al panel</a>
            </div>
        </div>
    </div>

    <?php if ($citas): ?>
    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Paciente</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha y Hora</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Motivo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acción</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($citas as $c): ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($c['paciente']); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900"><?php echo date('d/m/Y H:i', strtotime($c['fecha_hora_cita'])); ?></div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900"><?php echo htmlspecialchars($c['motivo_cita'] ?: '—'); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full
                                <?php echo $c['estado'] === 'atendida' ? 'bg-green-100 text-green-700' : 
                                             ($c['estado'] === 'cancelada' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700'); ?>">
                                <?php echo ucfirst($c['estado']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php if ($c['estado'] === 'pendiente'): ?>
                                <a href="registrar_diagnostico.php?id=<?php echo $c['id_cita']; ?>" class="text-teal-600 hover:text-teal-800 font-medium transition">Atender</a>
                            <?php else: ?>
                                <span class="text-gray-400">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php else: ?>
    <div class="bg-white rounded-xl shadow-md p-12 text-center">
        <div class="text-gray-400 mb-4">
            <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-1">No tienes citas programadas</h3>
        <p class="text-gray-500">Cuando tengas citas, aparecerán aquí.</p>
    </div>
    <?php endif; ?>
</div>

<div class="max-w-6xl mx-auto mt-8 grid grid-cols-2 md:grid-cols-4 gap-4">
    <?php
    $acciones = [
        // CAMBIO: Colores claros en las acciones rápidas
        ['Expedientes Médicos', 'expedientes_medicos.php', 'bg-gradient-to-r from-sky-300 to-indigo-300', '📄'],
        // Se puede añadir otra acción aquí si es necesario
    ];
    foreach ($acciones as $a): ?>
    <a href="<?php echo $a[1]; ?>" class="group">
        <div class="bg-white rounded-xl shadow-md overflow-hidden transform transition-all duration-300 hover:scale-[1.03] hover:shadow-xl border border-gray-100">
            <div class="p-5 text-center <?php echo $a[2]; ?>">
                <span class="text-3xl block mb-2"><?php echo $a[3]; ?></span>
                <h3 class="font-bold text-sm text-gray-900"><?php echo $a[0]; ?></h3>
            </div>
        </div>
    </a>
    <?php endforeach; ?>
</div>

<?php require_once '../includes/pie_pagina.php'; ?>