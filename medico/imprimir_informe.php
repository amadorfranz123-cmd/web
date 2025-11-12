<?php
session_start();
require_once '../config/conexion_bd.php';
require_once '../includes/autenticacion.php';
redirectIfNotLoggedIn();

if ($_SESSION['rol'] !== 'medico') {
    header('Location: ../index.php');
    exit;
}

// OBTENER id_persona del médico
if (!isset($_SESSION['id_persona'])) {
    die("<div class='alert-danger'>Error de sesión. <a href='../cerrar_sesion.php'>Volver a iniciar</a></div>");
}
$id_medico = $_SESSION['id_persona'];

// --- Lógica de Filtrado Opcional ---
// Permite filtrar por una sola cita (id_cita) o por todas las citas de un paciente (id_paciente).
$where_clause = "c.id_doctor = ? AND c.estado = 'atendida'";
$params = [$id_medico];
$informe_titulo = "Informe de Atenciones Totales";

if (isset($_GET['id_cita']) && is_numeric($_GET['id_cita'])) {
    // Caso 1: Imprimir una única cita
    $id_cita = (int)$_GET['id_cita'];
    $where_clause .= " AND c.id_cita = ?";
    $params[] = $id_cita;
    $informe_titulo = "Detalle de Cita #{$id_cita}";
} elseif (isset($_GET['id_paciente']) && is_numeric($_GET['id_paciente'])) {
    // Caso 2: Imprimir el historial de un paciente específico
    $id_paciente = (int)$_GET['id_paciente'];
    $where_clause .= " AND c.id_paciente = ?";
    $params[] = $id_paciente;
    // Se intenta obtener el nombre del paciente para el título
    $paciente_stmt = $db_connection->prepare("SELECT nombres, apellidos FROM persona WHERE id_persona = ?");
    $paciente_stmt->execute([$id_paciente]);
    $paciente_info = $paciente_stmt->fetch(PDO::FETCH_ASSOC);
    $nombre_paciente = $paciente_info ? htmlspecialchars($paciente_info['nombres'] . ' ' . $paciente_info['apellidos']) : 'Paciente Desconocido';
    $informe_titulo = "Historial Médico de {$nombre_paciente}";
}
// ------------------------------------

