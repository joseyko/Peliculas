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
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1; // Página actual

function obtenerPeliculasPorCategoria($categoria, $pagina) {
    global $api_key;
    $url = '';

    switch ($categoria) {
        case 'peliculasTerror':
            $url = "https://api.themoviedb.org/3/discover/movie?api_key={$api_key}&language=es-ES&with_genres=27&page={$pagina}";
            break;
        case 'mejores2010':
            $url = "https://api.themoviedb.org/3/discover/movie?api_key={$api_key}&language=es-ES&primary_release_year=2010&sort_by=vote_average.desc&page={$pagina}";
            break;
        case 'mejores2023':
            $url = "https://api.themoviedb.org/3/discover/movie?api_key={$api_key}&language=es-ES&primary_release_year=2023&sort_by=vote_average.desc&page={$pagina}";
            break;
        case 'peliculasComedia':
            $url = "https://api.themoviedb.org/3/discover/movie?api_key={$api_key}&language=es-ES&with_genres=35&page={$pagina}";
            break;
        default:
            return []; // Si la categoría no coincide, devolver un array vacío
    }

    $response = file_get_contents($url);
    return json_decode($response, true)['results'] ?? [];
}

$peliculas = obtenerPeliculasPorCategoria($categoria, $pagina);

if (isset($_GET['ajax'])) {
    // Si es una solicitud AJAX, devolver JSON
    echo json_encode($peliculas);
    exit();
}
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

        <!-- Contenedor de las películas -->
        <div id="movie-container" class="movie-row">
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

    <script>
        // Variables para la carga de más películas
        let page = 2;
        let loading = false;

        // Función para cargar más películas
        function loadMoreMovies() {
            if (loading) return;
            loading = true;

            fetch(`peliculas_categoria.php?categoria=<?php echo $categoria; ?>&pagina=${page}&ajax=1`)
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('movie-container');
                    data.forEach(movie => {
                        const movieCard = document.createElement('div');
                        movieCard.classList.add('movie-card', 'text-center');
                        movieCard.innerHTML = `
                            <a href="detalle_pelicula.php?id=${movie.id}">
                                <img src="<?php echo $image_base_url; ?>${movie.poster_path}" alt="Poster de ${movie.title}">
                            </a>
                            <div class="rating-stars">
                                ${'★'.repeat(Math.round(movie.vote_average / 2))}${'☆'.repeat(5 - Math.round(movie.vote_average / 2))}
                            </div>
                        `;
                        container.appendChild(movieCard);
                    });
                    page++;
                    loading = false;
                })
                .catch(() => loading = false);
        }

        // Detectar cuando se alcanza el final de la página
        window.addEventListener('scroll', () => {
            if (window.innerHeight + window.scrollY >= document.body.offsetHeight - 100) {
                loadMoreMovies();
            }
        });
    </script>
</body>
</html>
