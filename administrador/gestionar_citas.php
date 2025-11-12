<?php
session_start();
require_once '../config/conexion_bd.php';
require_once '../includes/autenticacion.php';
redirectIfNotLoggedIn();

if ($_SESSION['rol'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

try {
    $stmt = $db_connection->prepare("
        SELECT c.id_cita, c.id_paciente, c.id_doctor, c.fecha_hora_cita, c.estado, 
               p.nombres AS paciente, p.apellidos AS paciente_apellidos,
               m.nombres AS medico, m.apellidos AS medico_apellidos
        FROM cita c 
        JOIN paciente pa ON c.id_paciente = pa.id_persona 
        JOIN persona p ON pa.id_persona = p.id_persona 
        JOIN personal_de_salud ps ON c.id_doctor = ps.id_medico 
        JOIN persona m ON ps.id_medico = m.id_persona 
        ORDER BY c.fecha_hora_cita DESC
    ");
    $stmt->execute();
    $citas = $stmt->fetchAll();
} catch (PDOException $e) {
    die("<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative' role='alert'>
             <strong class='font-bold'>Error!</strong>
             <span class='block sm:inline'>Error al cargar las citas: " . htmlspecialchars($e->getMessage()) . "</span>
         </div>");
}

// Obtener lista de pacientes
$pacientes = $db_connection->query("
    SELECT pa.id_persona, CONCAT(p.nombres, ' ', p.apellidos) as nombre 
    FROM paciente pa 
    JOIN persona p ON pa.id_persona = p.id_persona
    ORDER BY p.nombres
")->fetchAll();

// Obtener lista de médicos
$medicos = $db_connection->query("
    SELECT ps.id_medico, CONCAT(p.nombres, ' ', p.apellidos) as nombre 
    FROM personal_de_salud ps 
    JOIN persona p ON ps.id_medico = p.id_persona
    ORDER BY p.nombres
")->fetchAll();

// Procesar edición de cita
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_cita'])) {
    $id_cita = (int)$_POST['id_cita'];
    $id_paciente = (int)$_POST['id_paciente'];
    $id_doctor = (int)$_POST['id_doctor'];
    $fecha_hora = $_POST['fecha_hora'];
    $estado = $_POST['estado'];
    
    try {
        $stmt = $db_connection->prepare("
            UPDATE cita 
            SET id_paciente = ?, id_doctor = ?, fecha_hora_cita = ?, estado = ? 
            WHERE id_cita = ?
        ");
        $stmt->execute([$id_paciente, $id_doctor, $fecha_hora, $estado, $id_cita]);
        // MENSAJE DE ÉXITO CON COLORES SUAVES
        $mensaje = '<div class="bg-green-50 border border-green-300 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                         <strong class="font-bold">¡Éxito!</strong>
                         <span class="block sm:inline">Cita actualizada correctamente.</span>
                         <span class="absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer" onclick="this.parentElement.style.display=\'none\'">
                             <svg class="fill-current h-6 w-6 text-green-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg>
                         </span>
                     </div>';
    } catch (PDOException $e) {
        // MENSAJE DE ERROR
        $mensaje = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                         <strong class="font-bold">Error!</strong>
                         <span class="block sm:inline">Error al actualizar: ' . htmlspecialchars($e->getMessage()) . '</span>
                         <span class="absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer" onclick="this.parentElement.style.display=\'none\'">
                             <svg class="fill-current h-6 w-6 text-red-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg>
                         </span>
                     </div>';
    }
}
?>
<?php require_once '../includes/cabecera.php'; ?>

<div class="min-h-screen bg-gradient-to-br from-blue-50 to-cyan-50 py-8">
    <div class="max-w-7xl mx-auto px-4">
        <div class="bg-gradient-to-r from-blue-100 to-cyan-200 rounded-xl shadow-lg p-6 mb-8 text-gray-800">
            <div class="flex flex-col md:flex-row items-center justify-between">
                <div class="flex items-center">
                    <img src="https://z-cdn-media.chatglm.cn/files/d311805a-20de-4b21-97bc-eef0e1278945_pasted_image_1761817530851.png?auth_key=1793354103-ec253e8b519f4245967b77afc3c2e616-0-fb19dde91b64e2a4550142cc74bfa3f5" 
                             alt="Administrador" 
                             class="w-20 h-20 rounded-full border-3 border-white shadow-lg mr-4 border-gray-100">
                    <div>
                        <h2 class="text-3xl font-bold mb-1">Gestionar Citas</h2>
                        <p class="text-gray-600">Hola, <strong><?php echo htmlspecialchars($_SESSION['nombre']); ?></strong></p>
                        <p class="text-gray-600 text-sm mt-1"><?php echo date('l, j F Y'); ?></p>
                    </div>
                </div>
                <a href="panel.php" class="mt-4 md:mt-0 bg-white bg-opacity-70 hover:bg-opacity-90 px-4 py-2 rounded-lg text-sm transition flex items-center shadow text-gray-800 border border-gray-200">
                    <i class="fas fa-arrow-left mr-2"></i> Volver
                </a>
            </div>
        </div>

        <?php if (isset($mensaje)) echo $mensaje; ?>

        <div class="mb-10 flex flex-col md:flex-row justify-between items-start md:items-center">
            <div>
                <h2 class="text-3xl font-bold text-cyan-800 mb-2">Gestionar Citas</h2>
                <p class="text-gray-600">Total: <strong><?php echo count($citas); ?></strong> citas registradas</p>
            </div>
        </div>

        <?php if (isset($_GET['edit']) && is_numeric($_GET['edit'])): 
            $id_cita = (int)$_GET['edit'];
            $cita_editar = null;
            
            foreach ($citas as $cita) {
                if ($cita['id_cita'] == $id_cita) {
                    $cita_editar = $cita;
                    break;
                }
            }
            
            if ($cita_editar): ?>
        <div class="bg-white rounded-xl shadow-lg p-6 mb-8 border border-cyan-100">
            <h3 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-edit text-cyan-600 mr-2"></i>
                Editar Cita #<?php echo $id_cita; ?>
            </h3>
            
            <form method="POST" class="space-y-4">
                <input type="hidden" name="id_cita" value="<?php echo $id_cita; ?>">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Paciente</label>
                        <select name="id_paciente" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-transparent">
                            <?php foreach ($pacientes as $paciente): ?>
                                <option value="<?php echo $paciente['id_persona']; ?>" 
                                        <?php echo ($cita_editar['id_paciente'] == $paciente['id_persona']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($paciente['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Médico</label>
                        <select name="id_doctor" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-transparent">
                            <?php foreach ($medicos as $medico): ?>
                                <option value="<?php echo $medico['id_medico']; ?>" 
                                        <?php echo ($cita_editar['id_doctor'] == $medico['id_medico']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($medico['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fecha y Hora</label>
                        <input type="datetime-local" 
                               name="fecha_hora" 
                               required 
                               value="<?php echo date('Y-m-d\TH:i', strtotime($cita_editar['fecha_hora_cita'])); ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-transparent">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                        <select name="estado" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-transparent">
                            <option value="pendiente" <?php echo ($cita_editar['estado'] == 'pendiente') ? 'selected' : ''; ?>>Pendiente</option>
                            <option value="atendida" <?php echo ($cita_editar['estado'] == 'atendida') ? 'selected' : ''; ?>>Atendida</option>
                            <option value="cancelada" <?php echo ($cita_editar['estado'] == 'cancelada') ? 'selected' : ''; ?>>Cancelada</option>
                        </select>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3 pt-4">
                    <a href="gestionar_citas.php" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                        Cancelar
                    </a>
                    <button type="submit" class="px-4 py-2 bg-cyan-600 text-white rounded-lg hover:bg-cyan-700 transition shadow-md">
                        Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
            <?php endif; ?>
        <?php endif; ?>

        <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100">
            <div class="p-6">
                <h3 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-calendar-alt text-cyan-600 mr-2"></i>
                    Lista de Citas
                </h3>
                
                <?php if (empty($citas)): ?>
                    <div class="text-center py-12">
                        <i class="fas fa-calendar-times text-gray-300 text-5xl mb-4"></i>
                        <h3 class="text-lg font-medium text-gray-900 mb-1">No hay citas registradas</h3>
                        <p class="text-gray-500">Actualmente no hay citas en el sistema.</p>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Paciente</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Médico</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($citas as $c): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($c['paciente'] . ' ' . $c['paciente_apellidos']); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">Dr. <?php echo htmlspecialchars($c['medico'] . ' ' . $c['medico_apellidos']); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900"><?php echo date('d/m/Y H:i', strtotime($c['fecha_hora_cita'])); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full
                                            <?php echo $c['estado'] === 'atendida' ? 'bg-green-100 text-green-800' : 
                                                         ($c['estado'] === 'cancelada' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800'); ?>">
                                            <?php echo ucfirst($c['estado']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <?php if ($c['estado'] === 'pendiente'): ?>
                                            <a href="gestionar_citas.php?edit=<?php echo $c['id_cita']; ?>" 
                                               class="text-cyan-600 hover:text-cyan-800 mr-3 transition">
                                                <i class="fas fa-edit"></i> Editar
                                            </a>
                                            <a href="eliminar_cita.php?id=<?php echo $c['id_cita']; ?>" 
                                               class="text-red-600 hover:text-red-800 transition"
                                               onclick="return confirm('¿Estás seguro de eliminar esta cita?');">
                                                <i class="fas fa-trash"></i> Eliminar
                                            </a>
                                        <?php else: ?>
                                            <span class="text-gray-500">Sin acción</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/pie_pagina.php'; ?>