<?php 
session_start();
include_once '../includes/conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../auth/login2.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$lista_id = $_GET['id'] ?? null;

if (!$lista_id) {
    echo "Lista no encontrada.";
    exit();
}

// Obtener detalles de la lista
$stmt_lista = $conn->prepare("SELECT nombre, descripcion, privacidad FROM listas WHERE id = ? AND usuario_id = ?");
$stmt_lista->bind_param("ii", $lista_id, $usuario_id);
$stmt_lista->execute();
$lista = $stmt_lista->get_result()->fetch_assoc();
$stmt_lista->close();

if (!$lista) {
    echo "Lista no encontrada o no tienes permiso para verla.";
    exit();
}

// Obtener IDs de películas en la lista
$stmt_peliculas = $conn->prepare("SELECT pelicula_id FROM peliculas_en_lista WHERE lista_id = ?");
$stmt_peliculas->bind_param("i", $lista_id);
$stmt_peliculas->execute();
$resultado_peliculas = $stmt_peliculas->get_result();
$stmt_peliculas->close();

// API y base de imágenes
$api_key = '56ca5cf36846fb5c1465bf82478749b5';
$image_base_url = 'https://image.tmdb.org/t/p/w500';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($lista['nombre']); ?></title>
    <link rel="stylesheet" href="../assets/css/perfil.css">
</head>
<body>
    <div class="lista-detalle-container">
        <h1><?php echo htmlspecialchars($lista['nombre']); ?></h1>
        <p><?php echo htmlspecialchars($lista['descripcion']); ?></p>

        <?php if ($resultado_peliculas->num_rows > 0): ?>
            <div class="lista-peliculas">
                <?php while ($row = $resultado_peliculas->fetch_assoc()): 
                    $pelicula_id = $row['pelicula_id'];
                    $url = "https://api.themoviedb.org/3/movie/$pelicula_id?api_key=$api_key&language=es-ES";
                    $movie_data = json_decode(file_get_contents($url), true);

                    if ($movie_data): ?>
                        <div class="pelicula-item">
                            <img src="<?php echo $image_base_url . $movie_data['poster_path']; ?>" alt="<?php echo htmlspecialchars($movie_data['title']); ?>" class="pelicula-poster">
                            <p><strong><?php echo htmlspecialchars($movie_data['title']); ?></strong></p>
                            <p>Año: <?php echo htmlspecialchars($movie_data['release_date']); ?></p>
                            <p>Género: <?php echo htmlspecialchars(implode(", ", array_column($movie_data['genres'], 'name'))); ?></p>
                        </div>
                    <?php endif; ?>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p>No hay películas en esta lista.</p>
        <?php endif; ?>
    </div>
</body>
</html>
