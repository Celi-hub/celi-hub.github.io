<?php
session_start();

// Si el usuario no ha iniciado sesión, redirigir al login
if (!isset($_SESSION['usuario'])) {
    header('Location: index.php');
    exit;
}

// Limpiamos el nombre de usuario de la sesión de forma más robusta,
// eliminando caracteres no alfanuméricos y convirtiendo a mayúsculas.
$jugador_actual = mb_strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', trim($_SESSION['usuario'])));
$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'];

// Determinar la vista actual: 'partidos', 'clasificacion' o 'admin_all_partidos'
$vista_actual = 'partidos'; // Vista por defecto
if (isset($_GET['view'])) {
    if ($_GET['view'] === 'clasificacion') {
        $vista_actual = 'clasificacion';
    } elseif ($is_admin && $_GET['view'] === 'all') {
        $vista_actual = 'admin_all_partidos';
    }
}

$partidos_jugador = [];
$csv_file_partidos = 'Partidos.xlsx - Partidos.csv';
$csv_file_jugadores = 'Partidos.xlsx - Jugadores.csv';

// Inicializar un array para las estadísticas de los jugadores
$jugadores_stats = [];

// === LÓGICA DE CÁLCULO DE PUNTOS ===
// Se lee el archivo de jugadores para inicializar las estadísticas de todos.
if (file_exists($csv_file_jugadores) && is_readable($csv_file_jugadores)) {
    if (($handle_jugadores = fopen($csv_file_jugadores, "r")) !== FALSE) {
        // Saltar las dos primeras líneas de encabezado del archivo de jugadores
        fgetcsv($handle_jugadores, 1000, ";");
        fgetcsv($handle_jugadores, 1000, ";");
        while (($data_jugador = fgetcsv($handle_jugadores, 1000, ";")) !== FALSE) {
            $nombre_jugador = trim($data_jugador[1]);
            if (!empty($nombre_jugador) && mb_strtoupper($nombre_jugador) !== 'XXXX') {
                $jugadores_stats[$nombre_jugador] = [
                    'partidos_ganados' => 0,
                    'partidos_perdidos' => 0,
                    'puntos' => 0
                ];
            }
        }
        fclose($handle_jugadores);
    }
}

