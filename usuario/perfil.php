<?php
session_start();

include_once '../includes/conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../auth/login2.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];


function obtenerHistorialVisualizacion($usuario_id, $conn) {
    $stmt = $conn->prepare("
        SELECT h.fecha_visualizacion, h.calificacion, h.comentario, h.estado, p.titulo 
        FROM historial h
        JOIN peliculas p ON h.pelicula_id = p.id
        WHERE h.usuario_id = ?
        ORDER BY h.fecha_visualizacion DESC
    ");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    return $stmt->get_result();
}


// Función para obtener favoritos y usar la API para los detalles de cada película
function obtenerFavoritos($usuario_id, $conn) {
    $stmt = $conn->prepare("SELECT movie_id FROM favoritos WHERE usuario_id = ?");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $favoritos = [];
    $api_key = '56ca5cf36846fb5c1465bf82478749b5';
    $image_base_url = 'https://image.tmdb.org/t/p/w500';

    while ($row = $result->fetch_assoc()) {
        $movie_id = $row['movie_id'];
        $url = "https://api.themoviedb.org/3/movie/$movie_id?api_key=$api_key&language=es-ES";
        $response = file_get_contents($url);
        
        if ($response !== FALSE) {
            $movie_data = json_decode($response, true);
            if ($movie_data && isset($movie_data['poster_path'])) {
                $favoritos[] = [
                    'id' => $movie_id,
                    'titulo' => $movie_data['title'],
                    'poster_path' => $image_base_url . $movie_data['poster_path']
                ];
            }
        }
    }

    return $favoritos;
}




// Función para obtener listas personalizadas
function obtenerListas($usuario_id, $conn) {
    $stmt = $conn->prepare("SELECT * FROM listas WHERE usuario_id = ?");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    return $stmt->get_result();
}

// Función para obtener estadísticas de visualización
function obtenerEstadisticas($usuario_id, $conn) {
    $stmt = $conn->prepare("SELECT COUNT(*) AS total_vistas, AVG(calificacion) AS calificacion_promedio FROM historial WHERE usuario_id = ?");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// Obtener datos
$historial = obtenerHistorialVisualizacion($usuario_id, $conn);
$favoritos = obtenerFavoritos($usuario_id, $conn);
$listas = obtenerListas($usuario_id, $conn);
$estadisticas = obtenerEstadisticas($usuario_id, $conn);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Perfil de Usuario</title>
    <link rel="stylesheet" href="../assets/css/perfil.css">
</head>
<body>
    <div class="perfil-container">
        <h1>Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre_usuario']); ?></h1>

        <!-- Sección de Estadísticas -->
        <section class="estadisticas">
            <h2>Estadísticas de Visualización</h2>
            <p>Total de Películas Vistas: <?php echo $estadisticas['total_vistas']; ?></p>
            <p>Calificación Promedio: <?php echo number_format($estadisticas['calificacion_promedio'], 1); ?> / 5</p>
        </section>

 <!-- Sección de Historial -->
<section class="historial">
    <h2>Historial de Visualización</h2>
    <?php if ($historial->num_rows > 0): ?>
        <ul class="historial-list">
            <?php while ($row = $historial->fetch_assoc()): ?>
                <li class="historial-item">
                    <h4><?php echo htmlspecialchars($row['titulo']); ?></h4>
                    <p><strong>Visto el:</strong> <?php echo $row['fecha_visualizacion'] !== '0000-00-00' ? $row['fecha_visualizacion'] : 'Fecha no disponible'; ?></p>
                    <p><strong>Estado:</strong> <?php echo !empty($row['estado']) ? htmlspecialchars($row['estado']) : 'No especificado'; ?></p>
                    <p><strong>Calificación:</strong> <?php echo $row['calificacion'] > 0 ? $row['calificacion'] . "/5" : 'No calificado'; ?></p>
                    <p><strong>Comentario:</strong> <?php echo !empty($row['comentario']) ? htmlspecialchars($row['comentario']) : 'Sin comentario'; ?></p>
                </li>
            <?php endwhile; ?>
        </ul>
    <?php else: ?>
        <p>No has visto ninguna película aún.</p>
    <?php endif; ?>
</section>



<!-- Sección de Favoritos -->
<section class="favoritos">
    <h2>Películas Favoritas</h2>
    <?php if (!empty($favoritos)): ?>
        <ul class="favoritos-list">
            <?php foreach ($favoritos as $movie): ?>
                <li class="favorito-item">
                    <img src="<?php echo $movie['poster_path']; ?>" alt="<?php echo htmlspecialchars($movie['titulo']); ?>" class="favorito-poster">
                    <span><?php echo htmlspecialchars($movie['titulo']); ?></span>
                    <a href="eliminar_favorito.php?id=<?php echo $movie['id']; ?>" class="btn-eliminar">Eliminar</a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>No tienes películas favoritas aún.</p>
    <?php endif; ?>
</section>

<!-- Formulario para crear una nueva lista personalizada -->
<section class="crear-lista mt-4">
    <h2>Crea una Nueva Lista</h2>
    <form action="crear_lista.php" method="POST">
        <input type="text" name="nombre" placeholder="Nombre de la lista" required class="form-control mb-2">
        <textarea name="descripcion" placeholder="Descripción de la lista (opcional)" class="form-control mb-2"></textarea>
        <select name="privacidad" class="form-control mb-2">
            <option value="privada">Privada</option>
            <option value="publica">Pública</option>
        </select>
        <button type="submit" class="btn btn-primary">Crear Lista</button>
    </form>
</section>


    <!-- Sección de Listas Personalizadas -->
<section class="listas mt-4">
    <h2>Mis Listas</h2>
    <?php if ($listas->num_rows > 0): ?>
        <ul>
            <?php while ($row = $listas->fetch_assoc()): ?>
                <li>
                    <strong><?php echo htmlspecialchars($row['nombre']); ?></strong> - <?php echo htmlspecialchars($row['descripcion']); ?><br>
                    <a href="ver_lista.php?id=<?php echo $row['id']; ?>">Ver Lista</a> |
                    <a href="agregar_pelicula_lista.php?id=<?php echo $row['id']; ?>">Agregar Películas</a>
                </li>
            <?php endwhile; ?>
        </ul>
    <?php else: ?>
        <p>No tienes listas personalizadas aún.</p>
    <?php endif; ?>
</section>

    </div>
</body>
</html>
