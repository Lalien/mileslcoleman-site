import './styles.css';

// Extend window interface to include gtag
declare global {
    interface Window {
        gtag?: (...args: any[]) => void;
        dataLayer?: any[];
    }
}

// Cookie utility functions
function getCookie(name: string): string | null {
    const nameEQ = encodeURIComponent(name) + "=";
    const ca = document.cookie.split(';');
    for (let i = 0; i < ca.length; i++) {
        let c = ca[i];
        while (c.charAt(0) === ' ') c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) === 0) return decodeURIComponent(c.substring(nameEQ.length, c.length));
    }
    return null;
}

function setCookie(name: string, value: string, days: number): void {
    let expires = "";
    if (days > 0) {
        const date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        expires = "; expires=" + date.toUTCString();
    }
    document.cookie = encodeURIComponent(name) + "=" + encodeURIComponent(value || "") + expires + "; path=/";
}

// Google Analytics consent management
function updateGAConsent(granted: boolean): void {
    if (typeof window.gtag === 'function') {
        window.gtag('consent', 'update', {
            'analytics_storage': granted ? 'granted' : 'denied'
        });
    }
}

document.addEventListener("DOMContentLoaded", (event) => {
    
    // Cookie consent modal logic
    const cookieConsent = getCookie('cookieConsent');
    const modal = document.getElementById('cookie-consent-modal');
    
    if (!cookieConsent && modal) {
        // Show modal if consent hasn't been given
        modal.classList.remove('hidden');
    } else if (cookieConsent === 'accepted') {
        // User previously accepted - grant analytics consent
        updateGAConsent(true);
    }
    
    // Accept button handler
    document.getElementById('cookie-accept')?.addEventListener('click', () => {
        setCookie('cookieConsent', 'accepted', 365);
        updateGAConsent(true);
        modal?.classList.add('hidden');
    });
    
    // Decline button handler
    document.getElementById('cookie-decline')?.addEventListener('click', () => {
        setCookie('cookieConsent', 'declined', 365);
        updateGAConsent(false);
        modal?.classList.add('hidden');
    });
    
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