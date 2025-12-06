import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['menu'];

    connect() {
        this.clickOutsideHandler = this.clickOutside.bind(this);
        document.addEventListener('click', this.clickOutsideHandler);
    }

    disconnect() {
        document.removeEventListener('click', this.clickOutsideHandler);
    }

    toggle(event) {
        event.stopPropagation();
        this.element.classList.toggle('open');
    }

    clickOutside(event) {
        if (!this.element.contains(event.target)) {
            this.element.classList.remove('open');
        }
    }
}
