<?php
// Iniciar sesión
session_start();

// Incluir el archivo de conexión a la base de datos
include '../includes/conexion.php';

// Verifica que el ID de la película esté presente en la URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "Película no encontrada.";
    exit();
}

// API Key y configuración
$api_key = '56ca5cf36846fb5c1465bf82478749b5';
$movie_id = intval($_GET['id']);
$url = 'https://api.themoviedb.org/3/movie/' . $movie_id . '?api_key=' . $api_key . '&language=es-ES';
$response = file_get_contents($url);
$data = json_decode($response, true);

if (!$data || isset($data['status_code'])) {
    echo "No se encontraron detalles para esta película.";
    exit();
}

// Solicitud para obtener trailers y videos de la película
$videos_url = "https://api.themoviedb.org/3/movie/{$movie_id}/videos?api_key={$api_key}&language=es-ES";
$videos_response = file_get_contents($videos_url);
$videos_data = json_decode($videos_response, true);

// Asegurarse de que $videos_data esté definido incluso si no hay resultados
if (!$videos_data || !isset($videos_data['results'])) {
    $videos_data = ['results' => []];
}

// Consultar comentarios relacionados con la película
$usuario_id = $_SESSION['usuario_id'] ?? null;
$estado = 'Por Ver';
$en_favoritos = false;
$resultado_comentarios = null;

if ($usuario_id) {
    // Verificar progreso del usuario
    $stmt = $conn->prepare("SELECT estado FROM historial WHERE usuario_id = ? AND pelicula_id = ?");
    $stmt->bind_param("ii", $usuario_id, $movie_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $estado = $row['estado'];
    }
    $stmt->close();

    // Verificar si está en favoritos
    $stmt = $conn->prepare("SELECT * FROM favoritos WHERE usuario_id = ? AND movie_id = ?");
    $stmt->bind_param("ii", $usuario_id, $movie_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $en_favoritos = $result->num_rows > 0;
    $stmt->close();

    // Consultar comentarios
    $stmt_comentarios = $conn->prepare("
        SELECT c.comentario, c.fecha, u.nombre_usuario 
        FROM comentarios c 
        JOIN usuarios u ON c.usuario_id = u.id 
        WHERE c.movie_id = ? 
        ORDER BY c.fecha DESC
    ");
    $stmt_comentarios->bind_param("i", $movie_id);
    $stmt_comentarios->execute();
    $resultado_comentarios = $stmt_comentarios->get_result();
}

$image_base_url = 'https://image.tmdb.org/t/p/w500';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($data['title']); ?> - Detalles de la Película</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.5/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
    <!-- Estilo Personalizado -->
    <link rel="stylesheet" href="../assets/css/detalle_pelicula.css">
</head>
<body class="bg-dark text-light">
    <!-- Barra superior -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top shadow">
        <div class="container">
            <!-- Título de la película -->
            <span class="navbar-brand fw-bold text-warning">
                <?php echo htmlspecialchars($data['title']); ?>
            </span>
            <!-- Botón de volver a la lista -->
            <a href="peliculas.php" class="btn btn-warning text-dark fw-bold">
                <i class="bi bi-arrow-left-circle"></i> Volver a la lista
            </a>
        </div>
    </nav>

    <!-- Contenido principal -->
    <div class="container mt-5 pt-5">
        <!-- Tarjeta de detalles de la película -->
        <div class="card bg-secondary shadow-lg border-0 d-flex flex-lg-row flex-column p-3">
            <!-- Imagen de la película -->
            <div class="col-lg-4 text-center">
                <?php if (!empty($data['poster_path'])): ?>
                    <img src="<?php echo $image_base_url . $data['poster_path']; ?>" alt="<?php echo htmlspecialchars($data['title']); ?>" class="img-fluid rounded shadow-lg">
                <?php else: ?>
                    <img src="../assets/imagenes/default.png" alt="Imagen no disponible" class="img-fluid rounded shadow-lg">
                <?php endif; ?>
            </div>
            <!-- Detalles de la película -->
            <div class="col-lg-8">
                <div class="details p-3">
                    <h2 class="text-warning"><?php echo htmlspecialchars($data['title']); ?></h2>
                    <ul class="list-unstyled">
                        <li><strong>Estreno:</strong> <?php echo htmlspecialchars($data['release_date']); ?></li>
                        <li><strong>Duración:</strong> <?php echo htmlspecialchars($data['runtime']); ?> minutos</li>
                        <li><strong>Calificación:</strong> <?php echo htmlspecialchars($data['vote_average']); ?> / 10</li>
                    </ul>
                    <h4>Géneros</h4>
                    <ul class="list-inline">
                        <?php foreach ($data['genres'] as $genre): ?>
                            <li class="list-inline-item badge bg-primary"><?php echo htmlspecialchars($genre['name']); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Progreso de visualización -->
        <?php if ($usuario_id): ?>
            <div class="mt-4">
                <div class="card bg-dark shadow">
                    <div class="card-body">
                        <h4>Progreso de Visualización</h4>
                        <form action="actualizar_progreso.php" method="POST" class="d-flex flex-column">
                            <input type="hidden" name="movie_id" value="<?php echo $movie_id; ?>">
                            <select name="estado" class="form-select mb-2" required>
                                <option value="Por Ver" <?php if ($estado === 'Por Ver') echo 'selected'; ?>>Por Ver</option>
                                <option value="En Progreso" <?php if ($estado === 'En Progreso') echo 'selected'; ?>>En Progreso</option>
                                <option value="Visto" <?php if ($estado === 'Visto') echo 'selected'; ?>>Visto</option>
                            </select>
                            <button type="submit" class="btn btn-primary">Actualizar</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Tráiler -->
        <?php if (!empty($videos_data['results'])): ?>
            <div class="trailer-container mt-5">
                <iframe src="https://www.youtube.com/embed/<?php echo htmlspecialchars($videos_data['results'][0]['key']); ?>" allowfullscreen></iframe>
            </div>
        <?php else: ?>
            <p class="text-light text-center mt-3">No hay videos disponibles para esta película.</p>
        <?php endif; ?>

        <!-- Comentarios -->
        <div class="comments-container mt-5">
            <h3>Comentarios</h3>
            <?php if ($resultado_comentarios && $resultado_comentarios->num_rows > 0): ?>
                <div class="list-group">
                    <?php while ($comentario = $resultado_comentarios->fetch_assoc()): ?>
                        <div class="list-group-item bg-secondary text-light shadow-sm">
                            <strong><?php echo htmlspecialchars($comentario['nombre_usuario']); ?></strong>
                            <span class="text-muted float-end">(<?php echo $comentario['fecha']; ?>)</span>
                            <p><?php echo htmlspecialchars($comentario['comentario']); ?></p>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p class="text-light">No hay comentarios para esta película.</p>
            <?php endif; ?>

            <!-- Formulario para añadir comentarios -->
            <?php if ($usuario_id): ?>
                <form action="guardar_comentario.php" method="POST" class="mt-3">
                    <input type="hidden" name="movie_id" value="<?php echo $movie_id; ?>">
                    <textarea name="comentario" rows="3" placeholder="Escribe tu reseña aquí..." class="form-control mb-3" required></textarea>
                    <button type="submit" class="btn btn-primary">Enviar Reseña</button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/detalle_pelicula.js"></script>
</body>
</html>


