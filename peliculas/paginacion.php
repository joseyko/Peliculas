<?php
function mostrarPaginacion($pagina, $total_paginas, $genero_seleccionado = '', $solo_favoritos = false) {
    echo '<div class="d-flex justify-content-center mt-4">';
    
    // Botón para ir a la primera página
    if ($pagina > 1) {
        echo '<a href="?pagina=1&genero=' . urlencode($genero_seleccionado) . '&solo_favoritos=' . ($solo_favoritos ? 1 : 0) . '" class="btn btn-secondary mr-2">Primera</a>';
    }

    // Botón para retroceder varias páginas
    if ($pagina > 5) {
        echo '<a href="?pagina=' . ($pagina - 5) . '&genero=' . urlencode($genero_seleccionado) . '&solo_favoritos=' . ($solo_favoritos ? 1 : 0) . '" class="btn btn-secondary mr-2">&laquo; -5</a>';
    }

    // Botón para ir a la página anterior
    if ($pagina > 1) {
        echo '<a href="?pagina=' . ($pagina - 1) . '&genero=' . urlencode($genero_seleccionado) . '&solo_favoritos=' . ($solo_favoritos ? 1 : 0) . '" class="btn btn-secondary mr-2">Anterior</a>';
    }

    echo "<span>Página $pagina de $total_paginas</span>";

    // Botón para ir a la página siguiente
    if ($pagina < $total_paginas) {
        echo '<a href="?pagina=' . ($pagina + 1) . '&genero=' . urlencode($genero_seleccionado) . '&solo_favoritos=' . ($solo_favoritos ? 1 : 0) . '" class="btn btn-secondary ml-2">Siguiente</a>';
    }

    // Botón para avanzar varias páginas
    if ($pagina < $total_paginas - 5) {
        echo '<a href="?pagina=' . ($pagina + 5) . '&genero=' . urlencode($genero_seleccionado) . '&solo_favoritos=' . ($solo_favoritos ? 1 : 0) . '" class="btn btn-secondary ml-2">+5 &raquo;</a>';
    }

    // Botón para ir a la última página
    if ($pagina < $total_paginas) {
        echo '<a href="?pagina=' . $total_paginas . '&genero=' . urlencode($genero_seleccionado) . '&solo_favoritos=' . ($solo_favoritos ? 1 : 0) . '" class="btn btn-secondary ml-2">Última</a>';
    }

    echo '</div>';
}
?>
