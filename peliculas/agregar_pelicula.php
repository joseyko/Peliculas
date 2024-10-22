<?php 

ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario_id'])) {
    // Si no está autenticado, redirigir al inicio de sesión
    header("Location: auth/login.php");
    exit();
}

include 'includes/conexion.php'; // Ajusta la ruta para incluir conexion.php

// Manejar el envío del formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titulo = $_POST['titulo'];
    $descripcion = $_POST['descripcion'];
    $fecha_estreno = $_POST['fecha_estreno'];

    // Procesar la imagen
    $imagen = $_FILES['imagen'];
    $ruta_imagen = 'imagenes/' . basename($imagen['name']); // Ruta donde se guardará la imagen

    // Depurar la carga de la imagen
    var_dump($_FILES['imagen']); // Verificar que la imagen se esté cargando

    // Mover la imagen a la carpeta deseada
    if (move_uploaded_file($imagen['tmp_name'], $ruta_imagen)) {
        // Preparar la consulta para insertar la película
        $stmt = $conn->prepare("INSERT INTO peliculas (titulo, descripcion, fecha_estreno, imagen) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $titulo, $descripcion, $fecha_estreno, $ruta_imagen);
        $stmt->execute();
        
        // Obtener el ID de la nueva película
        $pelicula_id = $conn->insert_id;

        // Insertar los géneros relacionados
        $generos = $_POST['generos']; // Array de géneros seleccionados
        foreach ($generos as $genero_id) {
            $stmt = $conn->prepare("INSERT INTO pelicula_genero (pelicula_id, genero_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $pelicula_id, $genero_id);
            $stmt->execute();
        }

        // Redirigir a la lista de películas con un mensaje de éxito
        header("Location: peliculas.php?mensaje=Película agregada con éxito");
        exit();
    } else {
        echo "<div class='alert alert-danger'>Error al subir la imagen " . $imagen['error'] . "</div>";
    }
}

// Obtener géneros para el formulario
$sql_generos = "SELECT * FROM generos";
$resultado_generos = $conn->query($sql_generos);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Película</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-4">
        <h2 class="text-center">Agregar Nueva Película</h2>
        
        <form action="agregar_pelicula.php" method="POST" enctype="multipart/form-data" novalidate>
            <div class="form-group">
                <label for="titulo">Título</label>
                <input type="text" name="titulo" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="descripcion">Descripción</label>
                <textarea name="descripcion" class="form-control" rows="4" required></textarea>
            </div>
            <div class="form-group">
                <label for="fecha_estreno">Fecha de Estreno</label>
                <input type="date" name="fecha_estreno" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="imagen">Cargar Imagen</label>
                <input type="file" name="imagen" class="form-control" accept="image/*" required>
            </div>
            <div class="form-group">
                <label for="generos">Géneros</label>
                <select name="generos[]" class="form-control" multiple required>
                    <?php while ($genero = $resultado_generos->fetch_assoc()): ?>
                        <option value="<?php echo $genero['id']; ?>"><?php echo htmlspecialchars($genero['nombre']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-success btn-block">Agregar Película</button>
        </form>

        <div class="text-right mt-3">
            <a href="peliculas.php" class="btn btn-primary">Volver a la Lista de Películas</a>
        </div>
    </div>
</body>
</html>
