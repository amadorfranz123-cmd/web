<?php
session_start();
require_once '../config/conexion_bd.php';
require_once '../includes/autenticacion.php';
redirectIfNotLoggedIn();

if ($_SESSION['rol'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

// === FILTROS ===
$desde = $_GET['desde'] ?? date('Y-m-01');
$hasta = $_GET['hasta'] ?? date('Y-m-d');

// === FUNCIÓN SEGURA PARA CONTAR ===
function contarCitas($db, $sql, $params = []) {
    try {
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    } catch (Exception $e) {
        return 0;
    }
}

// === TOTALES (CORREGIDOS) ===
$total_citas = contarCitas($db_connection, "
    SELECT COUNT(*) FROM cita 
    WHERE DATE(fecha_hora_cita) BETWEEN ? AND ?
", [$desde, $hasta]);

$pendientes = contarCitas($db_connection, "
    SELECT COUNT(*) FROM cita 
    WHERE estado = 'pendiente' AND DATE(fecha_hora_cita) BETWEEN ? AND ?
", [$desde, $hasta]);

$atendidas = contarCitas($db_connection, "
    SELECT COUNT(*) FROM cita 
    WHERE estado = 'atendida' AND DATE(fecha_hora_cita) BETWEEN ? AND ?
", [$desde, $hasta]);

$canceladas = contarCitas($db_connection, "
    SELECT COUNT(*) FROM cita 
    WHERE estado = 'cancelada' AND DATE(fecha_hora_cita) BETWEEN ? AND ?
", [$desde, $hasta]);

// === CITAS POR MÉDICO (CORREGIDO) ===
try {
    $stmt = $db_connection->prepare("
        SELECT p.nombres, COUNT(*) as total 
        FROM cita c 
        JOIN personal_de_salud ps ON c.id_doctor = ps.id_medico 
        JOIN persona p ON ps.id_medico = p.id_persona 
        WHERE DATE(c.fecha_hora_cita) BETWEEN ? AND ?
        GROUP BY c.id_doctor 
        ORDER BY total DESC
    ");
    $stmt->execute([$desde, $hasta]);
    $citas_medico = $stmt->fetchAll();
} catch (Exception $e) {
    $citas_medico = [];
}

// === CITAS POR DÍA (GRÁFICO) ===
try {
    $stmt = $db_connection->prepare("
        SELECT DATE(fecha_hora_cita) as fecha, COUNT(*) as citas 
        FROM cita 
        WHERE DATE(fecha_hora_cita) BETWEEN ? AND ?
        GROUP BY DATE(fecha_hora_cita)
        ORDER BY fecha
    ");
    $stmt->execute([$desde, $hasta]);
    $datos_grafico = $stmt->fetchAll();

    $labels = [];
    $valores = [];
    foreach ($datos_grafico as $d) {
        $labels[] = date('d/m', strtotime($d['fecha']));
        $valores[] = (int)$d['citas'];
    }
} catch (Exception $e) {
    $labels = [];
    $valores = [];
}
?>
<?php require_once '../includes/cabecera.php'; ?>

<div class="max-w-7xl mx-auto">
    <!-- ENCABEZADO -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <h2 class="text-3xl font-bold text-emerald-800">Reportes del Sistema</h2>
            <p class="text-gray-600">Del <strong><?php echo date('d/m/Y', strtotime($desde)); ?></strong> al <strong><?php echo date('d/m/Y', strtotime($hasta)); ?></strong></p>
        </div>
        <div class="flex gap-2">
            <a href="panel.php" class="text-emerald-600 hover:underline text-sm">Volver</a>
            <button onclick="exportarPDF()" class="btn-primary text-sm">Exportar PDF</button>
        </div>
    </div>

    <!-- FILTROS -->
    <div class="card mb-6 p-4">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Desde</label>
                <input type="date" name="desde" value="<?php echo $desde; ?>" required class="input-custom text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Hasta</label>
                <input type="date" name="hasta" value="<?php echo $hasta; ?>" required class="input-custom text-sm">
            </div>
            <div class="flex items-end">
                <button type="submit" class="btn-primary w-full text-sm">Filtrar</button>
            </div>
        </form>
    </div>

    <!-- ESTADÍSTICAS -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="card p-5 text-center bg-gradient-to-br from-emerald-500 to-teal-600 text-white">
            <h3 class="text-3xl font-bold"><?php echo $total_citas; ?></h3>
            <p class="text-sm opacity-90">Total Citas</p>
        </div>
        <div class="card p-5 text-center bg-gradient-to-br from-amber-500 to-orange-600 text-white">
            <h3 class="text-3xl font-bold"><?php echo $pendientes; ?></h3>
            <p class="text-sm opacity-90">Pendientes</p>
        </div>
        <div class="card p-5 text-center bg-gradient-to-br from-blue-500 to-cyan-600 text-white">
            <h3 class="text-3xl font-bold"><?php echo $atendidas; ?></h3>
            <p class="text-sm opacity-90">Atendidas</p>
        </div>
        <div class="card p-5 text-center bg-gradient-to-br from-red-500 to-pink-600 text-white">
            <h3 class="text-3xl font-bold"><?php echo $canceladas; ?></h3>
            <p class="text-sm opacity-90">Canceladas</p>
        </div>
    </div>

    <!-- GRÁFICO -->
    <div class="card mb-6 p-6">
        <h3 class="text-xl font-bold text-emerald-800 mb-4">Citas por Día</h3>
        <?php if (!empty($labels)): ?>
        <canvas id="graficoCitas" height="100"></canvas>
        <?php else: ?>
        <p class="text-center text-gray-500 py-8">No hay citas en este rango de fechas.</p>
        <?php endif; ?>
    </div>

    <!-- TABLA MÉDICOS -->
    <div class="card">
        <h3 class="text-xl font-bold text-emerald-800 mb-4 p-4">Citas por Médico</h3>
        <?php if ($citas_medico): ?>
        <table class="table-custom">
            <thead>
                <tr>
                    <th>Médico</th>
                    <th class="text-center">Total Citas</th>
                    <th class="text-center">Porcentaje</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($citas_medico as $cm): 
                    $porcentaje = $total_citas > 0 ? round(($cm['total'] / $total_citas) * 100, 1) : 0;
                ?>
                <tr>
                    <td class="font-medium"><?php echo htmlspecialchars($cm['nombres']); ?></td>
                    <td class="text-center font-bold"><?php echo $cm['total']; ?></td>
                    <td class="text-center">
                        <div class="flex items-center justify-center">
                            <div class="w-full bg-gray-200 rounded-full h-2 max-w-32 mr-2">
                                <div class="bg-emerald-600 h-2 rounded-full" style="width: <?php echo $porcentaje; ?>%"></div>
                            </div>
                            <span class="text-sm"><?php echo $porcentaje; ?>%</span>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p class="text-center py-8 text-gray-500">No hay médicos con citas en este período.</p>
        <?php endif; ?>
    </div>
</div>

<!-- CHART.JS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<?php if (!empty($labels)): ?>
<script>
new Chart(document.getElementById('graficoCitas'), {
    type: 'line',
    data: {
        labels: <?php echo json_encode($labels); ?>,
        datasets: [{
            label: 'Citas Diarias',
            data: <?php echo json_encode($valores); ?>,
            borderColor: '#10b981',
            backgroundColor: 'rgba(16, 185, 129, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'top' } },
        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
    }
});
</script>
<?php endif; ?>

<!-- PDF -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script>
function exportarPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();
    let y = 20;
    doc.setFontSize(16);
    doc.text('REPORTE DE CITAS', 105, y, { align: 'center' }); y += 10;
    doc.setFontSize(10);
    doc.text(`Período: <?php echo date('d/m/Y', strtotime($desde)); ?> - <?php echo date('d/m/Y', strtotime($hasta)); ?>`, 105, y, { align: 'center' }); y += 20;

    doc.setFontSize(12);
    doc.text('RESUMEN', 20, y); y += 10;
    doc.setFontSize(10);
    doc.text(`• Total: <?php echo $total_citas; ?>`, 25, y); y += 7;
    doc.text(`• Pendientes: <?php echo $pendientes; ?>`, 25, y); y += 7;
    doc.text(`• Atendidas: <?php echo $atendidas; ?>`, 25, y); y += 7;
    doc.text(`• Canceladas: <?php echo $canceladas; ?>`, 25, y); y += 15;

    doc.text('POR MÉDICO', 20, y); y += 10;
    <?php foreach ($citas_medico as $cm): 
        $porc = $total_citas > 0 ? round(($cm['total'] / $total_citas) * 100, 1) : 0;
    ?>
    doc.text(`• Dr. <?php echo addslashes($cm['nombres']); ?>: <?php echo $cm['total']; ?> (<?php echo $porc; ?>%)`, 25, y); y += 7;
    <?php endforeach; ?>

    doc.save('reporte_<?php echo $desde; ?>_<?php echo $hasta; ?>.pdf');
}
</script>

<?php require_once '../includes/pie_pagina.php'; ?>