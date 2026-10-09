import { Controller } from '@hotwired/stimulus';
import { isA11yModeEnhanced, toggleA11yMode } from '../a11y_mode.js';

export default class extends Controller {
    connect() {
        this.render(isA11yModeEnhanced(document.documentElement));
    }

    toggle() {
        this.render(toggleA11yMode(document.documentElement));
    }

    render(enhanced) {
        this.element.setAttribute('aria-pressed', String(enhanced));
    }
}
