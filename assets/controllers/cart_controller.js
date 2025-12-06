import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['badge', 'itemCount', 'total', 'subtotal', 'quantityInput'];
    static values = {
        locale: String,
        textAdding: String,
        textAdded: String
    };

    connect() {
        this.updateCartCount();
        this.handleCartUpdate = (event) => {
            this.updateBadge(event.detail.count);
        };
        window.addEventListener('cart:updated', this.handleCartUpdate);
    }

    disconnect() {
        window.removeEventListener('cart:updated', this.handleCartUpdate);
    }

    async addToCart(event) {
        const button = event.currentTarget;
        const productId = button.dataset.cartProductIdParam;
        const maxStock = parseInt(button.dataset.cartMaxStockParam);

        const quantityInput = document.getElementById('quantity');
        const quantity = quantityInput ? parseInt(quantityInput.value) : 1;

        if (quantity > maxStock) {
            alert(`Stock insufficient. Maximum disponible: ${maxStock}`);
            return;
        }

        button.disabled = true;
        const originalText = button.textContent;
        button.textContent = this.textAddingValue;

        try {
            const response = await fetch(`/${this.localeValue}/cart/add/${productId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ quantity }),
            });

            const data = await response.json();

            if (data.success) {
                this.updateBadge(data.itemCount);
                window.dispatchEvent(new CustomEvent('cart:updated', { detail: { count: data.itemCount } }));
                button.textContent = this.textAddedValue;
                setTimeout(() => {
                    button.textContent = originalText;
                    button.disabled = false;
                }, 2000);
            } else {
                alert(this.getErrorMessage(data.message, data.available));
                button.textContent = originalText;
                button.disabled = false;
            }
        } catch (error) {
            console.error('Error adding to cart:', error);
            alert('An error occurred. Please try again.');
            button.textContent = originalText;
            button.disabled = false;
        }
    }

    async updateQuantity(event) {
        const input = event.currentTarget;
        const productId = input.dataset.cartProductIdParam;
        const quantity = parseInt(input.value);

        if (quantity < 0) {
            input.value = 0;
            return;
        }

        try {
            const response = await fetch(`/${this.localeValue}/cart/update/${productId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ quantity }),
            });

            const data = await response.json();

            if (data.success) {
                this.updateBadge(data.itemCount);
                window.dispatchEvent(new CustomEvent('cart:updated', { detail: { count: data.itemCount } }));
                this.updateCartTotals(data.itemCount, data.total);

                if (quantity === 0) {
                    this.removeCartItemRow(productId);
                } else {
                    this.updateSubtotal(productId);
                }
            } else {
                alert(this.getErrorMessage(data.message, data.available));
                input.value = input.dataset.previousValue || 1;
            }
        } catch (error) {
            console.error('Error updating quantity:', error);
            input.value = input.dataset.previousValue || 1;
        }

        input.dataset.previousValue = input.value;
    }

    async increaseQuantity(event) {
        const button = event.currentTarget;
        const productId = button.dataset.cartProductIdParam;
        const maxStock = parseInt(button.dataset.cartMaxStockParam);

        const input = this.findQuantityInput(productId);
        if (!input) return;

        const currentQuantity = parseInt(input.value);

        if (currentQuantity >= maxStock) {
            alert(`Stock maximum atteint: ${maxStock}`);
            return;
        }

        input.value = currentQuantity + 1;
        await this.updateQuantity({ currentTarget: input });
    }

    async decreaseQuantity(event) {
        const button = event.currentTarget;
        const productId = button.dataset.cartProductIdParam;

        const input = this.findQuantityInput(productId);
        if (!input) return;

        const currentQuantity = parseInt(input.value);

        if (currentQuantity <= 1) {
            if (!confirm('Remove this item from cart?')) {
                return;
            }
            await this.removeItem(event);
            return;
        }

        input.value = currentQuantity - 1;
        await this.updateQuantity({ currentTarget: input });
    }

    async removeItem(event) {
        const button = event.currentTarget;
        const productId = button.dataset.cartProductIdParam;

        if (!confirm('Remove this item from cart?')) {
            return;
        }

        try {
            const response = await fetch(`/${this.localeValue}/cart/remove/${productId}`, {
                method: 'POST',
            });

            const data = await response.json();

            if (data.success) {
                this.updateBadge(data.itemCount);
                window.dispatchEvent(new CustomEvent('cart:updated', { detail: { count: data.itemCount } }));
                this.updateCartTotals(data.itemCount, data.total);
                this.removeCartItemRow(productId);

                if (data.itemCount === 0) {
                    window.location.reload();
                }
            }
        } catch (error) {
            console.error('Error removing item:', error);
            alert('An error occurred. Please try again.');
        }
    }

    async updateCartCount() {
        try {
            const response = await fetch(`/${this.localeValue}/cart/count`);
            const data = await response.json();
            this.updateBadge(data.count);
        } catch (error) {
            console.error('Error fetching cart count:', error);
        }
    }

    updateBadge(count) {
        if (this.hasBadgeTarget) {
            this.badgeTarget.textContent = count;
            this.badgeTarget.style.display = count > 0 ? 'flex' : 'none';
        }
    }

    updateCartTotals(itemCount, total) {
        if (this.hasItemCountTarget) {
            this.itemCountTarget.textContent = itemCount;
        }

        if (this.hasTotalTarget) {
            this.totalTarget.textContent = this.formatPrice(total);
        }
    }

    updateSubtotal(productId) {
        const cartItem = document.querySelector(`[data-cart-product-id="${productId}"]`);
        if (!cartItem) return;

        const quantityInput = cartItem.querySelector('.quantity-input');
        const quantity = parseInt(quantityInput.value);

        const priceElement = cartItem.querySelector('.cart-item-price');
        if (!priceElement) return;

        const priceText = priceElement.textContent.trim().replace(/[^\d.,]/g, '').replace(',', '.');
        const price = parseFloat(priceText);

        const subtotal = price * quantity;

        const subtotalElement = cartItem.querySelector('[data-cart-target="subtotal"]');
        if (subtotalElement) {
            subtotalElement.textContent = this.formatPrice(subtotal);
        }
    }

    removeCartItemRow(productId) {
        const cartItem = document.querySelector(`[data-cart-product-id="${productId}"]`);
        if (cartItem) {
            cartItem.style.opacity = '0';
            cartItem.style.transform = 'translateX(20px)';
            setTimeout(() => {
                cartItem.remove();
            }, 300);
        }
    }

    findQuantityInput(productId) {
        const cartItem = document.querySelector(`[data-cart-product-id="${productId}"]`);
        return cartItem ? cartItem.querySelector('.quantity-input') : null;
    }

    formatPrice(amount) {
        return new Intl.NumberFormat('fr-FR', {
            style: 'currency',
            currency: 'EUR',
        }).format(amount);
    }

    getErrorMessage(messageKey, available) {
        const messages = {
            product_not_found: 'Product not found.',
            invalid_quantity: 'Invalid quantity.',
            insufficient_stock: available !== undefined
                ? `Insufficient stock. Available: ${available}`
                : 'Insufficient stock.',
            item_not_in_cart: 'Item not in cart.',
        };

        return messages[messageKey] || 'An error occurred.';
    }
}
