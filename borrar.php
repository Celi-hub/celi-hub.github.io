<?php
$conexion = new mysqli("localhost", "root", "", "miweb");

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$id = $_GET['id'];

$sql = "DELETE FROM usuarios WHERE id=$id";

if ($conexion->query($sql) === TRUE) {
    echo "<p>✅ Registro borrado correctamente.</p>";
} else {
    echo "❌ Error al borrar: " . $conexion->error;
}

echo "<br><a href='listar.php'>Volver al listado</a>";
?>
