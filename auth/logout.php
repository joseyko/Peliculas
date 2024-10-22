<?php
session_start(); // Iniciar la sesión

// Destruir la sesión
session_unset(); // Libera todas las variables de sesión
session_destroy(); // Destruye la sesión

// Redirigir al inicio de sesión
header("Location: ../auth/login.php"); // Ajusta la ruta si es necesario
exit(); // Asegúrate de que el script se detenga aquí
?>
