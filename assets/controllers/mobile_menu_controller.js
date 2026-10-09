import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['toggle', 'dialog', 'level'];

    open() {
        this.dialogTarget.showModal();
        this.toggleTarget.setAttribute('aria-expanded', 'true');
    }

    close() {
        this.dialogTarget.close();
    }

    reset() {
        this.toggleTarget.setAttribute('aria-expanded', 'false');
        this.display('root');
        this.toggleTarget.focus();
    }

    show({ params: { level } }) {
        this.display(level);
        this.levelTargets.find((element) => !element.hidden)?.querySelector('.mobile-menu__heading')?.focus();
    }

    display(level) {
        this.levelTargets.forEach((element) => {
            element.hidden = element.dataset.level !== level;
        });
    }
}
