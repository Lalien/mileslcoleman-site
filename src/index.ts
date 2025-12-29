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
        try {
            window.gtag('consent', 'update', {
                'analytics_storage': granted ? 'granted' : 'denied'
            });
        } catch (error) {
            console.error('Failed to update Google Analytics consent:', error);
        }
    }
}

// Google Analytics event tracking helper
function trackEvent(eventName: string, eventCategory: string, eventLabel: string, eventValue?: number): void {
    if (typeof window.gtag === 'function') {
        try {
            const eventParams: { [key: string]: any } = {
                'event_category': eventCategory,
                'event_label': eventLabel
            };
            if (eventValue !== undefined) {
                eventParams['value'] = eventValue;
            }
            window.gtag('event', eventName, eventParams);
        } catch (error) {
            console.error('Failed to track event:', error);
        }
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
            const target = event.currentTarget as HTMLElement;
            const linkText = target.textContent?.trim() || 'Unknown';
            trackEvent('click', 'navigation', `menu_${linkText.toLowerCase().replace(/\s+/g, '_')}`);
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

    // Track Hero CTA button
    const heroCtaButton = document.querySelector('a[href="#services"]');
    if (heroCtaButton && heroCtaButton.closest('section')?.querySelector('h1')) {
        heroCtaButton.addEventListener('click', () => {
            trackEvent('click', 'cta', 'hero_learn_more');
        });
    }

    // Track Contact Section CTAs
    const phoneLinks = document.querySelectorAll('a[href^="tel:"]');
    phoneLinks.forEach((link) => {
        link.addEventListener('click', () => {
            const phoneNumber = (link as HTMLElement).textContent?.trim() || 'Unknown';
            trackEvent('click', 'contact', `phone_${phoneNumber.replace(/\D/g, '')}`);
        });
    });

    const emailLinks = document.querySelectorAll('a[href^="mailto:"]');
    emailLinks.forEach((link) => {
        link.addEventListener('click', () => {
            const email = link.getAttribute('href')?.replace('mailto:', '') || 'Unknown';
            trackEvent('click', 'contact', `email_${email.replace(/[^a-zA-Z0-9]/g, '_')}`);
        });
    });

    // Track "View Our Services" button in contact section
    const contactServiceButton = document.querySelector('#contact a[href="#services"]');
    if (contactServiceButton) {
        contactServiceButton.addEventListener('click', () => {
            trackEvent('click', 'cta', 'contact_view_services');
        });
    }

    // Track Footer Social Media Links
    const socialLinks = document.querySelectorAll('footer a[href*="facebook"], footer a[href*="twitter"], footer a[href*="linkedin"], footer a[href*="instagram"]');
    socialLinks.forEach((link) => {
        link.addEventListener('click', () => {
            const href = link.getAttribute('href') || '';
            let platform = 'unknown';
            if (href.includes('facebook')) platform = 'facebook';
            else if (href.includes('twitter')) platform = 'twitter';
            else if (href.includes('linkedin')) platform = 'linkedin';
            else if (href.includes('instagram')) platform = 'instagram';
            trackEvent('click', 'social', `footer_${platform}`);
        });
    });

    // Track Footer Quick Links
    const footerQuickLinks = document.querySelectorAll('footer a[href^="#"]');
    footerQuickLinks.forEach((link) => {
        link.addEventListener('click', () => {
            const href = link.getAttribute('href') || '';
            const section = href.replace('#', '');
            trackEvent('click', 'navigation', `footer_${section}`);
        });
    });

});