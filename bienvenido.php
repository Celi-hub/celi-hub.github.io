<?php
session_start();

// Si el usuario no ha iniciado sesión, redirigir al login
if (!isset($_SESSION['usuario'])) {
    header('Location: index.php');
    exit;
}

$usuario = $_SESSION['usuario'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap');
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="bg-slate-100 flex items-center justify-center min-h-screen p-4">
    <div class="bg-white p-8 rounded-lg shadow-2xl text-center">
        <h1 class="text-3xl font-bold text-slate-800 mb-4">¡Bienvenido, <?php echo htmlspecialchars($usuario); ?>!</h1>
        <p class="text-slate-600 mb-6">Has iniciado sesión correctamente. Aquí se mostrará la información de tus partidos.</p>
        
        <!-- Botón para cerrar sesión -->
        <a href="logout.php" class="inline-block bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-md transition duration-300">
            Cerrar Sesión
        </a>
    </div>
</body>
</html>