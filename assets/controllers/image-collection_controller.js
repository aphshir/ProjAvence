import { Controller } from '@hotwired/stimulus';
import Sortable from 'sortablejs';

export default class extends Controller {
    static targets = [
        'collection', 'cardsGrid', 'imageCard',
        'dropZone', 'fileInput'
    ];
    static values = {
        index: Number
    };

    connect() {
        this.indexValue = this.collectionTarget.children.length;
        this.setupDragAndDrop();
        this.initializeSortable();
    }

    initializeSortable() {
        if (!this.hasCardsGridTarget) return;

        this.sortable = new Sortable(this.cardsGridTarget, {
            animation: 200,
            handle: '.card-drag-handle',
            ghostClass: 'dragging',
            onEnd: (event) => {
                this.updatePositions();

                // If moved to first position, set as primary
                if (event.newIndex === 0 && event.oldIndex !== 0) {
                    const card = event.item;
                    const index = parseInt(card.dataset.index);
                    this.setCardAsPrimaryByIndex(index);
                }
            }
        });
    }

    setCardAsPrimaryByIndex(index) {
        const dataContainers = this.collectionTarget.querySelectorAll('.image-data-container');

        // Uncheck all
        dataContainers.forEach(container => {
            const isPrimaryField = container.querySelector('.is-primary-field');
            if (isPrimaryField) isPrimaryField.checked = false;
        });

        // Check the one at given index
        const currentData = dataContainers[index];
        if (currentData) {
            const isPrimaryField = currentData.querySelector('.is-primary-field');
            if (isPrimaryField) isPrimaryField.checked = true;
        }

        this.updatePrimaryBadges();
    }

    disconnect() {
        if (this.sortable) {
            this.sortable.destroy();
        }
    }

    // ===== Drag & Drop Upload =====

