/**
 * NEXAR - Main Application TypeScript
 * Core functionality and initialization
 */

// Types and Interfaces
interface NavItem {
    label: string;
    href: string;
}

interface Stat {
    value: string;
    label: string;
}

interface Feature {
    icon: string;
    title: string;
    description: string;
}

interface Testimonial {
    quote: string;
    author: string;
    role: string;
    avatar: string;
}

interface PricingTier {
    name: string;
    price: number;
    description: string;
    features: string[];
    highlighted?: boolean;
}

/**
 * Application State
 */
class AppState {
    private static instance: AppState;
    private state: Map<string, unknown> = new Map();

    private constructor() {}

    static getInstance(): AppState {
        if (!AppState.instance) {
            AppState.instance = new AppState();
        }
        return AppState.instance;
    }

    set<T>(key: string, value: T): void {
        this.state.set(key, value);
    }

    get<T>(key: string): T | undefined {
        return this.state.get(key) as T | undefined;
    }

    has(key: string): boolean {
        return this.state.has(key);
    }

    delete(key: string): boolean {
        return this.state.delete(key);
    }
}

/**
 * Utility Functions
 */
const Utils = {
    /**
     * Debounce function calls
     */
    debounce<T extends (...args: unknown[]) => unknown>(
        func: T,
        wait: number
    ): (...args: Parameters<T>) => void {
        let timeout: ReturnType<typeof setTimeout> | null = null;
        return (...args: Parameters<T>) => {
            if (timeout) clearTimeout(timeout);
            timeout = setTimeout(() => func(...args), wait);
        };
    },

    /**
     * Throttle function calls
     */
    throttle<T extends (...args: unknown[]) => unknown>(
        func: T,
        limit: number
    ): (...args: Parameters<T>) => void {
        let inThrottle = false;
        return (...args: Parameters<T>) => {
            if (!inThrottle) {
                func(...args);
                inThrottle = true;
                setTimeout(() => (inThrottle = false), limit);
            }
        };
    },

    /**
     * Smooth scroll to element
     */
    scrollTo(element: string | HTMLElement, offset: number = 80): void {
        const target =
            typeof element === 'string'
                ? document.querySelector(element)
                : element;
        if (target) {
            const top =
                target.getBoundingClientRect().top + window.pageYOffset - offset;
            window.scrollTo({
                top,
                behavior: 'smooth',
            });
        }
    },

    /**
     * Format number with commas
     */
    formatNumber(num: number): string {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    },

    /**
     * Check if element is in viewport
     */
    isInViewport(element: HTMLElement): boolean {
        const rect = element.getBoundingClientRect();
        return (
            rect.top >= 0 &&
            rect.left >= 0 &&
            rect.bottom <=
                (window.innerHeight || document.documentElement.clientHeight) &&
            rect.right <= (window.innerWidth || document.documentElement.clientWidth)
        );
    },

    /**
     * Generate unique ID
     */
    generateId(): string {
        return `id_${Math.random().toString(36).substr(2, 9)}`;
    },
};

/**
 * Animation Controller
 */
class AnimationController {
    private observer: IntersectionObserver | null = null;
    private animatedElements: Set<HTMLElement> = new Set();

    constructor() {
        this.init();
    }

    private init(): void {
        if ('IntersectionObserver' in window) {
            this.observer = new IntersectionObserver(
                (entries) => {
                    entries.forEach((entry) => {
                        if (entry.isIntersecting) {
                            const element = entry.target as HTMLElement;
                            element.classList.add('active');
                            this.animatedElements.delete(element);
                            if (this.observer) {
                                this.observer.unobserve(element);
                            }
                        }
                    });
                },
                {
                    threshold: 0.1,
                    rootMargin: '0px 0px -50px 0px',
                }
            );
        }
    }

    public observe(element: HTMLElement): void {
        if (this.observer && !this.animatedElements.has(element)) {
            this.animatedElements.add(element);
            this.observer.observe(element);
        } else {
            // Fallback for browsers without IntersectionObserver
            element.classList.add('active');
        }
    }

    public observeAll(selector: string): void {
        const elements = document.querySelectorAll<HTMLElement>(selector);
        elements.forEach((el) => this.observe(el));
    }
}

/**
 * Navbar Controller
 */
class NavbarController {
    private navbar: HTMLElement | null = null;
    private toggle: HTMLElement | null = null;
    private menu: HTMLElement | null = null;
    private isScrolled = false;
    private isMenuOpen = false;

    constructor() {
        this.init();
    }

    private init(): void {
        this.navbar = document.querySelector('.navbar');
        this.toggle = document.querySelector('.navbar-toggle');
        this.menu = document.querySelector('.navbar-menu-mobile');

        if (!this.navbar) return;

        this.bindEvents();
        this.handleScroll();
    }

    private bindEvents(): void {
        // Scroll handler
        window.addEventListener(
            'scroll',
            Utils.throttle(() => this.handleScroll(), 100)
        );

        // Toggle handler
        if (this.toggle) {
            this.toggle.addEventListener('click', () => this.toggleMenu());
        }

        // Close menu on link click
        if (this.menu) {
            this.menu.querySelectorAll('a').forEach((link) => {
                link.addEventListener('click', () => this.closeMenu());
            });
        }

        // Close menu on escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isMenuOpen) {
                this.closeMenu();
            }
        });

        // Close menu on outside click
        document.addEventListener('click', (e) => {
            if (
                this.isMenuOpen &&
                !this.menu?.contains(e.target as Node) &&
                !this.toggle?.contains(e.target as Node)
            ) {
                this.closeMenu();
            }
        });
    }

    private handleScroll(): void {
        const scrolled = window.scrollY > 50;
        if (scrolled !== this.isScrolled) {
            this.isScrolled = scrolled;
            this.navbar?.classList.toggle('scrolled', scrolled);
        }
    }

    private toggleMenu(): void {
        this.isMenuOpen = !this.isMenuOpen;
        this.menu?.classList.toggle('active', this.isMenuOpen);
        this.toggle?.setAttribute('aria-expanded', this.isMenuOpen.toString());
        document.body.style.overflow = this.isMenuOpen ? 'hidden' : '';
    }

    private closeMenu(): void {
        if (this.isMenuOpen) {
            this.isMenuOpen = false;
            this.menu?.classList.remove('active');
            this.toggle?.setAttribute('aria-expanded', 'false');
            document.body.style.overflow = '';
        }
    }
}

