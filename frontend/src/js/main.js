document.addEventListener("DOMContentLoaded", () => {
    console.log("Página cargada sin errores.");
});

const toggleButton = document.querySelector('.menu-toggle');
const navLinks = document.querySelector('.nav-links');

toggleButton.addEventListener('click', () => {
    navLinks.classList.toggle('active');
});