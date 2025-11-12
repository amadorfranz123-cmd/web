<?php
function redirectIfNotLoggedIn() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['rol'])) {
        header('Location: ../iniciar_sesion.php');
        exit;
    }
}

function isUserLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['rol']);
}

function getUserRole($db_connection, $id_persona) {
    $stmt = $db_connection->prepare("SELECT rol FROM personal_de_salud WHERE id_medico = ?");
    $stmt->execute([$id_persona]);
    $rol = $stmt->fetchColumn();

    if ($rol) return $rol;

    $stmt = $db_connection->prepare("SELECT 1 FROM paciente WHERE id_persona = ?");
    $stmt->execute([$id_persona]);
    return $stmt->fetchColumn() ? 'paciente' : 'desconocido';
}

// Obtener id_persona desde user_id
function getIdPersonaFromSession($db_connection) {
    if (!isset($_SESSION['user_id'])) return null;
    $stmt = $db_connection->prepare("SELECT id_persona FROM usuario WHERE id_usuario = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetchColumn();
}
?>