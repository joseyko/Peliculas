<?php
session_start();

include_once '../includes/conexion.php';
include_once 'funciones.php';
include_once 'paginacion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../auth/login2.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$api_key = '56ca5cf36846fb5c1465bf82478749b5';
$image_base_url = 'https://image.tmdb.org/t/p/w500';

// Recoger filtros de búsqueda avanzada
$anio = isset($_GET['anio']) ? $_GET['anio'] : '';
$puntuacion_minima = isset($_GET['puntuacion_minima']) ? $_GET['puntuacion_minima'] : '';

// Función para obtener películas de la API con filtros adicionales
function obtenerPeliculas($categoria, $pagina = 1, $anio = '', $puntuacion_minima = '') {
    global $api_key;
    $url = "https://api.themoviedb.org/3/movie/{$categoria}?api_key={$api_key}&language=es-ES&page={$pagina}";
    
    if ($anio) {
        $url .= "&primary_release_year=" . urlencode($anio);
    }
    if ($puntuacion_minima) {
        $url .= "&vote_average.gte=" . urlencode($puntuacion_minima);
    }

    $response = file_get_contents($url);
    return json_decode($response, true)['results'] ?? [];
}
// Función para obtener películas de la API filtradas por género
function obtenerPeliculasPorGenero($genero_id, $pagina = 1, $anio = '', $puntuacion_minima = '') {
    global $api_key;
    $url = "https://api.themoviedb.org/3/discover/movie?api_key={$api_key}&language=es-ES&page={$pagina}&with_genres={$genero_id}";

    if ($anio) {
        $url .= "&primary_release_year=" . urlencode($anio);
    }
    if ($puntuacion_minima) {
        $url .= "&vote_average.gte=" . urlencode($puntuacion_minima);
    }

    $response = file_get_contents($url);
    return json_decode($response, true)['results'] ?? [];
}

// Función para obtener películas filtradas por año
function obtenerPeliculasPorAnio($anio, $pagina = 1) {
    global $api_key;
    $url = "https://api.themoviedb.org/3/discover/movie?api_key={$api_key}&language=es-ES&page={$pagina}&primary_release_year={$anio}";

    $response = file_get_contents($url);
    return json_decode($response, true)['results'] ?? [];
}


// Consultas a la API para diferentes secciones
$novedades = obtenerPeliculas("now_playing");
$mejores_historias = obtenerPeliculas("top_rated");
$proximamente = obtenerPeliculas("upcoming");
$populares = obtenerPeliculas("popular");

// Definir listas personalizadas
$listas_personalizadas = [
    "peliculasTerror" => obtenerPeliculasPorGenero(27), // Películas de Terror
    "mejores2010" => obtenerPeliculasPorAnio(2010), // Mejores Películas del 2010
    "mejores2023" => obtenerPeliculasPorAnio(2023), // Mejores Películas del 2023
    "peliculasComedia" => obtenerPeliculasPorGenero(35) // Películas de Comedia
];



// Obtener películas destacadas
$peliculas_destacadas = obtenerPeliculas("top_rated");



?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lista de Películas</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/peliculas.css"> <!-- Tu CSS personalizado -->
</head>
<body>
    <!-- Barra superior fija -->
    <nav class="navbar navbar-dark bg-dark fixed-top shadow">
    <div class="container d-flex justify-content-between">
        <a class="navbar-brand" href="#">Películas</a>
        <div>
            <a href="../usuario/perfil.php" class="btn btn-outline-light me-2">Mi Perfil</a>
            <a href="../auth/logout.php" class="btn btn-danger">Cerrar Sesión</a>
        </div>
    </div>
