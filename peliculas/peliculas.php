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


// Consultas a la API para diferentes secciones
$novedades = obtenerPeliculas("now_playing");
$mejores_historias = obtenerPeliculas("top_rated");
$proximamente = obtenerPeliculas("upcoming");
$populares = obtenerPeliculas("popular");

// Definir listas personalizadas
$listas_personalizadas = [
    "mejores2000" => obtenerPeliculas("popular"),
    "mejoresMiedo" => obtenerPeliculas("top_rated"),
    "mejores2023" => obtenerPeliculas("upcoming")
];

// Obtener películas destacadas
$peliculas_destacadas = obtenerPeliculas("top_rated");


?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lista de Películas</title>
    <link rel="stylesheet" href="../assets/css/peliculas.css">
</head>
<body>
    <!-- Barra superior fija -->
    <div class="navbar-fixed-top">
        <div class="nav-title">Películas</div>
        <div>
            <a href="../usuario/perfil.php">Mi Perfil</a>
            <a href="../auth/logout.php" class="btn btn-danger">Cerrar Sesión</a>
        </div>
    </div>

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
       <!-- Formulario de Búsqueda en peliculas.php -->
       <div class="filtro-container">
    <h3>Buscar Películas</h3>
    <form action="buscar.php" method="GET" class="filtro-form">
        <!-- Filtro por Año -->
        <input type="number" name="anio" class="form-control" placeholder="Año" min="1900" max="2099">

        <!-- Filtro por Género -->
        <select name="genero" class="form-control">
            <option value="">Género</option>
            <option value="28">Acción</option>
            <option value="35">Comedia</option>
            <option value="18">Drama</option>
            <option value="99">Documental</option>
            <option value="27">Terror</option>
        </select>

        <!-- Filtro por Tipo (Película/Serie/Documental) -->
        <select name="tipo" class="form-control">
            <option value="">Tipo</option>
            <option value="movie">Película</option>
            <option value="tv">Serie</option>
            <option value="documentary">Documental</option>
        </select>

        <!-- Filtro por País de Origen -->
        <select name="pais" class="form-control">
            <option value="">País</option>
            <option value="US">Estados Unidos</option>
            <option value="FR">Francia</option>
            <option value="ES">España</option>
            <option value="IT">Italia</option>
            <option value="JP">Japón</option>
        </select>

        <!-- Campo de búsqueda -->
        <input type="text" name="buscar" class="form-control" placeholder="Buscar...">

        <!-- Botón de búsqueda -->
        <button type="submit" class="btn btn-primary">Buscar</button>
    </form>
</div>




        <!-- Sección de Novedades -->
        <section>
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
          <section>
            <h3 class="section-title">Listas Personalizadas</h3>
            <div class="listas-personalizadas">
                <?php foreach ($listas_personalizadas as $nombre_lista => $peliculas): ?>
                    <?php if (is_array($peliculas) && !empty($peliculas)): // Validar que sea un array ?>
                        <div class="lista-personalizada">
                            <a href="peliculas_categoria.php?categoria=<?php echo $nombre_lista; ?>">
                                <h4><?php echo ucfirst(str_replace("_", " ", $nombre_lista)); ?></h4>
                                <div class="lista-grid">
                                    <?php foreach (array_slice($peliculas, 0, 4) as $movie): ?>
                                        <img src="<?php echo $image_base_url . $movie['poster_path']; ?>" alt="Poster de <?php echo $movie['title']; ?>">
                                    <?php endforeach; ?>
                                </div>
                            </a>
                        </div>
                    <?php else: ?>
                        <p>No hay películas disponibles para la lista "<?php echo ucfirst(str_replace("_", " ", $nombre_lista)); ?>"</p>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </section>
    </div>
      <!-- Footer -->
      <footer class="footer">
            <div class="footer-content">
                <div class="footer-section">
                    <h4>Enlaces Rápidos</h4>
                    <ul>
                        <li><a href="#novedades">Novedades</a></li>
                        <li><a href="#destacadas">Películas Destacadas</a></li>
                        <li><a href="#proximamente">Próximamente en Cines</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Contacta con Nosotros</h4>
                    <p>Teléfono: +34 123 456 789</p>
                    <p>Email: contacto@peliculas.com</p>
                </div>
                <div class="footer-section">
                    <h4>Síguenos</h4>
                    <a href="https://facebook.com" target="_blank">Facebook</a> |
                    <a href="https://instagram.com" target="_blank">Instagram</a> |
                    <a href="https://twitter.com" target="_blank">Twitter</a>
                </div>
            </div>
            <p class="footer-bottom">© 2023 Películas Web. Todos los derechos reservados.</p>
        </footer>
        <!-- Botón de Volver Arriba -->
<button id="volver-arriba" title="Volver Arriba">&#8679;</button>
    <script src="../assets/js/peliculas.js"></script>
</body>
</html>