// Se lee el archivo de partidos para calcular los puntos.
if (file_exists($csv_file_partidos) && is_readable($csv_file_partidos)) {
    if (($handle_partidos = fopen($csv_file_partidos, "r")) !== FALSE) {
        // Leer y saltar la primera línea de encabezado.
        fgetcsv($handle_partidos, 1000, ";");
        $file_content = [];
        while (($data = fgetcsv($handle_partidos, 1000, ";")) !== FALSE) {
            $file_content[] = $data;
        }
        fclose($handle_partidos);

        foreach ($file_content as $data) {
            if (count($data) >= 8 && !empty($data[7])) {
                $resultado = explode('-', $data[7]);
                $score_p1 = (int)trim($resultado[0]);
                $score_p2 = (int)trim($resultado[1]);

                $jugador1_p1 = trim($data[2]);
                $jugador2_p1 = trim($data[3]);
                $jugador1_p2 = trim($data[5]);
                $jugador2_p2 = trim($data[6]);

                if ($score_p1 > $score_p2) { // Gana la pareja 1
                    if (isset($jugadores_stats[$jugador1_p1])) {
                        $jugadores_stats[$jugador1_p1]['partidos_ganados']++;
                        $jugadores_stats[$jugador1_p1]['puntos'] += 3;
                    }
                    if (isset($jugadores_stats[$jugador2_p1])) {
                        $jugadores_stats[$jugador2_p1]['partidos_ganados']++;
                        $jugadores_stats[$jugador2_p1]['puntos'] += 3;
                    }
                    if (isset($jugadores_stats[$jugador1_p2])) {
                        $jugadores_stats[$jugador1_p2]['partidos_perdidos']++;
                    }
                    if (isset($jugadores_stats[$jugador2_p2])) {
                        $jugadores_stats[$jugador2_p2]['partidos_perdidos']++;
                    }
                } elseif ($score_p2 > $score_p1) { // Gana la pareja 2
                    if (isset($jugadores_stats[$jugador1_p2])) {
                        $jugadores_stats[$jugador1_p2]['partidos_ganados']++;
                        $jugadores_stats[$jugador1_p2]['puntos'] += 3;
                    }
                    if (isset($jugadores_stats[$jugador2_p2])) {
                        $jugadores_stats[$jugador2_p2]['partidos_ganados']++;
                        $jugadores_stats[$jugador2_p2]['puntos'] += 3;
                    }
                    if (isset($jugadores_stats[$jugador1_p1])) {
                        $jugadores_stats[$jugador1_p1]['partidos_perdidos']++;
                    }
                    if (isset($jugadores_stats[$jugador2_p1])) {
                        $jugadores_stats[$jugador2_p1]['partidos_perdidos']++;
                    }
                }
            }
        }

        // Ordenar la tabla de estadísticas por puntos de forma descendente
        uasort($jugadores_stats, function($a, $b) {
            return $b['puntos'] <=> $a['puntos'];
        });

        // Iterar de nuevo para obtener los partidos del jugador actual o todos los partidos
        foreach ($file_content as $data) {
            // Si la fila no tiene al menos 7 columnas (para los nombres de los jugadores), la ignoramos.
            if (count($data) < 7) {
                continue;
            }
            
            // Limpiamos los nombres de los jugadores de la fila para la comparación robusta
            $jugadores_partido_limpios = array_map(function($name) {
                return mb_strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', trim($name)));
            }, [$data[2], $data[3], $data[5], $data[6]]);

            // Si la vista actual es de todos los partidos del admin, o si el jugador actual está en el partido,
            // agregamos el partido a la lista.
            if ($vista_actual === 'admin_all_partidos' || in_array($jugador_actual, $jugadores_partido_limpios)) {
                $partidos_jugador[] = [
                    'partido' => $data[0],
                    'jugador_1_pareja_1' => $data[2],
                    'jugador_2_pareja_1' => $data[3],
                    'jugador_1_pareja_2' => $data[5],
                    'jugador_2_pareja_2' => $data[6],
                    'resultado' => $data[7] ?? ''
                ];
            }
        }
    }
} else {
    // Si el archivo no se encuentra o no se puede leer, no se mostrarán partidos
    $partidos_jugador = [];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Torneo de Pádel - Partidos</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap');
        body {
            font-family: 'Inter', sans-serif;
        }
        .text-shadow {
            text-shadow: 1px 1px 2px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen p-4">
    <div class="container mx-auto p-8 rounded-lg shadow-2xl bg-white">
        <h1 class="text-4xl font-bold text-center text-indigo-700 mb-2 text-shadow">Torneo de Pádel</h1>

        <!-- Contenido principal -->
        <?php if ($vista_actual === 'clasificacion'): ?>
            <!-- Tabla de clasificación -->
            <h2 class="text-2xl font-bold text-center text-slate-800 my-6">Clasificación de Jugadores</h2>
            <?php if (count($jugadores_stats) > 0): ?>
                <div class="flex justify-center mb-8">
                    <div class="overflow-x-auto rounded-lg shadow-md inline-block">
                        <table class="divide-y divide-gray-200">
                            <thead class="bg-slate-200">
                                <tr>
                                    <th class="px-2 py-1 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">Jugador</th>
                                    <th class="px-2 py-1 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">G</th>
                                    <th class="px-2 py-1 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">P</th>
                                    <th class="px-2 py-1 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">Pts</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($jugadores_stats as $nombre => $stats): ?>
                                    <tr class="<?php echo (mb_strtoupper($nombre) === $jugador_actual) ? 'bg-indigo-100' : 'hover:bg-gray-50'; ?>">
                                        <td class="px-2 py-1 whitespace-nowrap text-sm font-medium text-slate-900"><?php echo htmlspecialchars($nombre); ?></td>
                                        <td class="px-2 py-1 whitespace-nowrap text-sm text-slate-600"><?php echo $stats['partidos_ganados']; ?></td>
                                        <td class="px-2 py-1 whitespace-nowrap text-sm text-slate-600"><?php echo $stats['partidos_perdidos']; ?></td>
                                        <td class="px-2 py-1 whitespace-nowrap text-sm text-slate-900 font-bold"><?php echo $stats['puntos']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php else: ?>
                <p class="text-center text-slate-600 mb-8">No hay jugadores para mostrar la clasificación.</p>
            <?php endif; ?>

        <?php else: ?>
            <!-- Lista de partidos (Vista de partidos) -->
            <?php if ($vista_actual === 'admin_all_partidos'): ?>
                <h2 class="text-2xl font-bold text-center text-slate-800 mb-6">Todos los partidos del torneo</h2>
            <?php else: ?>
                <h2 class="text-2xl font-bold text-center text-slate-800 mb-6">Partidos de <?php echo htmlspecialchars($_SESSION['usuario']); ?></h2>
                <p class="text-center text-slate-600 mb-8">Aquí puedes ver los partidos que te corresponden jugar.</p>
            <?php endif; ?>

            <?php if (count($partidos_jugador) > 0): ?>
                <div class="overflow-x-auto rounded-lg shadow-md">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-slate-200">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">Partido</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">Pareja 1</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">vs</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">Pareja 2</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">Resultado</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($partidos_jugador as $partido): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900"><?php echo htmlspecialchars($partido['partido']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                        <?php echo htmlspecialchars($partido['jugador_1_pareja_1'] . ' y ' . $partido['jugador_2_pareja_1']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800 font-bold">vs</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                        <?php echo htmlspecialchars($partido['jugador_1_pareja_2'] . ' y ' . $partido['jugador_2_pareja_2']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">
                                        <?php if (empty($partido['resultado']) || $is_admin): ?>
                                            <form action="guardar_resultado.php" method="POST" class="flex items-center space-x-2">
                                                <input type="hidden" name="partido_id" value="<?php echo htmlspecialchars($partido['partido']); ?>">
                                                <select name="resultado" class="border border-slate-300 rounded-md py-1 px-2 text-sm focus:outline-none focus:border-indigo-500">
                                                    <option value="">Selecciona</option>
                                                    <?php
                                                    $scores = ["6-0", "6-1", "6-2", "6-3", "6-4", "6-5", "1-6", "2-6", "3-6", "4-6", "5-6"];
                                                    foreach ($scores as $score) {
                                                        $selected = ($partido['resultado'] === $score) ? 'selected' : '';
                                                        echo "<option value=\"$score\" $selected>$score</option>";
                                                    }
                                                    ?>
                                                </select>
                                                <button type="submit" class="bg-indigo-500 hover:bg-indigo-600 text-white text-xs font-bold py-1 px-2 rounded-md transition duration-300">
                                                    Guardar
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span class="font-semibold text-slate-900"><?php echo htmlspecialchars($partido['resultado']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-center text-slate-600">No tienes partidos asignados en este momento. Por favor, verifica el archivo CSV.</p>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Botones de navegación -->
        <div class="mt-8 flex flex-col sm:flex-row justify-center items-center space-y-4 sm:space-y-0 sm:space-x-4">
            <?php if ($vista_actual === 'clasificacion'): ?>
                <!-- Botón para volver a los partidos -->
                <a href="partidos.php" class="inline-block bg-indigo-500 hover:bg-indigo-600 text-white font-bold py-2 px-4 rounded-md transition duration-300">
                    Volver a mis partidos
                </a>
            <?php else: ?>
                <!-- Botón para ver la clasificación -->
                <a href="partidos.php?view=clasificacion" class="inline-block bg-indigo-500 hover:bg-indigo-600 text-white font-bold py-2 px-4 rounded-md transition duration-300">
                    Ver Clasificación
                </a>
                <?php if ($is_admin): ?>
                    <?php if ($vista_actual === 'admin_all_partidos'): ?>
                        <!-- Botón para ver mis partidos (solo admin) -->
                        <a href="partidos.php" class="inline-block bg-indigo-500 hover:bg-indigo-600 text-white font-bold py-2 px-4 rounded-md transition duration-300">
                            Ver mis partidos
                        </a>
                    <?php else: ?>
                        <!-- Botón para ver todos los partidos (solo admin) -->
                        <a href="partidos.php?view=all" class="inline-block bg-indigo-500 hover:bg-indigo-600 text-white font-bold py-2 px-4 rounded-md transition duration-300">
                            Ver todos los partidos
                        </a>
                    <?php endif; ?>
                <?php endif; ?>
            <?php endif; ?>
            <a href="logout.php" class="inline-block bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-md transition duration-300">
                Cerrar Sesión
            </a>
        </div>
    </div>
</body>
</html>