</nav>


    <!-- Contenido principal -->
    <div class="content-container container mt-4">
        <h2 class="text-center">Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre_usuario']); ?></h2>

        <!-- Sección de Películas Destacadas (Carrusel) -->
        <section class="carousel-container">
            <h3 class="section-title">Películas Destacadas</h3>
            <div class="carousel-slide">
                <?php foreach ($peliculas_destacadas as $movie): ?>
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
            </div>
            <div class="carousel-controls">
                <button class="prev">&laquo;</button>
                <button class="next">&raquo;</button>
            </div>
        </section>
        <div class="container mt-5 p-4 bg-dark text-white rounded">
    <h3 class="text-center mb-3">Buscar Películas</h3>
    <form action="buscar.php" method="GET" class="row gx-2 gy-3 justify-content-center align-items-center">
        <!-- Filtro por Año -->
        <div class="col-6 col-md-2">
            <input type="number" name="anio" class="form-control form-control-sm custom-input" placeholder="Año" min="1900" max="2099">
        </div>

        <!-- Filtro por Género -->
        <div class="col-6 col-md-2">
            <select name="genero" class="form-select form-select-sm custom-input">
                <option value="">Género</option>
                <option value="28">Acción</option>
                <option value="35">Comedia</option>
                <option value="18">Drama</option>
                <option value="99">Documental</option>
                <option value="27">Terror</option>
            </select>
        </div>

        <!-- Filtro por Tipo -->
        <div class="col-6 col-md-2">
            <select name="tipo" class="form-select form-select-sm custom-input">
                <option value="">Tipo</option>
                <option value="movie">Película</option>
                <option value="tv">Serie</option>
                <option value="documentary">Documental</option>
            </select>
        </div>

        <!-- Filtro por País de Origen -->
        <div class="col-6 col-md-2">
            <select name="pais" class="form-select form-select-sm custom-input">
                <option value="">País</option>
                <option value="US">Estados Unidos</option>
                <option value="FR">Francia</option>
                <option value="ES">España</option>
                <option value="IT">Italia</option>
                <option value="JP">Japón</option>
            </select>
        </div>

        <!-- Campo de búsqueda -->
        <div class="col-12 col-md-3">
            <input type="text" name="buscar" class="form-control form-control-sm custom-input" placeholder="Buscar...">
        </div>

        <!-- Botón de búsqueda -->
        <div class="col-12 col-md-2">
            <button type="submit" class="btn btn-outline-light btn-sm w-100">Buscar</button>
        </div>
    </form>
</div>


        <!-- Sección de Novedades -->
       <!-- <section>
            <h3 class="section-title">Novedades</h3>
            <div class="movie-row">
                <?php foreach ($novedades as $movie): ?>
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
            </div>
        </section>
                -->
<!-- Sección de Novedades con bootstrap
 -->
