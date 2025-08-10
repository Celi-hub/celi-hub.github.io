<?php
$conexion = new mysqli("localhost", "root", "", "miweb");

$id = $_POST['id'];
$nombre = $_POST['nombre'];
$apellidos = $_POST['apellidos'];
$dni = $_POST['dni'];
$fnacim = $_POST['fnacim'];
$telef = $_POST['telef'];
$email = $_POST['email'];

$sql = "UPDATE usuarios SET 
          nombre='$nombre', 
          apellidos='$apellidos', 
          dni='$dni', 
          fnacim='$fnacim', 
          telef='$telef', 
          email='$email' 
        WHERE id=$id";

if ($conexion->query($sql) === TRUE) {
    echo "<p>✅ Cambios guardados correctamente.</p>";
} else {
    echo "❌ Error al actualizar: " . $conexion->error;
}

echo "<br><a href='listar.php'>Volver al listado</a>";
?>
