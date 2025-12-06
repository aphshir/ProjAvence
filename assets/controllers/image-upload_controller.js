import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = [
        'typeSelector',
        'urlContainer',
        'fileContainer',
        'urlField',
        'fileField',
        'dropZone',
        'preview',
        'previewContainer'
    ];

    connect() {
        this.toggleUploadType();
    }

    toggleUploadType() {
        const selectedType = this.typeSelectorTarget.value;

        if (selectedType === 'url') {
            this.urlContainerTarget.style.display = 'block';
            this.fileContainerTarget.style.display = 'none';
            this.urlFieldTarget.required = true;
            this.fileFieldTarget.required = false;
        } else {
            this.urlContainerTarget.style.display = 'none';
            this.fileContainerTarget.style.display = 'block';
            this.urlFieldTarget.required = false;
            this.fileFieldTarget.required = false;
        }
    }

    previewImage(event) {
        const file = event.target.files[0];

        if (file && file.type.startsWith('image/')) {
            const reader = new FileReader();

            reader.onload = (e) => {
                this.previewTarget.src = e.target.result;
                this.previewContainerTarget.style.display = 'block';
            };

            reader.readAsDataURL(file);
        }
    }

    handleDragOver(event) {
        event.preventDefault();
        event.stopPropagation();
        this.dropZoneTarget.classList.add('drag-over');
    }

    handleDragLeave(event) {
        event.preventDefault();
        event.stopPropagation();
        this.dropZoneTarget.classList.remove('drag-over');
    }

    handleDrop(event) {
        event.preventDefault();
        event.stopPropagation();
        this.dropZoneTarget.classList.remove('drag-over');

        const files = event.dataTransfer.files;

        if (files.length > 0) {
            const file = files[0];

            if (file.type.startsWith('image/')) {
                this.fileFieldTarget.files = files;

                const changeEvent = new Event('change', { bubbles: true });
                this.fileFieldTarget.dispatchEvent(changeEvent);
            }
        }
    }
}