<div class="container mt-5">
    <h2 class="section-title text-center mb-4">Novedades</h2>
    <div class="row justify-content-center">
        <?php foreach ($novedades as $movie): ?>
            <div class="col-6 col-md-4" style="flex: 0 0 20%; max-width: 20%; margin-bottom: 1rem;">
                <div class="card movie-card bg-dark text-white border-0">
                    <a href="detalle_pelicula.php?id=<?php echo $movie['id']; ?>" class="position-relative">
                        <img src="<?php echo $image_base_url . $movie['poster_path']; ?>" class="card-img-top" alt="Poster de <?php echo $movie['title']; ?>">
                        <div class="movie-details">
                            <p><?php echo htmlspecialchars($movie['overview']); ?></p>
                            <p><strong>Estreno:</strong> <?php echo $movie['release_date']; ?></p>
                        </div>
                    </a>
                    <div class="card-footer text-center" style="color: #5ce0e6; font-size: 2rem;">
                        <?php for ($i = 0; $i < 5; $i++) echo $i < round($movie['vote_average'] / 2) ? '★' : '☆'; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

        <!-- Sección de Mejores Películas de la Historia -->
        <section>
            <h3 class="section-title">Mejores Películas de la Historia</h3>
            <div class="movie-row">
                <?php foreach ($mejores_historias as $movie): ?>
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
            </div>
        </section>

        <!-- Sección de Próximamente en Cines -->
        <section>
            <h3 class="section-title">Próximamente en Cines</h3>
            <div class="movie-row">
                <?php foreach ($proximamente as $movie): ?>
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
            </div>
        </section>

        <!-- Sección de Populares: "Películas que todo el mundo debería ver" -->
        <section>
            <h3 class="section-title">Películas que Todo el Mundo Debería Ver</h3>
            <div class="movie-row">
                <?php foreach ($populares as $movie): ?>
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
            </div>
        </section>
          <!-- Sección de Listas Personalizadas -->
          <div class="container mt-5">
    <h3 class="section-title text-center mb-5">Listas Personalizadas</h3>
    <div class="row row-cols-1 row-cols-md-4 g-4">
        <?php foreach ($listas_personalizadas as $nombre_lista => $peliculas): ?>
            <div class="col d-flex justify-content-center">
                <div class="card bg-dark text-white shadow-sm border-0 rounded-4" style="width: 16rem;">
                    <div class="card-header text-center py-3" style="background: linear-gradient(45deg, #5ce0e6, #3c8dbc); font-weight: bold;">
                        <?php echo ucfirst(str_replace("_", " ", $nombre_lista)); ?>
                    </div>
                    <div class="card-body d-flex justify-content-center align-items-center" style="position: relative; height: 160px;">
                        <?php if (is_array($peliculas) && !empty($peliculas)): ?>
                            <?php 
                                $total_movies = count($peliculas) > 4 ? 4 : count($peliculas); // Limitar a 4 portadas
                                $initial_offset = -($total_movies - 1) * 15; // Calcular desplazamiento inicial para centrar
                                foreach (array_slice($peliculas, 0, 4) as $index => $movie): 
                            ?>
                                <a href="detalle_pelicula.php?id=<?php echo $movie['id']; ?>" class="text-decoration-none"
                                   style="position: absolute; left: 50%; transform: translateX(calc(<?php echo $initial_offset + ($index * 30); ?>px - 50%)); z-index: <?php echo $index; ?>;">
                                    <img src="<?php echo $image_base_url . $movie['poster_path']; ?>" alt="Poster de <?php echo $movie['title']; ?>" 
                                         class="rounded-3" style="width: 80px; height: 120px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);">
                                </a>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-center text-muted">No hay películas disponibles en esta lista.</p>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer text-center border-0 bg-transparent">
                        <a href="peliculas_categoria.php?categoria=<?php echo $nombre_lista; ?>" class="btn btn-outline-info btn-sm w-75">Ver Lista Completa</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

      <!-- Footer -->
      <footer class="bg-dark text-light py-4 mt-5 w-100">
    <div class="container-fluid px-5">
        <div class="row text-center text-md-start">
            <!-- Sección de información -->
            <div class="col-md-4 mb-4">
                <h5 class="text-warning">Sobre Nosotros</h5>
                <p>Descubre las mejores películas y mantente al día con los últimos lanzamientos. Tu portal de cine confiable.</p>
            </div>
            <!-- Sección de enlaces rápidos -->
            <div class="col-md-4 mb-4">
                <h5 class="text-warning">Enlaces Rápidos</h5>
                <ul class="list-unstyled">
                    <li><a href="#" class="text-light text-decoration-none">Inicio</a></li>
                    <li><a href="#" class="text-light text-decoration-none">Populares</a></li>
                    <li><a href="#" class="text-light text-decoration-none">Novedades</a></li>
                    <li><a href="#" class="text-light text-decoration-none">Contacto</a></li>
                </ul>
            </div>
            <!-- Sección de contacto -->
            <div class="col-md-4 mb-4">
                <h5 class="text-warning">Contáctanos</h5>
                <ul class="list-unstyled">
                    <li><i class="bi bi-envelope me-2"></i> contacto@peliculas.com</li>
                    <li><i class="bi bi-telephone me-2"></i> +34 123 456 789</li>
                    <li><i class="bi bi-geo-alt me-2"></i> Calle Cine, 123, Madrid</li>
                </ul>
                <div class="d-flex gap-3 mt-3">
                    <a href="#" class="text-light"><i class="bi bi-facebook fs-4"></i></a>
                    <a href="#" class="text-light"><i class="bi bi-twitter fs-4"></i></a>
                    <a href="#" class="text-light"><i class="bi bi-instagram fs-4"></i></a>
                </div>
            </div>
        </div>
        <div class="text-center mt-4">
            <small class="text-muted">© 2024 Películas.com - Todos los derechos reservados.</small>
        </div>
    </div>
</footer>

        <!-- Botón de Volver Arriba -->
<button id="volver-arriba" title="Volver Arriba">&#8679;</button>
    <script src="../assets/js/peliculas.js"></script>
</body>
</html>
