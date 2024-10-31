<?php
session_start();

include_once '../includes/conexion.php';
include_once 'funciones.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../auth/login2.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$api_key = '56ca5cf36846fb5c1465bf82478749b5';
$image_base_url = 'https://image.tmdb.org/t/p/w500';

$categoria = isset($_GET['categoria']) ? $_GET['categoria'] : '';

function obtenerPeliculasPorCategoria($categoria) {
    global $api_key;
    $query = urlencode($categoria);
    $url = "https://api.themoviedb.org/3/search/movie?api_key={$api_key}&language=es-ES&query={$query}";
    $response = file_get_contents($url);
    return json_decode($response, true)['results'] ?? [];
}

$peliculas = obtenerPeliculasPorCategoria($categoria);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($categoria); ?> - Películas</title>
    <link rel="stylesheet" href="../assets/css/peliculas.css">
</head>
<body>
    <!-- Barra superior fija -->
    <div class="navbar-fixed-top">
        <div class="nav-title"><?php echo htmlspecialchars($categoria); ?></div>
        <div>
            <a href="peliculas.php">Volver a Películas</a>
        </div>
    </div>

    <!-- Contenido principal -->
    <div class="content-container container mt-4">
        <h2 class="text-center">Películas: <?php echo htmlspecialchars($categoria); ?></h2>

        <!-- Mostrar películas de la categoría -->
        <div class="movie-row">
            <?php foreach ($peliculas as $movie): ?>
                <div class="movie-card text-center">
                    <a href="detalle_pelicula.php?id=<?php echo $movie['id']; ?>">
                        <img src="<?php echo $image_base_url . $movie['poster_path']; ?>" alt="Poster de <?php echo $movie['title']; ?>">
                    </a>
                    <div class="rating-stars">
                        <?php for ($i = 0; $i < 5; $i++) echo $i < round($movie['vote_average'] / 2) ? '★' : '☆'; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
