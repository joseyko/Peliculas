<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRTIX</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/estilos.css"> <!-- Archivo de estilos personalizados -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&family=Poppins:wght@600&display=swap" rel="stylesheet">
</head>
<body>

    <!-- Sección de Bienvenida -->
    <div class="hero">
        <h1>EXPLORA EL MUNDO DEL CINE. TODO EN UN LUGAR</h1>
        <p class="lead">Únete a nuestra comunidad de cinéfilos, descubre y comparte tus películas favoritas. Todo en CRTIX</p>
        <div class="d-flex flex-column align-items-center">
            <a href="auth/registro.php" class="btn btn-custom">Regístrate ahora</a>
            <a href="auth/login.php" class="link-secondary mt-3">Ya tengo una cuenta</a> <!-- Añadido mt-3 para margen superior -->
        </div>
    </div>

    <!-- Divider SVG -->
    <div class="divider">
        <svg width="100%" height="100" viewBox="0 0 1440 100" preserveAspectRatio="none">
            <path d="M0,0 C720,100 720,0 1440,0 L1440,100 L0,100 Z" style="fill: #7488bf;"></path>
        </svg>
    </div>

    <!-- Nueva Sección -->
    <div class="features-section">
        <div class="container">
            <div class="row text-center">
                <div class="col-6 col-md-3 feature-box">
                    <h3>Valora tus películas favoritas</h3>
                </div>
                <div class="col-6 col-md-3 feature-box">
                    <h3>Añade comentarios sobre todas las películas que quieras</h3>
                </div>
                <div class="col-6 col-md-3 feature-box">
                    <h3>Mantente actualizado de noticias sobre las películas</h3>
                </div>
                <div class="col-6 col-md-3 feature-box">
                    <h3>Haz listas de tus películas</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>© 2024 Tu Proyecto de Películas. Todos los derechos reservados.</p>
            <p>Encuentra nuevas historias y redescubre los clásicos en nuestro sitio.</p>
        </div>
    </footer>

</body>
</html>
