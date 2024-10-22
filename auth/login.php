<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['usuario_id'])) {
    header("Location: ../peliculas/peliculas.php");
    exit();
}
?>
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
            <form action="login.php" method="POST" class="needs-validation" novalidate>
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

            <div class="mt-3 text-center">
                <a href="recuperar_contraseña.php">¿Olvidaste tu contraseña?</a>
            </div>

            <?php
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                include '../includes/conexion.php';
                if (!$conn) {
                    die("Error: No se pudo conectar a la base de datos.");
                }

                $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
                $contrasena = htmlspecialchars($_POST['contrasena'], ENT_QUOTES, 'UTF-8');

                if (empty($email) || empty($contrasena)) {
                    echo "<div class='alert alert-danger mt-3'>Por favor, complete todos los campos.</div>";
                } else {
                    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE email = ?");
                    $stmt->bind_param("s", $email);
                    $stmt->execute();
                    $resultado = $stmt->get_result();

                    if ($resultado->num_rows > 0) {
                        $usuario = $resultado->fetch_assoc();

                        if (password_verify($contrasena, $usuario['contrasena'])) {
                            $_SESSION['usuario_id'] = $usuario['id'];
                            $_SESSION['nombre_usuario'] = $usuario['nombre_usuario'];

                            header("Refresh: 2; URL=../peliculas/peliculas.php");
                            exit();
                        } else {
                            echo "<div class='alert alert-danger mt-3'>La contraseña es incorrecta.</div>";
                        }
                    } else {
                        echo "<div class='alert alert-danger mt-3'>No se encontró una cuenta asociada con ese correo.</div>";
                    }

                    $stmt->close();
                    $conn->close();
                }
            }
            ?>
        </div>
    </div>

    <script>
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
