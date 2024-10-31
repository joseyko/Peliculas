<?php
// Iniciar sesión
session_start();

// Incluir el archivo de conexión a la base de datos
include '../includes/conexion.php'; // Ajusta la ruta si es necesario

// Verifica que el ID de la película esté presente en la URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "Película no encontrada.";
    exit();
}

// Reemplaza 'YOUR_API_KEY' con tu clave de API de TMDb
$api_key = '56ca5cf36846fb5c1465bf82478749b5';
$movie_id = intval($_GET['id']);
echo "<p>ID de la película en detalle_pelicula.php: " . $movie_id . "</p>"; // Depuración: muestra el ID de la película en la página

$url = 'https://api.themoviedb.org/3/movie/' . $movie_id . '?api_key=' . $api_key . '&language=es-ES';

// Realizar la solicitud HTTP para obtener detalles de la película
$response = file_get_contents($url);
$data = json_decode($response, true);

if (!$data || isset($data['status_code'])) {
    echo "No se encontraron detalles para esta película.";
    exit();
}

// Solicitud para obtener trailers y videos de la película
$videos_url = 'https://api.themoviedb.org/3/movie/' . $movie_id . '/videos?api_key=' . $api_key;
$videos_response = file_get_contents($videos_url);
$videos_data = json_decode($videos_response, true);

// URL base para obtener imágenes de TMDb
$image_base_url = 'https://image.tmdb.org/t/p/w500';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($data['title']); ?> - Detalles de la Película</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center"><?php echo htmlspecialchars($data['title']); ?></h1>
        
        <div class="row mt-4">
            <!-- Imagen de la película -->
            <div class="col-md-4">
                <?php if (!empty($data['poster_path'])): ?>
                    <img src="<?php echo $image_base_url . $data['poster_path']; ?>" alt="<?php echo htmlspecialchars($data['title']); ?>" class="img-fluid">
                <?php else: ?>
                    <img src="../assets/imagenes/default.png" alt="Imagen no disponible" class="img-fluid">
                <?php endif; ?>
            </div>

            <!-- Detalles de la película -->
            <div class="col-md-8">
                <h3>Descripción</h3>
                <p><?php echo htmlspecialchars($data['overview']); ?></p>
                
                <p><strong>Fecha de estreno:</strong> <?php echo htmlspecialchars($data['release_date']); ?></p>
                <p><strong>Calificación:</strong> <?php echo htmlspecialchars($data['vote_average']); ?> / 10</p>
                <p><strong>Duración:</strong> <?php echo htmlspecialchars($data['runtime']); ?> minutos</p>
                
                <h4>Géneros</h4>
                <ul>
                    <?php foreach ($data['genres'] as $genre): ?>
                        <li><?php echo htmlspecialchars($genre['name']); ?></li>
                    <?php endforeach; ?>
                </ul>
                
                <h4>Producción</h4>
                <ul>
                    <?php foreach ($data['production_companies'] as $company): ?>
                        <li><?php echo htmlspecialchars($company['name']); ?></li>
                    <?php endforeach; ?>
                </ul>
                
                <h4>Países de producción</h4>
                <ul>
                    <?php foreach ($data['production_countries'] as $country): ?>
                        <li><?php echo htmlspecialchars($country['name']); ?></li>
                    <?php endforeach; ?>
                </ul>
                
                <a href="peliculas.php" class="btn btn-primary mt-3">Volver a la lista</a>
            </div>
        </div>

        <!-- Sección de Trailer -->
        <div class="row mt-5">
            <div class="col-12">
                <h3>Trailer</h3>
                <?php
                if (!empty($videos_data['results'])) {
                    foreach ($videos_data['results'] as $video) {
                        // Mostrar solo videos de YouTube y de tipo Trailer
                        if ($video['site'] === 'YouTube' && $video['type'] === 'Trailer') {
                            echo '<div class="embed-responsive embed-responsive-16by9">';
                            echo '<iframe class="embed-responsive-item" src="https://www.youtube.com/embed/' . $video['key'] . '" allowfullscreen></iframe>';
                            echo '</div>';
                            break; // Muestra solo el primer trailer
                        }
                    }
                } else {
                    echo "<p>No hay trailers disponibles para esta película.</p>";
                }
                ?>
                
                <?php if (isset($_SESSION['usuario_id'])): ?>
                    <div class="mt-4">
                        <h4>Califica esta Película</h4>
                        <form action="guardar_calificacion.php" method="POST">
                            <input type="hidden" name="movie_id" value="<?php echo $movie_id; ?>">
                            <label for="calificacion">Calificación (1-5):</label>
                            <select name="calificacion" required>
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                <?php endfor; ?>
                            </select>
                            <button type="submit" class="btn btn-primary">Enviar Calificación</button>
                        </form>
                    </div>
                <?php else: ?>
                    <p>Debes <a href="auth/login2.php">iniciar sesión</a> para calificar esta película.</p>
                <?php endif; ?>

                <?php
                // Consulta para obtener la calificación promedio
                $stmt = $conn->prepare("SELECT AVG(calificacion) AS promedio, COUNT(calificacion) AS total FROM calificaciones WHERE movie_id = ?");
                $stmt->bind_param("i", $movie_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $calificacion_data = $result->fetch_assoc();
                ?>

                <div class="mt-4">
                    <h4>Calificación Promedio</h4>
                    <?php if ($calificacion_data['total'] > 0): ?>
                        <p><?php echo round($calificacion_data['promedio'], 1); ?> de 5 (<?php echo $calificacion_data['total']; ?> calificaciones)</p>
                    <?php else: ?>
                        <p>Aún no hay calificaciones para esta película.</p>
                    <?php endif; ?>
                </div>
                
                <!-- Formulario para comentarios -->
                <?php
                if (!isset($_SESSION['usuario_id'])) {
                    echo "<p>Debes iniciar sesión para dejar un comentario.</p>";
                } else {
                    ?>
                    <div class="mt-5">
                        <h4>Deja tu Reseña</h4>
                        <form action="guardar_comentario.php" method="POST">
                            <input type="hidden" name="movie_id" value="<?php echo $movie_id; ?>">
                            <div class="form-group">
                                <textarea name="comentario" class="form-control" rows="4" placeholder="Escribe tu reseña aquí..." required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Enviar Reseña</button>
                        </form>
                    </div>
                    <?php
                }

                // Mostrar comentarios
                $comentarios = $conn->prepare("SELECT c.comentario, c.fecha, u.nombre_usuario FROM comentarios c JOIN usuarios u ON c.usuario_id = u.id WHERE c.movie_id = ? ORDER BY c.fecha DESC");
                $comentarios->bind_param("i", $movie_id);
                $comentarios->execute();
                $resultado_comentarios = $comentarios->get_result();

                if ($resultado_comentarios->num_rows > 0): ?>
                    <h4 class="mt-5">Comentarios de Usuarios</h4>
                    <ul class="list-unstyled">
                        <?php while ($comentario = $resultado_comentarios->fetch_assoc()): ?>
                            <li class="mb-3">
                                <strong><?php echo htmlspecialchars($comentario['nombre_usuario']); ?></strong> 
                                <span class="text-muted">(<?php echo $comentario['fecha']; ?>)</span>
                                <p><?php echo htmlspecialchars($comentario['comentario']); ?></p>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                <?php else: ?>
                    <p class="mt-3">No hay comentarios para esta película.</p>
                <?php endif;

                $comentarios->close();
                ?>
            </div>
        </div>
    </div>
</body>
</html>
