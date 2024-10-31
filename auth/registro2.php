<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="../assets/css/login2.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/91a731da61.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.1/font/bootstrap-icons.css">
    <title>CRTIX - Crear Cuenta</title>
</head>
<body>
    <div class="container">
        <header>Crear cuenta</header>
        
        <?php
        // Inicialización de variables
        $nombre = $apellidos = $email = $nombre_usuario = $contrasena = '';
        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            include '../includes/conexion.php'; // Incluir la conexión

            // Recoger y validar datos del formulario
            $nombre = trim($_POST['nombre']);
            $apellidos = trim($_POST['apellidos']);
            $email = trim($_POST['email']);
            $nombre_usuario = trim($_POST['nombre_usuario']);
            $contrasena = password_hash($_POST['contrasena'], PASSWORD_DEFAULT); // Encriptar contraseña

            // Verificación de campos obligatorios
            if (empty($nombre) || empty($apellidos) || empty($email) || empty($nombre_usuario) || empty($contrasena)) {
                $errors[] = "Todos los campos son obligatorios.";
            }

            if (empty($errors)) {
                // Verificar si el nombre de usuario o el correo ya existen
                $sql_check = "SELECT * FROM usuarios WHERE nombre_usuario = '$nombre_usuario' OR email = '$email'";
                $result = $conn->query($sql_check);

                if ($result->num_rows > 0) {
                    $errors[] = "El nombre de usuario o el correo electrónico ya están registrados.";
                } else {
                    // Insertar el nuevo usuario
                    $sql = "INSERT INTO usuarios (nombre, apellidos, nombre_usuario, email, contrasena) 
                            VALUES ('$nombre', '$apellidos', '$nombre_usuario', '$email', '$contrasena')";

                    if ($conn->query($sql) === TRUE) {
                        // Redirigir solo después de un registro exitoso
                        header("Location: ../peliculas/peliculas.php");
                        exit();
                    } else {
                        $errors[] = "Error al registrar: " . $conn->error;
                    }
                }
                $conn->close();
            }
        }
        ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <h3>Error al crear la cuenta</h3>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="progress-bar">
            <div class="step"><p>Usuario</p><div class="bullet"><span>1</span></div><div class="check fas fa-check"></div></div>
            <div class="step"><p>Cuenta</p><div class="bullet"><span>2</span></div><div class="check fas fa-check"></div></div>
            <div class="step"><p>Enviar</p><div class="bullet"><span>3</span></div><div class="check fas fa-check"></div></div>
        </div>

        <div class="form-outer">
            <form method="POST" action="">
                <div class="page slide-page">
                    <div class="field">
                        <div class="label">Nombre</div>
                        <input type="text" name="nombre" value="<?php echo htmlspecialchars($nombre); ?>">
                    </div>
                    <div class="field">
                        <div class="label">Apellidos</div>
                        <input type="text" name="apellidos" value="<?php echo htmlspecialchars($apellidos); ?>">
                    </div>
                    <div class="field">
                        <button class="firstNext next">Siguiente</button>
                    </div>
                </div>

                <div class="page">
                    <div class="field">
                        <div class="label">Correo Electrónico</div>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>">
                    </div>
                    <div class="field btns">
                        <button class="prev-1 prev">Anterior</button>
                        <button class="next-1 next">Siguiente</button>
                    </div>
                </div>

                <div class="page">
                    <div class="field">
                        <div class="label">Nombre de Usuario</div>
                        <input type="text" name="nombre_usuario" value="<?php echo htmlspecialchars($nombre_usuario); ?>">
                    </div>
                    <div class="field">
                        <div class="label">Contraseña</div>
                        <input type="password" name="contrasena">
                    </div>
                    <div class="field btns">
                        <button class="prev-3 prev">Anterior</button>
                        <button type="submit" class="submit">Enviar</button>
                    </div>
                </div>
            </form>
        </div>
        <div class="credit"><a href="login2.php">Ya tengo una cuenta</a></div>
    </div>
    <script src="../assets/js/login2.js"></script>
</body>
</html>