try {
    $stmt = $db_connection->prepare("
        SELECT 
            c.id_cita,
            p.nombres || ' ' || p.apellidos AS paciente,
            c.fecha_hora_cita,
            a.diagnostico,
            a.recetas,
            a.fecha_atencion,
            p.id_persona AS id_paciente
        FROM cita c 
        JOIN paciente pa ON c.id_paciente = pa.id_persona
        JOIN persona p ON pa.id_persona = p.id_persona
        LEFT JOIN atencion a ON c.id_atencion = a.id_atencion 
        WHERE {$where_clause}
        ORDER BY c.fecha_hora_cita DESC
    ");
    $stmt->execute($params);
    $informe = $stmt->fetchAll();
} catch (PDOException $e) {
    die("<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative'>Error en la base de datos: " . htmlspecialchars($e->getMessage()) . "</div>");
}
?>
<?php require_once '../includes/cabecera.php'; ?>
<div class="max-w-6xl mx-auto px-4 py-8">
    
    <div class="bg-gradient-to-r from-blue-100 to-cyan-200 rounded-xl shadow-lg p-6 mb-8 text-gray-800">
        <div class="flex flex-col md:flex-row items-center">
            <img src="https://z-cdn-media.chatglm.cn/files/d311805a-20de-4b21-97bc-eef0e1278945_pasted_image_1761817530851.png?auth_key=1793353987-0a8e7b1a5d4149f6b6b2b9f06598bb2e-0-9848d7140f17061551ae7bb41b1798ff" 
                 alt="Doctor" 
                 class="w-20 h-20 rounded-full border-3 border-white shadow-lg mb-4 md:mb-0 md:mr-6 border-gray-300">
            <div class="text-center md:text-left">
                <h2 class="text-3xl font-bold mb-1 text-cyan-800"><?php echo htmlspecialchars($informe_titulo); ?></h2>
                <p class="text-gray-600">Dr. <strong><?php echo htmlspecialchars($_SESSION['nombre']); ?></strong></p>
                <p class="text-gray-500 text-sm">Total de registros: <span class="font-bold"><?php echo count($informe); ?></span></p>
            </div>
            <div class="mt-4 md:mt-0 md:ml-auto flex flex-col md:flex-row gap-3">
                <a href="historial_medico.php" class="bg-white hover:bg-gray-100 text-gray-700 px-4 py-2 rounded-lg font-medium shadow-md transition transform hover:scale-105 border border-gray-300">
                    Volver a Historial
                </a>
                <button onclick="window.print()" class="bg-gradient-to-r from-blue-600 to-indigo-500 text-white px-4 py-2 rounded-lg font-medium shadow-md transition transform hover:scale-105 print:hidden">
                    <i class="fas fa-print inline mr-1"></i>
                    Imprimir Informe
                </button>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100" id="print-area">
        
        <div class="bg-gradient-to-r from-blue-700 to-cyan-600 text-white p-6 text-center print:bg-white print:text-black print:border-b print:border-gray-300 print:shadow-none print:p-4">
            <h1 class="text-3xl font-bold mb-2 print:text-xl print:text-gray-800"><?php echo htmlspecialchars($informe_titulo); ?></h1>
            <div class="flex flex-col md:flex-row justify-center items-center gap-4 mt-4 print:mt-1 print:text-gray-600 print:text-sm">
                <div>
                    <p class="text-lg print:text-base">Dr. <?php echo htmlspecialchars($_SESSION['nombre']); ?></p>
                    <p class="text-sm opacity-80 print:opacity-100">Médico</p>
                </div>
                <div class="hidden md:block print:hidden">|</div>
                <div>
                    <p class="text-lg print:text-base"><?php echo date('d/m/Y H:i'); ?></p>
                    <p class="text-sm opacity-80 print:opacity-100">Fecha de emisión</p>
                </div>
            </div>
        </div>

        <?php if ($informe): ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-100 print:bg-gray-100 print:text-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Paciente</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha Cita</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/3">Diagnóstico</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/3">Receta</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider print:hidden">Acción</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($informe as $i): ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($i['paciente']); ?></div>
                            <div class="text-xs text-gray-500">ID Cita: <?php echo $i['id_cita']; ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900"><?php echo date('d/m/Y H:i', strtotime($i['fecha_hora_cita'])); ?></div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">
                                <?php if ($i['diagnostico']): ?>
                                    <div class="bg-blue-50 p-3 rounded-lg border border-blue-200 print:bg-white print:border-none print:p-0">
                                        <?php echo nl2br(htmlspecialchars($i['diagnostico'])); ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-gray-400 italic">Sin diagnóstico</span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">
                                <?php if ($i['recetas']): ?>
                                    <div class="bg-cyan-50 p-3 rounded-lg border border-cyan-200 print:bg-white print:border-none print:p-0">
                                        <?php echo nl2br(htmlspecialchars($i['recetas'])); ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-gray-400 italic">Sin receta</span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center whitespace-nowrap print:hidden">
                            <a href="?id_cita=<?php echo $i['id_cita']; ?>" class="text-blue-600 hover:text-blue-800 font-medium text-sm border border-blue-200 rounded-lg px-3 py-1 bg-blue-50 hover:bg-blue-100 transition">
                                Imprimir Cita
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="text-center py-16">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-1">No se han encontrado atenciones</h3>
            <p class="text-gray-500">Ajusta los filtros o verifica si hay atenciones registradas.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
@media print {
    /* 1. Ocultar todo lo que no sea el área de impresión */
    body * { visibility: hidden; }
    #print-area, #print-area * { visibility: visible; }
    #print-area { position: absolute; left: 0; top: 0; width: 100%; border: none !important; box-shadow: none !important; }
    
    /* 2. Ocultar botones e ítems no necesarios */
    .print\:hidden, .bg-gradient-to-r.from-blue-100.to-cyan-200 { display: none !important; }
    
    /* 3. Estilo de tabla para impresión */
    .min-w-full { width: 100% !important; table-layout: fixed; }
    .divide-y, .divide-gray-200 { border-color: #e5e7eb !important; }
    
    /* Ajustes tipográficos */
    .text-xs { font-size: 10px !important; }
    .text-sm { font-size: 12px !important; }
    .text-lg { font-size: 14px !important; }

    /* Eliminar fondos y bordes de celdas para limpieza */
    .bg-blue-50, .bg-cyan-50 { background-color: white !important; }
    .border-blue-200, .border-cyan-200 { border: none !important; }
    .rounded-lg { border-radius: 0 !important; }
    .px-6 { padding: 8px !important; }
    .py-4 { padding-top: 6px !important; padding-bottom: 6px !important; }
}
</style>

<?php require_once '../includes/pie_pagina.php'; ?>