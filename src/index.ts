import './styles.css';
document.addEventListener("DOMContentLoaded", (event) => {
    
    function toggleMenu() {
        document.getElementById("hamburger-menu")?.classList.toggle("active");
        document.getElementById("overlay")?.classList.toggle("active");
        document.getElementById("menu-items")?.classList.toggle("active");
    }
    
    document.getElementById("hamburger-menu")!.addEventListener('click', function() {
        toggleMenu();
    });

    const menuItems = document.querySelectorAll('.menu-item');
    menuItems.forEach(item => {
        item.addEventListener('click', (event) => {
            toggleMenu();
        });
    });

    const servicePlates = document.querySelectorAll('.service-plate');

    servicePlates.forEach(servicePlate => {
        let overlay = servicePlate.querySelector('.overlay')
        servicePlate.addEventListener('mouseenter', () => {
            overlay?.classList.toggle("show");
        });

        servicePlate.addEventListener('mouseleave', () => {
            overlay?.classList.toggle("show");
        });
    });

});