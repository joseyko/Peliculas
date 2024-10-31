<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Generar un token CSRF si no existe en la sesión
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Genera un token CSRF aleatorio
}

// Redirigir si el usuario ya ha iniciado sesión
if (isset($_SESSION['usuario_id'])) {
    header("Location: ../peliculas/peliculas.php");
    exit();
}

$errors = []; // Inicializa el array de errores

// Procesar el formulario de inicio de sesión
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include '../includes/conexion.php'; // Incluir conexión a la base de datos

    if (!$conn) {
        die("Error: No se pudo conectar a la base de datos.");
    }

    // Verificar el token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errors[] = "Error de validación. Por favor, intenta de nuevo.";
    } else {
        // Filtrar y sanitizar datos de entrada
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $contrasena = htmlspecialchars($_POST['password'], ENT_QUOTES, 'UTF-8');

        // Validar datos
        if (empty($email) || empty($contrasena)) {
            $errors[] = "Por favor, complete todos los campos.";
        } else {
            // Preparar y ejecutar la consulta
            $stmt = $conn->prepare("SELECT * FROM usuarios WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $resultado = $stmt->get_result();

            if ($resultado->num_rows > 0) {
                $usuario = $resultado->fetch_assoc();

                // Verificar la contraseña
                if (password_verify($contrasena, $usuario['contrasena'])) {
                    // Guardar información en la sesión
                    $_SESSION['usuario_id'] = $usuario['id'];
                    $_SESSION['nombre_usuario'] = $usuario['nombre_usuario'];

                    // Redirigir a la página de películas
                    header("Location: ../peliculas/peliculas.php");
                    exit();
                } else {
                    $errors[] = "La contraseña es incorrecta.";
                }
            } else {
                $errors[] = "No se encontró una cuenta asociada con ese correo.";
            }

            $stmt->close();
        }
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="..\assets\css\login2.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/91a731da61.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.1/font/bootstrap-icons.css">
    <title>Miskatonic</title>
</head>
<body>
    <div class="container">
        <header>Iniciar sesión</header>
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <h3>Ha ocurrido un error</h3>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <div class="form-outer">
            <form method="POST" action="login2.php">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <div class="page slide-page">
                    <div class="field">
                        <div class="label">Email</div>
                        <input id="email" type="email" class="form-control" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" autocomplete="email" required>
                    </div>
                    <div class="field">
                        <div class="label">Password</div>
                        <input type="password" id="password" class="form-control" name="password" autocomplete="current-password" required>
                    </div>
                    <a href="recuperar_contraseña.php">¿Olvidaste tu contraseña?</a>
                    <div class="field btns">
                        <button class="back" type="button" onclick="window.location.href='../prueba.php'">Volver</button>
                        <button class="submit" type="submit">Iniciar sesión</button>
                    </div>
                </div>
            </form>
        </div>
        <div class="credit"><a href="registro2.php">¿No tienes una cuenta?</a></div>
    </div>
    <script src="assets/js/login2.js"></script>
</body>
</html>
