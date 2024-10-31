document.addEventListener("DOMContentLoaded", function() {
  const slidePage = document.querySelector(".slide-page");
  const nextBtnFirst = document.querySelector(".firstNext");
  const prevBtnSec = document.querySelector(".prev-1");
  const nextBtnSec = document.querySelector(".next-1");
  const prevBtnFourth = document.querySelector(".prev-3");
  const submitBtn = document.querySelector(".submit");
  const progressText = document.querySelectorAll(".step p");
  const progressCheck = document.querySelectorAll(".step .check");
  const bullet = document.querySelectorAll(".step .bullet");
  let current = 1;

  // Verificar que los elementos existen antes de añadir eventos
  if (nextBtnFirst) {
      nextBtnFirst.addEventListener("click", function(event) {
          event.preventDefault();
          slidePage.style.marginLeft = "-25%";
          bullet[current - 1].classList.add("active");
          progressCheck[current - 1].classList.add("active");
          progressText[current - 1].classList.add("active");
          current += 1;
      });
  }

  if (nextBtnSec) {
      nextBtnSec.addEventListener("click", function(event) {
          event.preventDefault();
          slidePage.style.marginLeft = "-50%";
          bullet[current - 1].classList.add("active");
          progressCheck[current - 1].classList.add("active");
          progressText[current - 1].classList.add("active");
          current += 1;
      });
  }

  if (submitBtn) {
      submitBtn.addEventListener("click", function() {
          bullet[current - 1].classList.add("active");
          progressCheck[current - 1].classList.add("active");
          progressText[current - 1].classList.add("active");
          current += 1;
          setTimeout(function() {
              location.reload();
          }, 800);
      });
  }

  if (prevBtnSec) {
      prevBtnSec.addEventListener("click", function(event) {
          event.preventDefault();
          slidePage.style.marginLeft = "0%";
          bullet[current - 2].classList.remove("active");
          progressCheck[current - 2].classList.remove("active");
          progressText[current - 2].classList.remove("active");
          current -= 1;
      });
  }

  if (prevBtnFourth) {
      prevBtnFourth.addEventListener("click", function(event) {
          event.preventDefault();
          slidePage.style.marginLeft = "-25%";
          bullet[current - 2].classList.remove("active");
          progressCheck[current - 2].classList.remove("active");
          progressText[current - 2].classList.remove("active");
          current -= 1;
      });
  }
});