    setupDragAndDrop() {
        if (!this.hasDropZoneTarget) return;

        const zone = this.dropZoneTarget;

        ['dragover', 'dragenter'].forEach(eventName => {
            zone.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                zone.classList.add('drag-over');
            });
        });

        ['dragleave', 'dragend', 'drop'].forEach(eventName => {
            zone.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                zone.classList.remove('drag-over');
            });
        });

        zone.addEventListener('drop', (e) => {
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                // Process dropped files immediately
                Array.from(files).forEach(file => {
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            this.createImageCard(e.target.result, file, 'file', '', false);
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }
        });
    }

    handleFileSelect(event) {
        const files = event.target.files;
        if (files.length > 0) {
            // Process files immediately and add cards directly
            Array.from(files).forEach(file => {
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        this.createImageCard(e.target.result, file, 'file', '', false);
                    };
                    reader.readAsDataURL(file);
                }
            });
            // Reset file input for next selection
            event.target.value = '';
        }
    }

    // ===== Create Image Card =====

    createImageCard(imageUrl, file = null, uploadType = 'url', altText = '', isPrimary = false) {
        // altText and isPrimary are now parameters

        // Create hidden form data
        const dataContainer = this.createDataContainer(imageUrl, file, uploadType, altText, isPrimary);
        this.collectionTarget.appendChild(dataContainer);

        // Create visual card
        const card = this.createVisualCard(imageUrl, this.indexValue, isPrimary);

        if (this.hasCardsGridTarget) {
            this.cardsGridTarget.appendChild(card);
        }

        this.indexValue++;
        this.updatePositions();

        if (isPrimary) {
            this.updatePrimaryBadges();
        }
    }

    createDataContainer(imageUrl, file, uploadType, altText, isPrimary) {
        const prototype = this.collectionTarget.dataset.prototype;
        const newForm = prototype.replace(/__name__/g, this.indexValue);

        const dataContainer = document.createElement('div');
        dataContainer.classList.add('image-data-container');
        dataContainer.dataset.index = this.indexValue;
        dataContainer.innerHTML = newForm;
        dataContainer.style.display = 'none';

        // Set values - use ID-based selectors for reliability
        const uploadTypeField = dataContainer.querySelector('select[id$="_uploadType"]');
        const urlField = dataContainer.querySelector('input[id$="_url"]');
        const fileField = dataContainer.querySelector('input[id$="_file"]');
        const altTextField = dataContainer.querySelector('input[id$="_altText"]');
        const positionField = dataContainer.querySelector('input[id$="_position"]');
        const isPrimaryField = dataContainer.querySelector('input[id$="_isPrimary"]');

        if (uploadTypeField) uploadTypeField.value = uploadType;
        if (urlField) urlField.value = imageUrl; // Set imageUrl for both URL and file uploads (data URL for files)
        if (altTextField) altTextField.value = altText;
        if (positionField) positionField.value = this.indexValue;
        if (isPrimaryField) isPrimaryField.checked = isPrimary;

        if (file && fileField) {
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            fileField.files = dataTransfer.files;
        }

        return dataContainer;
    }

    createVisualCard(imageUrl, index, isPrimary) {
        const card = document.createElement('div');
        card.classList.add('image-card');
        card.dataset.index = index;
        card.dataset.imageCollectionTarget = 'imageCard';

        card.innerHTML = `
            <div class="card-drag-handle" data-action="mousedown->image-collection#startDrag">
                <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M7 2a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm3 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0zM7 5a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm3 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0zM7 8a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm3 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm-3 3a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm3 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm-3 3a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm3 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0z"/>
                </svg>
            </div>

            <div class="card-image-preview">
                <img src="${imageUrl}" alt="">
            </div>

            <div class="card-badges">
                ${isPrimary ? '<span class="badge badge-primary">⭐ Principale</span>' : ''}
                <span class="badge badge-position">#${index + 1}</span>
            </div>

            <div class="card-actions">
                <button type="button" class="card-action-btn" data-action="click->image-collection#setCardAsPrimary" title="Définir comme principale">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                        <path d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z"/>
                    </svg>
                </button>
                <button type="button" class="card-action-btn danger" data-action="click->image-collection#deleteCard" title="Supprimer">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                        <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/>
                        <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4L4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/>
                    </svg>
                </button>
            </div>
        `;

        return card;
    }

    // ===== Card Actions =====

    setCardAsPrimary(event) {
        const card = event.currentTarget.closest('.image-card');
        const index = parseInt(card.dataset.index);

        // Uncheck all primary checkboxes
        const dataContainers = this.collectionTarget.querySelectorAll('.image-data-container');
        dataContainers.forEach(container => {
            const isPrimaryField = container.querySelector('.is-primary-field');
            if (isPrimaryField) isPrimaryField.checked = false;
        });

        // Check current one
        const currentData = dataContainers[index];
        if (currentData) {
            const isPrimaryField = currentData.querySelector('.is-primary-field');
            if (isPrimaryField) isPrimaryField.checked = true;
        }

        // Move card to first position
        this.movePrimaryToFirst(card);

        this.updatePrimaryBadges();
    }

    movePrimaryToFirst(card) {
        if (!this.hasCardsGridTarget) return;

        const firstCard = this.cardsGridTarget.firstElementChild;
        if (firstCard && firstCard !== card) {
            this.cardsGridTarget.insertBefore(card, firstCard);
            this.updatePositions();
        }
    }

    deleteCard(event) {
        if (!confirm('Êtes-vous sûr de vouloir supprimer cette image ?')) {
            return;
        }

        const card = event.currentTarget.closest('.image-card');
        const index = parseInt(card.dataset.index);

        // Remove visual card
        card.remove();

        // Remove data container
        const dataContainers = this.collectionTarget.querySelectorAll('.image-data-container');
        if (dataContainers[index]) {
            dataContainers[index].remove();
        }

        this.updatePositions();
        this.updatePrimaryBadges();
    }

    updatePrimaryBadges() {
        // Remove all primary badges from cards
        if (this.hasCardsGridTarget) {
            this.cardsGridTarget.querySelectorAll('.badge-primary').forEach(badge => badge.remove());

            // Find primary and add badge
            const dataContainers = this.collectionTarget.querySelectorAll('.image-data-container');
            dataContainers.forEach((container, index) => {
                const isPrimaryField = container.querySelector('.is-primary-field');
                if (isPrimaryField && isPrimaryField.checked) {
                    const cards = this.cardsGridTarget.querySelectorAll('.image-card');
                    if (cards[index]) {
                        const badgesContainer = cards[index].querySelector('.card-badges');
                        const primaryBadge = document.createElement('span');
                        primaryBadge.classList.add('badge', 'badge-primary');
                        primaryBadge.innerHTML = '⭐ Principale';
                        badgesContainer.insertBefore(primaryBadge, badgesContainer.firstChild);
                    }
                }
            });
        }
    }

    // ===== Update Positions =====

    updatePositions() {
        if (!this.hasCardsGridTarget) return;

        const cards = this.cardsGridTarget.querySelectorAll('.image-card');
        const dataContainers = this.collectionTarget.querySelectorAll('.image-data-container');

        console.log('=== UPDATE POSITIONS ===');
        console.log('Cards count:', cards.length);
        console.log('Data containers count:', dataContainers.length);

        cards.forEach((card, visualIndex) => {
            const oldIndex = parseInt(card.dataset.index);
            console.log(`Card ${visualIndex}: oldIndex=${oldIndex}`);

            // Find the matching data container by data-index attribute
            const matchingContainer = Array.from(dataContainers).find(
                container => parseInt(container.dataset.index) === oldIndex
            );

            if (matchingContainer) {
                const positionInput = matchingContainer.querySelector('input[id$="_position"]');
                console.log(`  - Found matching container for oldIndex ${oldIndex}`);
                console.log(`  - positionInput found:`, positionInput !== null);
                if (positionInput) {
                    console.log(`  - Setting position to ${visualIndex}, input ID:`, positionInput.id);
                    positionInput.value = visualIndex;
                    console.log(`  - Position value after set:`, positionInput.value);
                }
            } else {
                console.log(`  - NO container found for oldIndex ${oldIndex}`);
            }

            // Update position badge
            const positionBadge = card.querySelector('.badge-position');
            if (positionBadge) {
                positionBadge.textContent = `#${visualIndex + 1}`;
            }
        });

        console.log('=== END UPDATE POSITIONS ===');
    }
}
