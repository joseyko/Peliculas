<?php
// funciones.php
include '../includes/conexion.php';

function obtenerGeneros($api_key) {
    $url = "https://api.themoviedb.org/3/genre/movie/list?api_key=$api_key&language=es-ES";
    $response = file_get_contents($url);
    return json_decode($response, true)['genres'];
}

function obtenerPeliculasPopulares($api_key, $pagina, $genero = null) {
    $url = "https://api.themoviedb.org/3/movie/popular?api_key=$api_key&language=es-ES&page=$pagina";
    if ($genero) {
        $url .= "&with_genres=$genero";
    }
    $response = file_get_contents($url);
    return json_decode($response, true);
}

function esFavorito($conn, $usuario_id, $movie_id) {
    $stmt = $conn->prepare("SELECT id FROM favoritos WHERE usuario_id = ? AND movie_id = ?");
    $stmt->bind_param("ii", $usuario_id, $movie_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}


