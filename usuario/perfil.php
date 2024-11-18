<?php
session_start();

include_once '../includes/conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../auth/login2.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

// Manejar el formulario de edición del perfil
if (isset($_POST['actualizar_perfil'])) {
    $nombre_usuario = $_POST['nombre_usuario'];
    $correo = $_POST['correo'];
    $foto_perfil = $_FILES['foto_perfil'] ?? null;

    $directorio_destino = '../uploads/';
    if (!is_dir($directorio_destino)) {
        mkdir($directorio_destino, 0755, true);
    }

    $ruta_completa = null;
    if (!empty($foto_perfil['name'])) {
        $nombre_archivo = $usuario_id . '_' . basename($foto_perfil['name']);
        $ruta_completa = $directorio_destino . $nombre_archivo;

        if (!move_uploaded_file($foto_perfil['tmp_name'], $ruta_completa)) {
            echo "Error: No se pudo cargar la foto de perfil.";
            $ruta_completa = null;
        }
    }

    // Preparar consulta SQL
    if ($ruta_completa) {
        $stmt = $conn->prepare("UPDATE usuarios SET nombre_usuario = ?, correo = ?, foto_perfil = ? WHERE id = ?");
        $stmt->bind_param("sssi", $nombre_usuario, $correo, $ruta_completa, $usuario_id);
    } else {
        $stmt = $conn->prepare("UPDATE usuarios SET nombre_usuario = ?, correo = ? WHERE id = ?");
        $stmt->bind_param("ssi", $nombre_usuario, $correo, $usuario_id);
    }

    // Ejecutar consulta y redirigir
    if ($stmt->execute()) {
        $_SESSION['nombre_usuario'] = $nombre_usuario;
        $_SESSION['correo'] = $correo;
        if ($ruta_completa) {
            $_SESSION['foto_perfil'] = $ruta_completa;
        }
        header("Location: perfil.php?mensaje=perfil_actualizado");
        exit();
    } else {
        echo "Error: No se pudo actualizar el perfil.";
    }
}

// Funciones para obtener datos
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

function obtenerListas($usuario_id, $conn) {
    $stmt = $conn->prepare("SELECT * FROM listas WHERE usuario_id = ?");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    return $stmt->get_result();
}

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de Usuario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/perfil.css" rel="stylesheet">
</head>
<body class="bg-dark text-light">

 <!-- Barra de navegación -->
 <nav class="navbar navbar-dark bg-dark shadow-sm fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">Mi Perfil</a>
            <div class="dropdown">
                <a class="nav-link dropdown-toggle text-light" href="#" id="perfilDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="<?php echo $_SESSION['foto_perfil'] ?? '../assets/default-profile.png'; ?>" alt="Foto de perfil" class="rounded-circle" width="40">
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="#editar-perfil">Editar Perfil</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="../auth/logout.php">Cerrar Sesión</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5 pt-5">
        <!-- Sección de edición del perfil -->
        <section id="editar-perfil" class="mb-5">
            <div class="card bg-secondary shadow-sm">
                <div class="card-body">
                    <h2 class="card-title text-warning">Editar Perfil</h2>
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="nombre_usuario" class="form-label">Nombre de Usuario</label>
                            <input type="text" id="nombre_usuario" name="nombre_usuario" class="form-control" value="<?php echo htmlspecialchars($_SESSION['nombre_usuario']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="correo" class="form-label">Correo Electrónico</label>
                            <input type="email" id="correo" name="correo" class="form-control" value="<?php echo htmlspecialchars($_SESSION['correo']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="foto_perfil" class="form-label">Foto de Perfil</label>
                            <input type="file" id="foto_perfil" name="foto_perfil" class="form-control">
                        </div>
                        <button type="submit" name="actualizar_perfil" class="btn btn-warning">Guardar Cambios</button>
                    </form>
                </div>
            </div>
        </section>
    </div>
