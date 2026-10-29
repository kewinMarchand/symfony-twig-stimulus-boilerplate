/* stimulusFetch: 'lazy' */
import { Controller } from '@hotwired/stimulus';

import { isMesureRefusee } from '../opposition_mesure.js';

let started = false;
let lastUrl = null;

const push = (...command) => {
    window._paq = window._paq || [];
    window._paq.push(command);
};

/**
 * Mesure d'audience sans cookie, donc sans bandeau. Le contrôleur n'existe sur la page que si
 * Matomo est configuré. Turbo remplace <body> à chaque navigation : chaque connexion compte une
 * page vue, et les navigations de frame qui changent l'URL aussi.
 */
export default class extends Controller {
    static values = { scriptUrl: String, trackerUrl: String, siteId: String };

    connect() {
        // Refus lu avant la première commande (section 16 de la spec) : ni file, ni script, ni requête.
        if (isMesureRefusee()) {
            return;
        }
        if (!started) {
            started = true;
            push('disableCookies');
            push('setTrackerUrl', this.trackerUrlValue);
            push('setSiteId', this.siteIdValue);
            const script = document.createElement('script');
            script.src = this.scriptUrlValue;
            script.async = true;
            document.head.append(script);
        }
        this.trackPageView();
    }

    trackPageView() {
        if (!started || isMesureRefusee() || window.location.href === lastUrl) {
            return;
        }
        if (lastUrl) {
            push('setReferrerUrl', lastUrl);
        }
        lastUrl = window.location.href;
        push('setCustomUrl', lastUrl);
        push('setDocumentTitle', document.title);
        push('trackPageView');
    }
}
