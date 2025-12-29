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

// Supported social media platforms
const SOCIAL_PLATFORMS = ['facebook', 'twitter', 'linkedin', 'instagram'];

// Google Analytics event tracking helper
function trackEvent(eventName: string, eventCategory: string, eventLabel: string, eventValue?: number): void {
    if (typeof window !== 'undefined' && typeof window.gtag === 'function') {
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

// Helper to sanitize text for tracking labels
function sanitizeTextForTracking(text: string): string {
    return text.toLowerCase().replace(/\s+/g, '_');
}

// Helper to detect social media platform from URL
function getSocialPlatform(url: string): string {
    return SOCIAL_PLATFORMS.find(platform => url.includes(platform)) || 'unknown';
}

// Contact form modal functions
function openContactModal(): void {
    const modal = document.getElementById('contact-form-modal');
    if (modal) {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden'; // Prevent background scrolling
    }
}

function closeContactModal(): void {
    const modal = document.getElementById('contact-form-modal');
    if (modal) {
        modal.classList.add('hidden');
        document.body.style.overflow = ''; // Restore scrolling
        
        // Reset form
        const form = document.getElementById('contact-form') as HTMLFormElement;
        if (form) {
            form.reset();
        }
        
        // Hide any messages
        const messageDiv = document.getElementById('form-message');
        if (messageDiv) {
            messageDiv.classList.add('hidden');
        }
    }
}

function showFormMessage(message: string, isError: boolean = false): void {
    const messageDiv = document.getElementById('form-message');
    if (messageDiv) {
        messageDiv.textContent = message;
        messageDiv.classList.remove('hidden', 'bg-green-100', 'text-green-800', 'bg-red-100', 'text-red-800');
        
        if (isError) {
            messageDiv.classList.add('bg-red-100', 'text-red-800');
        } else {
            messageDiv.classList.add('bg-green-100', 'text-green-800');
        }
    }
}

function setSubmitButtonLoading(isLoading: boolean): void {
    const submitButton = document.getElementById('submit-contact-form') as HTMLButtonElement;
    const submitText = document.getElementById('submit-text');
    const submitSpinner = document.getElementById('submit-spinner');
    
    if (submitButton && submitText && submitSpinner) {
        submitButton.disabled = isLoading;
        
        if (isLoading) {
            submitText.classList.add('hidden');
            submitSpinner.classList.remove('hidden');
        } else {
            submitText.classList.remove('hidden');
            submitSpinner.classList.add('hidden');
        }
    }
}

async function handleContactFormSubmit(event: Event): Promise<void> {
    event.preventDefault();
    
    const form = event.target as HTMLFormElement;
    const formData = new FormData(form);
    
    // Convert FormData to JSON
    const data: { [key: string]: string } = {};
    formData.forEach((value, key) => {
        data[key] = value.toString();
    });
    
    // Show loading state
    setSubmitButtonLoading(true);
    showFormMessage('Sending your message...', false);
    
    try {
        const response = await fetch('/contact-handler.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showFormMessage(result.message, false);
            form.reset();
            
            // Close modal after 2 seconds
            setTimeout(() => {
                closeContactModal();
            }, 2000);
        } else {
            showFormMessage(result.message || 'An error occurred. Please try again.', true);
        }
    } catch (error) {
        console.error('Error submitting form:', error);
        showFormMessage('Failed to send message. Please try again or contact us directly.', true);
    } finally {
        setSubmitButtonLoading(false);
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
    
    // Contact form modal handlers
    document.getElementById('open-contact-modal')?.addEventListener('click', openContactModal);
    document.getElementById('close-contact-modal')?.addEventListener('click', closeContactModal);
    
    // Close modal when clicking outside of it
    document.getElementById('contact-form-modal')?.addEventListener('click', (e) => {
        if (e.target === e.currentTarget) {
            closeContactModal();
        }
    });
    
    // Handle contact form submission
    document.getElementById('contact-form')?.addEventListener('submit', handleContactFormSubmit);
    
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
            trackEvent('click', 'navigation', `menu_${sanitizeTextForTracking(linkText)}`);
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

    // Track Contact Section CTAs - without PII
    const phoneLinks = document.querySelectorAll('a[href^="tel:"]');
    phoneLinks.forEach((link) => {
        link.addEventListener('click', () => {
            trackEvent('click', 'contact', 'phone_clicked');
        });
    });

    const emailLinks = document.querySelectorAll('a[href^="mailto:"]');
    emailLinks.forEach((link) => {
        link.addEventListener('click', () => {
            trackEvent('click', 'contact', 'email_clicked');
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
    const socialSelector = SOCIAL_PLATFORMS.map(platform => `footer a[href*="${platform}"]`).join(', ');
    const socialLinks = document.querySelectorAll(socialSelector);
    socialLinks.forEach((link) => {
        link.addEventListener('click', () => {
            const href = link.getAttribute('href') || '';
            const platform = getSocialPlatform(href);
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