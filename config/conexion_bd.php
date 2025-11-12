<?php
$host = 'localhost';
$dbname = 'sistema_medico1';
$username = 'root';
$password = '';

try {
    $db_connection = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $db_connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db_connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>