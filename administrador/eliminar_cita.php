<?php
session_start();
require_once '../config/conexion_bd.php';
require_once '../includes/autenticacion.php';
redirectIfNotLoggedIn();

if ($_SESSION['rol'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    try {
        $stmt = $db_connection->prepare("UPDATE cita SET estado = 'cancelada' WHERE id_cita = ?");
        $stmt->execute([$id]);
    } catch (PDOException $e) {
        // Manejo silencioso, redirección sigue
    }
}

header('Location: gestionar_citas.php');
exit;
?>