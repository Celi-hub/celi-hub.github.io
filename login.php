<?php
session_start();

// Comprobar si los datos del formulario fueron enviados
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['usuario'], $_POST['password'])) {
    header('Location: index.php?error=1');
    exit;
}

$csv_file = 'Partidos.xlsx - Jugadores.csv';
$usuario_enviado = trim($_POST['usuario']);
$password_enviada = trim($_POST['password']);
$login_exitoso = false;
$delimiter = ';'; // Se establece el delimitador como punto y coma (;)

// Contraseña especial para el administrador. Se valida sin distinguir entre mayúsculas y minúsculas.
$admin_special_password = '1532';

// Comprobar si el archivo de jugadores existe y es legible
if (file_exists($csv_file) && is_readable($csv_file)) {
    if (($handle = fopen($csv_file, "r")) !== FALSE) {
        // Leer y saltar las dos primeras líneas de encabezado.
        fgetcsv($handle, 1000, $delimiter);
        fgetcsv($handle, 1000, $delimiter);

        while (($data = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
            // Se comprueba si la línea leída es válida y contiene al menos 2 columnas.
            if (count($data) < 2) {
                continue;
            }
            
            // El nombre del jugador está en la segunda columna (índice 1)
            $nombre_jugador_csv = trim($data[1]);
            
            // Comprobar si el nombre no está vacío
            if (empty($nombre_jugador_csv)) {
                 continue;
            }
            
            // Generar la contraseña esperada según la regla: nombre + primera letra + última letra.
            $primera_letra = mb_substr($nombre_jugador_csv, 0, 1);
            $ultima_letra = mb_substr($nombre_jugador_csv, -1);
            $password_esperada = $nombre_jugador_csv . $primera_letra . $ultima_letra;

            // Lógica de validación
            // Primero, verificar si el nombre de usuario coincide.
            if (mb_strtoupper($usuario_enviado) === mb_strtoupper($nombre_jugador_csv)) {
                // Si el usuario es el administrador "CELI", solo se permite la contraseña especial.
                if (mb_strtoupper($nombre_jugador_csv) === 'CELI') {
                    if (mb_strtoupper($password_enviada) === $admin_special_password) {
                        $_SESSION['usuario'] = $nombre_jugador_csv;
                        $_SESSION['is_admin'] = true;
                        $login_exitoso = true;
                        break;
                    }
                } else {
                    // Para los demás usuarios, se usa la contraseña generada.
                    if (mb_strtoupper($password_enviada) === mb_strtoupper($password_esperada)) {
                        $_SESSION['usuario'] = $nombre_jugador_csv;
                        $_SESSION['is_admin'] = false;
                        $login_exitoso = true;
                        break;
                    }
                }
            }
        }
        fclose($handle);
    }
}

if ($login_exitoso) {
    header('Location: partidos.php');
} else {
    // Si el login falla, redirigir al index con un mensaje de error
    header('Location: index.php?error=1');
}
exit;

