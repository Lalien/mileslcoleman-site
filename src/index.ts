import './styles.css';
const Parallax       = require('parallax-js')
document.addEventListener("DOMContentLoaded", (event) => {
    var hamburgerMenu = document.getElementById("hamburger-menu");
    hamburgerMenu!.addEventListener('click', function() {
        this!.classList.toggle("active");
        document.getElementById("overlay")?.classList.toggle("active");
    });
});