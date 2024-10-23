<?php 
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario_id'])) {
    // Si no está autenticado, redirigir al inicio de sesión
    header("Location: auth/login.php");
    exit();
}

// Corregir la ruta del archivo de conexión
include '../includes/conexion.php'; // Ajusta la ruta correcta

// Obtener la búsqueda, si existe
$buscar = isset($_GET['buscar']) ? $_GET['buscar'] : '';

// Obtener las películas junto con sus géneros y puntuaciones
$sql = "
    SELECT p.id, p.titulo, p.descripcion, p.fecha_estreno, p.imagen,
           GROUP_CONCAT(g.nombre SEPARATOR ', ') AS generos,
           COALESCE(AVG(pu.puntuacion), 0) AS puntuacion_media,
           COALESCE(COUNT(pu.puntuacion), 0) AS total_puntuaciones
    FROM peliculas p
    LEFT JOIN pelicula_genero pg ON p.id = pg.pelicula_id
    LEFT JOIN generos g ON pg.genero_id = g.id
    LEFT JOIN puntuaciones pu ON p.id = pu.pelicula_id
    WHERE p.titulo LIKE ? OR p.descripcion LIKE ?
    GROUP BY p.id
";

$buscar_param = '%' . $buscar . '%';
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $buscar_param, $buscar_param);
$stmt->execute();
$resultado = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Películas</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .card {
            margin-bottom: 20px;
        }
        .card-title {
            font-size: 1.5rem;
            font-weight: bold;
        }
        .card-text {
            font-size: 1rem;
        }
        .puntuacion-container {
            margin-top: 15px;
        }
        .card-img-top {
            max-width: 100%; /* Asegura que la imagen no exceda el ancho del contenedor */
            max-height: 200px; /* Ajustar la altura máxima de la imagen */
            object-fit: contain; /* Mantener las proporciones de la imagen */
        }
        .imagen-error {
            color: red;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h2 class="text-center">Lista de Películas</h2>
        
        <p class="text-center">Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre_usuario']); ?>. Aquí está la lista de películas.</p>
        
        <div class="text-right mb-3">
            <a href="../auth/logout.php" class="btn btn-danger">Cerrar Sesión</a>
        </div>

        <?php if (isset($_GET['mensaje'])): ?>
            <div class="alert alert-success mt-3">
                <?php echo htmlspecialchars($_GET['mensaje']); ?>
            </div>
        <?php endif; ?>

        <!-- Formulario de búsqueda -->
        <div class="text-center mb-3">
            <form action="peliculas.php" method="GET" class="form-inline">
                <input type="text" name="buscar" class="form-control mr-2" placeholder="Buscar películas..." value="<?php echo htmlspecialchars($buscar); ?>">
                <button type="submit" class="btn btn-primary">Buscar</button>
            </form>
        </div>

        <div class="row">
            <?php while ($pelicula = $resultado->fetch_assoc()): ?>
                <div class="col-md-4">
                    <div class="card">
                        <?php
                        // Generar la ruta completa de la imagen
                        $imagenRuta = '../assets/' . htmlspecialchars($pelicula['imagen']);
                        
                        // Verificar si el archivo de imagen existe
                        if (file_exists($imagenRuta) && !empty($pelicula['imagen'])) {
                            echo '<img src="' . $imagenRuta . '" alt="' . htmlspecialchars($pelicula['titulo']) . '" class="card-img-top">';
                        } else {
                            echo '<img src="../assets/imagenes/default.png" alt="Imagen no disponible" class="card-img-top">'; // Imagen por defecto
                            echo '<p class="imagen-error">Imagen no encontrada para: ' . htmlspecialchars($pelicula['titulo']) . '</p>';
                        }
                        ?>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($pelicula['titulo']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars($pelicula['descripcion']); ?></p>
                            <p class="card-text"><strong>Géneros:</strong> <?php echo htmlspecialchars($pelicula['generos']); ?></p>
                            <p class="card-text"><strong>Fecha de Estreno:</strong> <?php echo htmlspecialchars($pelicula['fecha_estreno']); ?></p>
                            <p class="card-text"><strong>Puntuación Media:</strong> <?php echo round($pelicula['puntuacion_media'], 2); ?> (<?php echo $pelicula['total_puntuaciones']; ?> votos)</p>

                            <div class="puntuacion-container">
                                <form action="puntuar.php" method="POST">
                                    <input type="hidden" name="pelicula_id" value="<?php echo $pelicula['id']; ?>">
                                    <div class="form-group">
                                        <label for="puntuacion">Puntuación (1-5):</label>
                                        <input type="number" name="puntuacion" class="form-control" min="1" max="5" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Puntuar</button>
                                </form>
                                <!-- Enlace para editar puntuación -->
                                <a href="editar_puntuacion.php?pelicula_id=<?php echo $pelicula['id']; ?>" class="btn btn-warning mt-2">Editar Puntuación</a>
                                <!-- Enlace para editar película -->
                                <a href="editar_peliculas.php?id=<?php echo $pelicula['id']; ?>" class="btn btn-warning">Editar</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
            
        </div>

        <?php
        $resultado->close();
        $conn->close();
        ?>
    </div>
</body>
</html>
