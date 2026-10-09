/* stimulusFetch: 'lazy' */
import { Controller } from '@hotwired/stimulus';

const FOCUSABLE = 'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), summary';

export default class extends Controller {
    static targets = ['title', 'announcer', 'count', 'form', 'panel', 'opener'];

    apply() {
        this.formTarget.requestSubmit();
    }

    trackClick(event) {
        this.focusTitle = Boolean(event.target.closest('[data-catalog-focus="title"]'));
    }

    rememberFocus() {
        this.focusedId = document.activeElement?.id || null;
    }

    restoreFocus() {
        this.announcerTarget.textContent = this.hasCountTarget ? this.countTarget.textContent : '';

        if (this.focusTitle) {
            this.focusTitle = false;
            this.titleTarget.focus({ preventScroll: true });
            this.titleTarget.scrollIntoView({ block: 'start' });
            return;
        }

        if (this.focusedId) {
            document.getElementById(this.focusedId)?.focus();
        }
    }

    openFilters() {
        this.filtersOpen = true;
        this.showPanel();
        this.panelTarget.querySelector(FOCUSABLE)?.focus();
    }

    closeFilters() {
        this.filtersOpen = false;
        this.panelTarget.classList.remove('is-open');
        this.panelTarget.removeAttribute('role');
        this.panelTarget.removeAttribute('aria-modal');
        document.documentElement.classList.remove('has-modal');
        this.openerTarget.setAttribute('aria-expanded', 'false');
        this.openerTarget.focus();
    }

    panelTargetConnected() {
        if (this.filtersOpen) {
            this.showPanel();
        }
    }

    showPanel() {
        this.panelTarget.classList.add('is-open');
        this.panelTarget.setAttribute('role', 'dialog');
        this.panelTarget.setAttribute('aria-modal', 'true');
        document.documentElement.classList.add('has-modal');
        this.openerTarget.setAttribute('aria-expanded', 'true');
    }

    trapFocus(event) {
        if (!this.filtersOpen) {
            return;
        }

        if (event.key === 'Escape') {
            event.preventDefault();
            this.closeFilters();
            return;
        }

        if (event.key !== 'Tab') {
            return;
        }

        const focusable = [...this.panelTarget.querySelectorAll(FOCUSABLE)].filter((element) => element.offsetParent !== null);
        const first = focusable[0];
        const last = focusable[focusable.length - 1];

        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    }
}
