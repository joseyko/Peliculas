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

// Capturar filtros de búsqueda
$anio = isset($_GET['anio']) ? $_GET['anio'] : '';
$genero = isset($_GET['genero']) ? $_GET['genero'] : '';
$tipo = isset($_GET['tipo']) && in_array($_GET['tipo'], ['movie', 'tv']) ? $_GET['tipo'] : 'movie'; // Asegurar que sea 'movie' o 'tv'
$pais = isset($_GET['pais']) ? $_GET['pais'] : '';
$buscar = isset($_GET['buscar']) ? $_GET['buscar'] : '';

// Función para obtener resultados filtrados
function buscarPeliculas($pagina = 1, $anio = '', $genero = '', $tipo = 'movie', $pais = '', $buscar = '') {
    global $api_key;
    // Verificar y construir la URL de la API con el tipo especificado
    $url = "https://api.themoviedb.org/3/discover/{$tipo}?api_key={$api_key}&language=es-ES&page={$pagina}";

    if ($anio) {
        $url .= "&primary_release_year=" . urlencode($anio);
    }
    if ($genero) {
        $url .= "&with_genres=" . urlencode($genero);
    }
    if ($pais) {
        $url .= "&region=" . urlencode($pais);
    }
    if ($buscar) {
        $url .= "&query=" . urlencode($buscar);
    }

    // Obtener y decodificar respuesta de la API
    $response = file_get_contents($url);
    $data = json_decode($response, true);

    // Validar respuesta y devolver resultados o un array vacío si no hay resultados
    if (isset($data['results']) && is_array($data['results'])) {
        return $data['results'];
    } else {
        return []; // Devolver un array vacío si no hay resultados válidos
    }
}

// Obtener resultados de búsqueda
$resultados = buscarPeliculas(1, $anio, $genero, $tipo, $pais, $buscar);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Resultados de Búsqueda</title>
    <link rel="stylesheet" href="../assets/css/peliculas.css">
</head>
<body>
    <!-- Barra superior fija -->
    <div class="navbar-fixed-top">
        <div class="nav-title">Resultados de Búsqueda</div>
        <div>
            <a href="peliculas.php">Volver a Películas</a>
            <a href="../usuario/perfil.php">Mi Perfil</a>
            <a href="../auth/logout.php" class="btn btn-danger">Cerrar Sesión</a>
        </div>
    </div>

    <!-- Resultados de Búsqueda -->
    <div class="content-container container mt-4">
        <h2 class="text-center">Resultados de Búsqueda</h2>
        
        <section>
            <div class="movie-row">
                <?php if (empty($resultados)): ?>
                    <p class="text-center">No se encontraron resultados para los filtros aplicados.</p>
                <?php else: ?>
                    <?php foreach ($resultados as $movie): ?>
                        <div class="movie-card text-center">
                            <a href="detalle_pelicula.php?id=<?php echo $movie['id']; ?>">
                                <img src="<?php echo $image_base_url . $movie['poster_path']; ?>" alt="Poster de <?php echo $movie['title']; ?>">
                            </a>
                            <div class="movie-details">
                                <p><?php echo htmlspecialchars($movie['overview']); ?></p>
                                <p><strong>Estreno:</strong> <?php echo $movie['release_date']; ?></p>
                            </div>
                            <div class="rating-stars">
                                <?php for ($i = 0; $i < 5; $i++) echo $i < round($movie['vote_average'] / 2) ? '★' : '☆'; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </div>
    <script src="../assets/js/peliculas.js"></script>
</body>
</html>
