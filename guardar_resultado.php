<?php
session_start();

// Solo el administrador puede guardar resultados.
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    // Si no es admin, redirigir al listado de partidos.
    header('Location: partidos.php');
    exit;
}

// Verificar que se hayan enviado los datos del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['partido_id'], $_POST['resultado'])) {
    $partido_id_a_buscar = $_POST['partido_id'];
    $nuevo_resultado = $_POST['resultado'];
    $csv_file = 'Partidos.xlsx - Partidos.csv';
    $delimiter = ';';

    // Comprobar si el archivo existe y es legible/escribible
    if (file_exists($csv_file) && is_readable($csv_file) && is_writable($csv_file)) {
        $lines = file($csv_file, FILE_IGNORE_NEW_LINES);
        $file_content = [];

        // Leer todas las líneas del archivo en un array
        foreach ($lines as $line) {
            $file_content[] = str_getcsv($line, $delimiter);
        }

        // Buscar y actualizar la línea del partido
        $line_updated = false;
        foreach ($file_content as $index => $data) {
            // Asegurarse de que la línea tiene el formato correcto
            if (count($data) > 0 && trim($data[0]) === $partido_id_a_buscar) {
                // El resultado es la columna 7 (índice 7).
                // Asegurarse de que el array tenga suficientes elementos antes de actualizar.
                if (count($data) < 8) {
                    $data[7] = ''; // Inicializar la columna si no existe
                }
                $data[7] = $nuevo_resultado;
                $file_content[$index] = $data;
                $line_updated = true;
                break;
            }
        }

        // Si se encontró y actualizó la línea, guardar los cambios en el archivo.
        if ($line_updated) {
            $fp = fopen($csv_file, 'w');
            foreach ($file_content as $line) {
                fputcsv($fp, $line, $delimiter);
            }
            fclose($fp);
        }
    }
}

// Redirigir de vuelta a la página de partidos.
header('Location: partidos.php?view=all');
exit;