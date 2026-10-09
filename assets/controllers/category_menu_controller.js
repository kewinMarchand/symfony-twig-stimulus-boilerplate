import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['toggle', 'panel'];

    toggle() {
        if (this.panelTarget.hidden) {
            this.open();
        } else {
            this.close();
        }
    }

    open() {
        this.panelTarget.hidden = false;
        this.toggleTarget.setAttribute('aria-expanded', 'true');
        this.alignPanel();
    }

    alignPanel() {
        const columns = [...this.panelTarget.querySelectorAll('.category-menu__column')];
        const depth = Math.max(...columns.map((column) => this.columnDepth(column)));
        const width = columns[0].offsetWidth * depth;
        const overflows = this.element.getBoundingClientRect().left + width > document.documentElement.clientWidth;

        this.panelTarget.classList.toggle('category-menu__panel--end', overflows);
    }

    columnDepth(column) {
        let depth = 0;
        for (let element = column; element && element !== this.panelTarget; element = element.parentElement) {
            depth += element.classList.contains('category-menu__column') ? 1 : 0;
        }
        return depth;
    }

    close() {
        this.collapseAll();
        this.panelTarget.hidden = true;
        this.toggleTarget.setAttribute('aria-expanded', 'false');
    }

    closeAndFocus() {
        if (this.panelTarget.hidden) {
            return;
        }
        this.close();
        this.toggleTarget.focus();
    }

    closeOnOutsideClick(event) {
        if (!this.panelTarget.hidden && !this.element.contains(event.target)) {
            this.close();
        }
    }

    expand(event) {
        const item = event.currentTarget.closest('li');

        for (const sibling of item.parentElement.children) {
            if (sibling !== item) {
                this.collapse(sibling);
            }
        }

        const link = item.querySelector(':scope > a[aria-expanded]');
        if (link) {
            item.classList.add('is-open');
            link.setAttribute('aria-expanded', 'true');
        }
    }

    collapseAll() {
        this.panelTarget.querySelectorAll(':scope > ul > li').forEach((item) => this.collapse(item));
    }

    collapse(item) {
        item.classList.remove('is-open');
        item.querySelector(':scope > a[aria-expanded]')?.setAttribute('aria-expanded', 'false');
        item.querySelectorAll(':scope > ul > li').forEach((child) => this.collapse(child));
    }
}
