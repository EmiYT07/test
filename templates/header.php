<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plataforma Educativa</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="js/script.js" defer></script>
</head>
<body>
<header>
    <nav>
        <a href="index.php">Inicio</a>
        <?php if (isAdmin()): ?>
            <a href="admin.php">Administrador</a>
        <?php endif; ?>
        <?php if (isLoggedIn()): ?>
            <a href="logout.php">Cerrar Sesión</a>
        <?php else: ?>
            <a href="login.php">Iniciar Sesión</a>
        <?php endif; ?>
    </nav>
</header>
