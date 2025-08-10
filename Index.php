<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Torneo de Pádel - Iniciar Sesión</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap');
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center p-4">
    <div class="container mx-auto p-8 rounded-lg shadow-2xl bg-white max-w-sm">
         <h1 class="text-2xl font-bold text-center text-slate-800 mb-6">Bienvenidos al Torneo</h1>
		<h1 class="text-3xl font-bold text-center text-slate-1000 mb-6">LA LEYENDA</h1>
		<h2 class="text-1xl font-bold text-left text-slate-600 mb-6">Iniciar Sesión</h2>
        
        <?php if (!empty($error_message)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo htmlspecialchars($error_message); ?></span>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST" class="space-y-6">
            <div>
                <label for="usuario" class="block text-sm font-medium text-slate-700">Nombre de Usuario</label>
                <input type="text" id="usuario" name="usuario" required
                       class="mt-1 block w-full px-3 py-2 border border-slate-300 rounded-md shadow-sm placeholder-slate-400
                              focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 sm:text-sm">
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-slate-700">Contraseña</label>
                <input type="password" id="password" name="password" required
                       class="mt-1 block w-full px-3 py-2 border border-slate-300 rounded-md shadow-sm placeholder-slate-400
                              focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 sm:text-sm">
            </div>
            <div class="flex items-center justify-end">
                <button type="submit"
                        class="w-full px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-bold text-white
                               bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2
                               focus:ring-indigo-500 transition duration-300">
                    Entrar
                </button>
            </div>
        </form>
    </div>
</body>
</html>