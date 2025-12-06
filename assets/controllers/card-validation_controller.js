import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = [
        'numberInput', 'numberIcon', 'numberError',
        'expirationInput', 'expirationIcon', 'expirationError',
        'cvvInput', 'cvvIcon', 'cvvError'
    ];

    static values = {
        numberPattern: { type: String, default: '^[0-9]{13,19}$' },
        expirationPattern: { type: String, default: '^(0[1-9]|1[0-2])\\/[0-9]{2,4}$' },
        cvvPattern: { type: String, default: '^[0-9]{3,4}$' }
    };

    connect() {
        this.numberRegex = new RegExp(this.numberPatternValue);
        this.expirationRegex = new RegExp(this.expirationPatternValue);
        this.cvvRegex = new RegExp(this.cvvPatternValue);
    }

    validateNumber(event) {
        const input = event.target;
        const value = input.value.replace(/\s/g, '');

        // Only validate if there's content
        if (value.length === 0) {
            this.clearValidation(input, 'number');
            return;
        }

        // Allow only digits
        if (!/^\d*$/.test(value)) {
            input.value = value.replace(/\D/g, '');
        }

        // Check format
        const isValid = this.numberRegex.test(value);
        this.setValidationState(input, 'number', isValid);
    }

    validateExpiration(event) {
        const input = event.target;
        let value = input.value;

        // Only validate if there's content
        if (value.length === 0) {
            this.clearValidation(input, 'expiration');
            return;
        }

        // Auto-format: add slash after month
        if (value.length === 2 && !value.includes('/') && event.inputType !== 'deleteContentBackward') {
            input.value = value + '/';
            value = input.value;
        }

        // Check format
        const isValid = this.expirationRegex.test(value);

        // Additional check: expiration date should be in the future
        if (isValid) {
            const [month, year] = value.split('/');
            const expDate = new Date(
                year.length === 2 ? 2000 + parseInt(year) : parseInt(year),
                parseInt(month) - 1
            );
            const now = new Date();
            now.setDate(1);
            now.setHours(0, 0, 0, 0);

            if (expDate < now) {
                this.setValidationState(input, 'expiration', false, 'expired');
                return;
            }
        }

        this.setValidationState(input, 'expiration', isValid);
    }

    validateCvv(event) {
        const input = event.target;
        const value = input.value;

        // Only validate if there's content
        if (value.length === 0) {
            this.clearValidation(input, 'cvv');
            return;
        }

        // Allow only digits
        if (!/^\d*$/.test(value)) {
            input.value = value.replace(/\D/g, '');
        }

        // Check format
        const isValid = this.cvvRegex.test(value);
        this.setValidationState(input, 'cvv', isValid);
    }

    setValidationState(input, fieldName, isValid, errorType = 'format') {
        const iconTarget = this.findTarget(`${fieldName}Icon`, input);
        const errorTarget = this.findTarget(`${fieldName}Error`, input);

        input.classList.remove('is-valid', 'is-invalid');
        input.classList.add(isValid ? 'is-valid' : 'is-invalid');

        if (iconTarget) {
            iconTarget.innerHTML = isValid
                ? '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>'
                : '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>';
            iconTarget.classList.remove('valid', 'invalid');
            iconTarget.classList.add(isValid ? 'valid' : 'invalid');
        }

        if (errorTarget && !isValid) {
            const messages = {
                number: { format: 'Card number must be 13-19 digits' },
                expiration: {
                    format: 'Format: MM/YY or MM/YYYY',
                    expired: 'Card has expired'
                },
                cvv: { format: 'CVV must be 3-4 digits' }
            };
            errorTarget.textContent = messages[fieldName]?.[errorType] || 'Invalid format';
            errorTarget.classList.add('visible');
        } else if (errorTarget) {
            errorTarget.textContent = '';
            errorTarget.classList.remove('visible');
        }
    }

    clearValidation(input, fieldName) {
        const iconTarget = this.findTarget(`${fieldName}Icon`, input);
        const errorTarget = this.findTarget(`${fieldName}Error`, input);

        input.classList.remove('is-valid', 'is-invalid');

        if (iconTarget) {
            iconTarget.innerHTML = '';
            iconTarget.classList.remove('valid', 'invalid');
        }

        if (errorTarget) {
            errorTarget.textContent = '';
            errorTarget.classList.remove('visible');
        }
    }

    findTarget(targetName, contextElement) {
        // Find the card item container
        const cardItem = contextElement.closest('.credit-card-item');
        if (!cardItem) return null;

        // Find target within the same card item
        const targetAttr = `[data-card-validation-target="${targetName.replace('Target', '')}"]`;
        return cardItem.querySelector(targetAttr);
    }
}