<!-- Contenido principal -->
<div class="container mt-5">
    <h1 class="text-center text-warning mb-4">Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre_usuario']); ?></h1>
      
    <!-- Estadísticas -->
        <section class="mb-5">
            <div class="card bg-secondary shadow">
                <div class="card-body">
                    <h2 class="card-title text-warning">Estadísticas de Visualización</h2>
                    <p>Total de Películas Vistas: <strong><?php echo $estadisticas['total_vistas']; ?></strong></p>
                    <div class="progress">
                        <div class="progress-bar bg-warning" role="progressbar" style="width: <?php echo min($estadisticas['calificacion_promedio'] * 20, 100); ?>%;">
                            <?php echo number_format($estadisticas['calificacion_promedio'], 1); ?> / 5
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Favoritos -->
        <section class="mb-5">
            <h2 class="text-warning">Películas Favoritas</h2>
            <?php if (!empty($favoritos)): ?>
                <div class="row g-3">
                    <?php foreach ($favoritos as $movie): ?>
                        <div class="col-md-3">
                            <div class="card bg-dark text-light shadow h-100">
                                <img src="<?php echo $movie['poster_path']; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($movie['titulo']); ?>">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($movie['titulo']); ?></h5>
                                    <button class="btn btn-danger btn-sm w-100 eliminar-favorito" data-id="<?php echo $movie['id']; ?>">
                                        Eliminar
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>No tienes películas favoritas aún.</p>
            <?php endif; ?>
        </section>

        <!-- Historial -->
        <section class="mb-5">
            <h2 class="text-warning">Historial de Visualización</h2>
            <?php if ($historial->num_rows > 0): ?>
                <div class="list-group shadow-sm">
                    <?php while ($row = $historial->fetch_assoc()): ?>
                        <div class="list-group-item bg-dark text-light">
                            <h5 class="mb-1"><?php echo htmlspecialchars($row['titulo']); ?></h5>
                            <small class="text-muted">Visto el: <?php echo $row['fecha_visualizacion'] !== '0000-00-00' ? $row['fecha_visualizacion'] : 'Fecha no disponible'; ?></small>
                            <p><strong>Estado:</strong> <?php echo htmlspecialchars($row['estado']); ?></p>
                            <p><strong>Calificación:</strong> <?php echo $row['calificacion'] > 0 ? $row['calificacion'] . "/5" : 'No calificado'; ?></p>
                            <p><strong>Comentario:</strong> <?php echo htmlspecialchars($row['comentario']); ?></p>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p>No has visto ninguna película aún.</p>
            <?php endif; ?>
        </section>

        <!-- Crear nueva lista -->
        <section class="mb-5">
            <h2 class="text-warning">Crea una Nueva Lista</h2>
            <div class="card bg-secondary shadow-sm">
                <div class="card-body">
                    <form action="crear_lista.php" method="POST">
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre de la Lista</label>
                            <input type="text" id="nombre" name="nombre" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <textarea id="descripcion" name="descripcion" class="form-control"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="privacidad" class="form-label">Privacidad</label>
                            <select id="privacidad" name="privacidad" class="form-select">
                                <option value="privada">Privada</option>
                                <option value="publica">Pública</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Crear Lista</button>
                    </form>
                </div>
            </div>
        </section>

        <!-- Listas personalizadas -->
        <section class="mb-5">
            <h2 class="text-warning">Mis Listas</h2>
            <?php if ($listas->num_rows > 0): ?>
                <div class="list-group shadow-sm">
                    <?php while ($row = $listas->fetch_assoc()): ?>
                        <div class="list-group-item bg-dark text-light d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-1"><?php echo htmlspecialchars($row['nombre']); ?></h5>
                                <p class="mb-1"><?php echo htmlspecialchars($row['descripcion']); ?></p>
                            </div>
                            <div>
                                <a href="ver_lista.php?id=<?php echo $row['id']; ?>" class="btn btn-info btn-sm">Ver Lista</a>
                                <a href="agregar_pelicula_lista.php?id=<?php echo $row['id']; ?>" class="btn btn-success btn-sm">Agregar Películas</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p>No tienes listas personalizadas aún.</p>
            <?php endif; ?>
        </section>
    </div>
<!-- Modal para editar perfil -->
<div class="modal fade" id="editarPerfilModal" tabindex="-1" aria-labelledby="editarPerfilLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark text-light">
            <div class="modal-header">
                <h5 class="modal-title text-warning" id="editarPerfilLabel">Editar Perfil</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nombreUsuario" class="form-label">Nombre de Usuario</label>
                        <input type="text" class="form-control" id="nombreUsuario" name="nombre_usuario" value="<?php echo isset($_SESSION['nombre_usuario']) ? htmlspecialchars($_SESSION['nombre_usuario']) : ''; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="correo" class="form-label">Correo Electrónico</label>
                        <input type="email" class="form-control" id="correo" name="correo" value="<?php echo isset($_SESSION['correo']) ? htmlspecialchars($_SESSION['correo']) : ''; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="fotoPerfil" class="form-label">Foto de Perfil</label>
                        <input type="file" class="form-control" id="fotoPerfil" name="foto_perfil">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" name="actualizar_perfil">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
