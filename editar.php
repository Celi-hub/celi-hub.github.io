<?php
$conexion = new mysqli("localhost", "root", "", "miweb");
$id = $_GET['id'];

$resultado = $conexion->query("SELECT * FROM usuarios WHERE id=$id");
$fila = $resultado->fetch_assoc();
?>

<h1>Editar usuario</h1>
<form method="post" action="actualizar.php">
  <input type="hidden" name="id" value="<?php echo $fila['id']; ?>">

  <label>Nombre:</label>
  <input type="text" name="nombre" value="<?php echo $fila['nombre']; ?>"><br><br>

  <label>Apellidos:</label>
  <input type="text" name="apellidos" value="<?php echo $fila['apellidos']; ?>"><br><br>

  <label>DNI:</label>
  <input type="text" name="dni" value="<?php echo $fila['dni']; ?>"><br><br>

  <label>Fecha Nacimiento:</label>
  <input type="date" name="fnacim" value="<?php echo $fila['fnacim']; ?>"><br><br>

  <label>Teléfono:</label>
  <input type="text" name="telef" value="<?php echo $fila['telef']; ?>"><br><br>

  <label>Email:</label>
  <input type="email" name="email" value="<?php echo $fila['email']; ?>"><br><br>

  <button type="submit">Guardar cambios</button>
</form>
