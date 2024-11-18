document.addEventListener('DOMContentLoaded', () => {
    const favoritosBtn = document.getElementById('favoritos-btn');

    if (favoritosBtn) {
        favoritosBtn.addEventListener('click', function () {
            const movieId = this.dataset.movieId;

            fetch('favoritos.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `movie_id=${movieId}`
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        this.classList.toggle('btn-danger');
                        this.classList.toggle('btn-secondary');
                        this.textContent = this.classList.contains('btn-danger') ? 'Eliminar de Favoritos' : 'Añadir a Favoritos';
                    }
                })
                .catch(error => console.error('Error:', error));
        });
    }
});
