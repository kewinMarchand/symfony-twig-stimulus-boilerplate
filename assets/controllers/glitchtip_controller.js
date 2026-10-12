/* stimulusFetch: 'lazy' */
import { Controller } from '@hotwired/stimulus';

let started = false;

/**
 * Suivi des erreurs du navigateur. Le contrôleur n'existe sur la page que si un DSN est défini,
 * et le SDK ne se charge qu'après l'affichage (événement load), une seule fois pour toute la visite.
 */
export default class extends Controller {
    static values = { dsn: String, environment: String, bundle: String };

    connect() {
        if (started) {
            return;
        }
        started = true;

        if (document.readyState === 'complete') {
            this.loadSdk();
        } else {
            window.addEventListener('load', () => this.loadSdk(), { once: true });
        }
    }

    loadSdk() {
        const { dsnValue: dsn, environmentValue: environment } = this;
        const script = document.createElement('script');
        script.src = this.bundleValue;
        script.async = true;
        script.addEventListener(
            'load',
            () => window.Sentry.init({ dsn, environment, sendDefaultPii: false, tracesSampleRate: 0 }),
            { once: true },
        );
        document.head.append(script);
    }
}