/**
 * Form Handler
 */
class FormHandler {
    private forms: NodeListOf<HTMLFormElement>;

    constructor() {
        this.forms = document.querySelectorAll('form');
        this.init();
    }

    private init(): void {
        this.forms.forEach((form) => {
            form.addEventListener('submit', (e) => this.handleSubmit(e, form));
        });
    }

    private async handleSubmit(
        e: Event,
        form: HTMLFormElement
    ): Promise<void> {
        e.preventDefault();

        const submitBtn = form.querySelector<HTMLButtonElement>('button[type="submit"]');
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        // Add loading state
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML =
                '<span class="spinner spinner-sm"></span> Sending...';
        }

        try {
            // Simulate API call
            await this.submitForm(form.action, data);
            this.showNotification('Success!', 'Your message has been sent.', 'success');
            form.reset();
        } catch (error) {
            this.showNotification('Error', 'Something went wrong. Please try again.', 'error');
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Send Message';
            }
        }
    }

    private async submitForm(
        url: string,
        data: Record<string, unknown>
    ): Promise<unknown> {
        // Simulate network delay
        await new Promise((resolve) => setTimeout(resolve, 1000));

        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data),
        });

        if (!response.ok) {
            throw new Error('Form submission failed');
        }

        return response.json();
    }

    private showNotification(
        title: string,
        message: string,
        type: 'success' | 'error' | 'info'
    ): void {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <strong>${title}</strong>
                <p>${message}</p>
            </div>
            <button class="notification-close">&times;</button>
        `;

        // Append to body
        document.body.appendChild(notification);

        // Animate in
        requestAnimationFrame(() => {
            notification.classList.add('active');
        });

        // Auto remove
        setTimeout(() => {
            notification.classList.remove('active');
            setTimeout(() => notification.remove(), 300);
        }, 5000);

        // Manual close
        notification
            .querySelector('.notification-close')
            ?.addEventListener('click', () => {
                notification.classList.remove('active');
                setTimeout(() => notification.remove(), 300);
            });
    }
}

/**
 * Theme Controller
 */
class ThemeController {
    private readonly STORAGE_KEY = 'nexar-theme';
    private currentTheme: 'dark' | 'light';

    constructor() {
        this.currentTheme = this.getStoredTheme() || 'dark';
        this.init();
    }

    private init(): void {
        this.applyTheme(this.currentTheme);
        this.bindEvents();
    }

    private getStoredTheme(): 'dark' | 'light' | null {
        const stored = localStorage.getItem(this.STORAGE_KEY);
        if (stored === 'dark' || stored === 'light') {
            return stored;
        }
        return null;
    }

    private applyTheme(theme: 'dark' | 'light'): void {
        document.documentElement.setAttribute('data-theme', theme);
        this.currentTheme = theme;
    }

    public toggle(): void {
        const newTheme = this.currentTheme === 'dark' ? 'light' : 'dark';
        this.applyTheme(newTheme);
        localStorage.setItem(this.STORAGE_KEY, newTheme);
    }

    private bindEvents(): void {
        // Listen for system theme changes
        const mediaQuery = window.matchMedia('(prefers-color-scheme: light)');
        mediaQuery.addEventListener('change', (e) => {
            if (!this.getStoredTheme()) {
                this.applyTheme(e.matches ? 'light' : 'dark');
            }
        });
    }

    public getCurrentTheme(): 'dark' | 'light' {
        return this.currentTheme;
    }
}

/**
 * Main Application Class
 */
class NEXARApp {
    private animationController: AnimationController;
    private navbarController: NavbarController;
    private formHandler: FormHandler;
    private themeController: ThemeController;

    constructor() {
        this.animationController = new AnimationController();
        this.navbarController = new NavbarController();
        this.formHandler = new FormHandler();
        this.themeController = new ThemeController();

        this.init();
    }

    private init(): void {
        // Wait for DOM
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.onDOMReady());
        } else {
            this.onDOMReady();
        }
    }

    private onDOMReady(): void {
        // Initialize scroll animations
        this.animationController.observeAll('.reveal');
        this.animationController.observeAll('.reveal-left');
        this.animationController.observeAll('.reveal-right');

        // Initialize stagger animations
        document.querySelectorAll('.stagger-group').forEach((group) => {
            const children = group.querySelectorAll<HTMLElement>('.reveal');
            children.forEach((child, index) => {
                child.style.transitionDelay = `${index * 100}ms`;
                this.animationController.observe(child);
            });
        });

        // Log initialization
        console.log('🚀 NEXAR App initialized successfully');
    }

    // Public API
    public scrollTo(section: string): void {
        Utils.scrollTo(section);
    }

    public toggleTheme(): void {
        this.themeController.toggle();
    }

    public getTheme(): 'dark' | 'light' {
        return this.themeController.getCurrentTheme();
    }
}

// Initialize application
const app = new NEXARApp();

// Export for global access
declare global {
    interface Window {
        NEXAR: NEXARApp;
    }
}

window.NEXAR = app;

export default NEXARApp;