<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .login-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 20px;
            background-color: #f7f7f7;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <h2 class="text-center">Iniciar Sesión</h2>
            <form action="auth/login.php" method="POST" class="needs-validation" novalidate> <!-- Añadida la clase -->
                <div class="form-group">
                    <label for="email">Correo Electrónico</label>
                    <input type="email" name="email" class="form-control" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    <div class="invalid-feedback">Por favor, ingrese un correo válido.</div>
                </div>
                <div class="form-group">
                    <label for="contrasena">Contraseña</label>
                    <input type="password" name="contrasena" class="form-control" required>
                    <div class="invalid-feedback">Por favor, ingrese su contraseña.</div>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Iniciar Sesión</button>
            </form>

            <!-- Botón para recuperar contraseña -->
            <div class="mt-3 text-center">
                <a href="recuperar_contraseña.php">¿Olvidaste tu contraseña?</a>
            </div>

            <?php
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                include '../includes/conexion.php'; // Ajusta la ruta
                
                // Preparar la consulta para evitar inyecciones SQL
                $stmt = $conn->prepare("SELECT * FROM usuarios WHERE email = ?");
                $stmt->bind_param("s", $email);
                
                $email = $_POST['email'];
                $contrasena = $_POST['contrasena'];

                $stmt->execute();
                $resultado = $stmt->get_result();

                if ($resultado->num_rows > 0) {
                    $usuario = $resultado->fetch_assoc();

                    // Verificar la contraseña
                    if (password_verify($contrasena, $usuario['contrasena'])) {
                        // Inicio de sesión exitoso
                        session_start();
                        $_SESSION['usuario_id'] = $usuario['id'];
                        $_SESSION['nombre_usuario'] = $usuario['nombre_usuario'];

                        header("Location: ../peliculas/peliculas.php"); // Redirigir a películas
                        exit();
                    } else {
                        echo "<div class='alert alert-danger mt-3'>Contraseña incorrecta.</div>";
                    }
                } else {
                    echo "<div class='alert alert-danger mt-3'>El usuario no existe.</div>";
                }

                $stmt->close();
                $conn->close();
            }
            ?>
        </div>
    </div>

    <script>
        // Script para la validación de formulario de Bootstrap
        (function () {
            'use strict';
            window.addEventListener('load', function () {
                var forms = document.getElementsByClassName('needs-validation');
                Array.prototype.filter.call(forms, function (form) {
                    form.addEventListener('submit', function (event) {
                        if (form.checkValidity() === false) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        form.classList.add('was-validated');
                    }, false);
                });
            }, false);
        })();
    </script>
</body>
</html>
