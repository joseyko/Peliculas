<?php
// Dado que ambos archivos están en la carpeta 'includes', la ruta es directa
include 'conexion.php'; 

// Verificar si la conexión fue exitosa
if ($conn) {
    echo "Conexión exitosa a la base de datos";
} else {
    echo "Error de conexión: " . $conn->connect_error;
}
?>
