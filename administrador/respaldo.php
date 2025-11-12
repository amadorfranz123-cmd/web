<?php
session_start();
require_once '../config/conexion_bd.php';
require_once '../includes/autenticacion.php';

redirectIfNotLoggedIn();
if ($_SESSION['rol'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

if (isset($_POST['backup'])) {
    try {
        // Incluir PhpWord
        require_once '../vendor/autoload.php';
        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $phpWord->getCompatibility()->setOoxmlVersion(15);
        
        $section = $phpWord->addSection();
        $section->addTitle('RESPALDO DEL SISTEMA WEB DE CITAS MÉDICAS - QUICHINA', 1);
        $section->addText('Fecha: ' . date('d/m/Y H:i:s'));
        $section->addText('Generado por: ' . $_SESSION['nombre_usuario']);
        $section->addTextBreak(1);

        // 1. Pacientes
        $stmt = $db_connection->query("SELECT p.*, pa.id_paciente FROM persona p JOIN paciente pa ON p.id_persona = pa.id_persona");
        $section->addTitle('PACIENTES', 2);
        $table = $section->addTable('table-style');
        $table->addRow();
        $table->addCell(2000)->addText('ID');
        $table->addCell(3000)->addText('Nombres');
        $table->addCell(3000)->addText('Apellidos');
        $table->addCell(2000)->addText('Teléfono');
        $table->addCell(3000)->addText('Email');
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $table->addRow();
            $table->addCell()->addText($row['id_paciente']);
            $table->addCell()->addText($row['nombres']);
            $table->addCell()->addText($row['apellidos']);
            $table->addCell()->addText($row['telefono']);
            $table->addCell()->addText($row['email']);
        }
        $section->addTextBreak(2);

        // 2. Citas
        $stmt = $db_connection->query("
            SELECT c.*, p.nombres, p.apellidos, per.nombres as medico 
            FROM citas c 
            JOIN paciente pa ON c.id_paciente = pa.id_paciente 
            JOIN persona p ON pa.id_persona = p.id_persona 
            LEFT JOIN personal_de_salud ps ON c.id_personal = ps.id_personal 
            LEFT JOIN persona per ON ps.id_persona = per.id_persona
        ");
        $section->addTitle('CITAS MÉDICAS', 2);
        $table = $section->addTable();
        $table->addRow();
        $table->addCell()->addText('ID Cita');
        $table->addCell()->addText('Paciente');
        $table->addCell()->addText('Médico');
        $table->addCell()->addText('Fecha');
        $table->addCell()->addText('Hora');
        $table->addCell()->addText('Estado');
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $table->addRow();
            $table->addCell()->addText($row['id_cita']);
            $table->addCell()->addText($row['nombres'] . ' ' . $row['apellidos']);
            $table->addCell()->addText($row['medico'] ?? 'Sin asignar');
            $table->addCell()->addText($row['fecha']);
            $table->addCell()->addText($row['hora']);
            $table->addCell()->addText(ucfirst($row['estado']));
        }

        // Guardar archivo
        $filename = 'backup_citas_quichina_' . date('Y-m-d_His') . '.docx';
        $filepath = '../backups/' . $filename;
        if (!is_dir('../backups')) mkdir('../backups', 0777, true);
        
        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($filepath);

        // Descargar
        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        readfile($filepath);
        unlink($filepath); // Eliminar después de descargar
        exit;
    } catch (Exception $e) {
        $error = "Error al generar backup: " . $e->getMessage();
    }
}
?>

<?php require_once '../includes/cabecera.php'; ?>
<div class="max-w-2xl mx-auto p-6">
    <div class="bg-white shadow-lg rounded-xl p-6 mb-6">
        <h2 class="text-2xl font-bold text-emerald-700 mb-4">Copia de Seguridad</h2>
        <p class="text-gray-600 mb-6">Genera un respaldo completo del sistema en formato Word (.docx)</p>
        
        <?php if (isset($error)): ?>
            <div class="bg-red-100 text-red-800 p-4 rounded-lg mb-4"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <button type="submit" name="backup" 
                    class="w-full bg-emerald-600 text-white py-3 rounded-lg font-bold hover:bg-emerald-700 transition">
                Generar Backup en Word (.docx)
            </button>
        </form>

        <div class="mt-6 p-4 bg-gray-50 rounded-lg">
            <p class="text-sm text-gray-600">
                <strong>Incluye:</strong><br>
                • Lista completa de pacientes<br>
                • Historial de citas médicas<br>
                • Fecha y usuario generador<br>
                • Formato editable en Microsoft Word
            </p>
        </div>
    </div>
</div>
<?php require_once '../includes/pie_pagina.php'; ?>