document.addEventListener("DOMContentLoaded", function () {
    // Confirmación para agregar o quitar de favoritos
    const favoritosForms = document.querySelectorAll("form[action='favoritos.php']");

    favoritosForms.forEach(form => {
        form.addEventListener("submit", function (event) {
            event.preventDefault();
            const isAdding = this.querySelector("button").textContent.includes("Agregar");
            const confirmMessage = isAdding ? "¿Deseas agregar esta película a tus favoritos?" : "¿Deseas eliminar esta película de tus favoritos?";

            if (confirm(confirmMessage)) {
                this.submit();
            }
        });
    });

    // Funcionalidad del carrusel de "Películas Destacadas"
    const carouselSlide = document.querySelector(".carousel-slide");
    const prevButton = document.querySelector(".carousel-controls .prev");
    const nextButton = document.querySelector(".carousel-controls .next");
    const scrollStep = 300; // Ajusta este valor para la cantidad de desplazamiento

    nextButton.addEventListener("click", () => {
        carouselSlide.scrollBy({
            left: scrollStep,
            behavior: "smooth"
        });
    });

    prevButton.addEventListener("click", () => {
        carouselSlide.scrollBy({
            left: -scrollStep,
            behavior: "smooth"
        });
    });

    // Desplazamiento automático del carrusel
    function autoScroll() {
        if (carouselSlide.scrollLeft < (carouselSlide.scrollWidth - carouselSlide.clientWidth)) {
            carouselSlide.scrollBy({
                left: scrollStep,
                behavior: "smooth"
            });
        } else {
            carouselSlide.scrollTo({ left: 0, behavior: "smooth" });
        }
    }

    // Configurar desplazamiento automático cada 3 segundos
    setInterval(autoScroll, 3000); // Cambia de imagen cada 3 segundos

    // Funcionalidad del botón "Volver Arriba"
    const volverArribaButton = document.createElement("button");
    volverArribaButton.id = "volver-arriba";
    volverArribaButton.title = "Volver Arriba";
    volverArribaButton.innerHTML = "&#8679;";
    document.body.appendChild(volverArribaButton);

    window.addEventListener("scroll", () => {
        if (window.scrollY > 200) {
            volverArribaButton.style.display = "block";
        } else {
            volverArribaButton.style.display = "none";
        }
    });

    volverArribaButton.addEventListener("click", () => {
        window.scrollTo({
            top: 0,
            behavior: "smooth"
        });
    });
});
