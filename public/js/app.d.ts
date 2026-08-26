/**
 * NEXAR - Main Application TypeScript
 * Core functionality and initialization
 */
/**
 * Main Application Class
 */
declare class NEXARApp {
    private animationController;
    private navbarController;
    private formHandler;
    private themeController;
    constructor();
    private init;
    private onDOMReady;
    scrollTo(section: string): void;
    toggleTheme(): void;
    getTheme(): 'dark' | 'light';
}
declare global {
    interface Window {
        NEXAR: NEXARApp;
    }
}
export default NEXARApp;
//# sourceMappingURL=app.d.ts.map