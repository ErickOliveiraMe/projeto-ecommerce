document.addEventListener('DOMContentLoaded', function() {
    // Função principal que controla o carrinho
    function initCart() {
        // Seleciona todos os itens do carrinho
        const cartItems = document.querySelectorAll('.cart-item');
        
        // Adiciona eventos para cada item
        cartItems.forEach(item => {
            setupCartItem(item);
        });
        
        // Atualiza o resumo do pedido inicialmente
        updateOrderSummary();
    }

    // Configura um item do carrinho
    function setupCartItem(item) {
        const minusBtn = item.querySelector('.minus');
        const plusBtn = item.querySelector('.plus');
        const quantityInput = item.querySelector('input[type="number"]');
        const priceElement = item.querySelector('.item-price p');
        const removeBtn = item.querySelector('.remove-item');
        
        // Extrai o preço unitário
        const unitPrice = extractPrice(priceElement.textContent);
        
        // Atualiza o preço quando a quantidade muda
        function updatePrice() {
            const quantity = parseInt(quantityInput.value);
            const totalPrice = unitPrice * quantity;
            priceElement.textContent = formatPrice(totalPrice);
            updateOrderSummary();
            saveCart();
        }
        
        // Eventos dos botões
        minusBtn.addEventListener('click', function() {
            let quantity = parseInt(quantityInput.value);
            if (quantity > 1) {
                quantityInput.value = --quantity;
                updatePrice();
            }
        });
        
        plusBtn.addEventListener('click', function() {
            let quantity = parseInt(quantityInput.value);
            if (quantity < parseInt(quantityInput.max)) {
                quantityInput.value = ++quantity;
                updatePrice();
            }
        });
        
        // Input manual
        quantityInput.addEventListener('change', function() {
            if (this.value < 1) this.value = 1;
            if (this.value > 10) this.value = 10;
            updatePrice();
        });
        
        removeBtn.addEventListener('click', function() {
            item.remove();
            updateOrderSummary();
            saveCart();
        });
        
        // Atualiza o preço inicial
        updatePrice();
    }

    // Atualiza o resumo do pedido
    function updateOrderSummary() {
        const cartItems = document.querySelectorAll('.cart-item');
        let subtotal = 0;
        
        cartItems.forEach(item => {
            const priceText = item.querySelector('.item-price p').textContent;
            subtotal += extractPrice(priceText);
        });
        
        // Atualiza os valores no resumo
        const shipping = 25.00;
        const discount = 0.00;
        const total = subtotal + shipping - discount;
        
        document.querySelector('.summary-row:nth-child(1) span:last-child').textContent = formatPrice(subtotal);
        document.querySelector('.summary-row:nth-child(2) span:last-child').textContent = formatPrice(shipping);
        document.querySelector('.summary-row:nth-child(3) span:last-child').textContent = `-${formatPrice(discount)}`;
        document.querySelector('.summary-row.total span:last-child').textContent = formatPrice(total);
    }

    // Funções auxiliares
    function extractPrice(priceText) {
        return parseFloat(priceText.replace('R$ ', '').replace('.', '').replace(',', '.'));
    }

    function formatPrice(price) {
        return 'R$ ' + price.toFixed(2).replace('.', ',');
    }

    // Armazenamento local
    function saveCart() {
        const cartData = [];
        document.querySelectorAll('.cart-item').forEach(item => {
            cartData.push({
                name: item.querySelector('h3').textContent,
                price: extractPrice(item.querySelector('.item-price p').textContent),
                quantity: item.querySelector('input').value,
                image: item.querySelector('img').src,
                variant: item.querySelector('.item-variant').textContent
            });
        });
        localStorage.setItem('cart', JSON.stringify(cartData));
    }

    function loadCart() {
        const savedCart = localStorage.getItem('cart');
        if (savedCart) {
            const cartData = JSON.parse(savedCart);
            const cartItemsContainer = document.querySelector('.cart-items');
            
            if (cartData.length > 0) {
                cartItemsContainer.innerHTML = '';
                
                cartData.forEach(item => {
                    const cartItem = document.createElement('div');
                    cartItem.className = 'cart-item';
                    cartItem.innerHTML = `
                        <div class="item-image">
                            <img src="${item.image}" alt="${item.name}">
                        </div>
                        <div class="item-details">
                            <h3>${item.name}</h3>
                            <p class="item-variant">${item.variant}</p>
                            <div class="item-quantity">
                                <button class="quantity-btn minus"><i class="fas fa-minus"></i></button>
                                <input type="number" value="${item.quantity}" min="1" max="10">
                                <button class="quantity-btn plus"><i class="fas fa-plus"></i></button>
                            </div>
                        </div>
                        <div class="item-price">
                            <p>${formatPrice(item.price * item.quantity)}</p>
                            <button class="remove-item"><i class="fas fa-trash"></i></button>
                        </div>
                    `;
                    
                    cartItemsContainer.appendChild(cartItem);
                    setupCartItem(cartItem);
                });
            }
        }
    }

    // Busca de CEP
    document.getElementById('cep')?.addEventListener('blur', function() {
        const cep = this.value.replace(/\D/g, '');
        
        if (cep.length === 8) {
            fetch(`https://viacep.com.br/ws/${cep}/json/`)
                .then(response => response.json())
                .then(data => {
                    if (!data.erro) {
                        document.getElementById('address').value = data.logradouro;
                        document.getElementById('neighborhood').value = data.bairro;
                        document.getElementById('city').value = data.localidade;
                        document.getElementById('state').value = data.uf;
                    }
                })
                .catch(() => alert('CEP não encontrado'));
        }
    });

    // Cupom e atualização (opcional)
    document.querySelector('.coupon button')?.addEventListener('click', function() {
        alert('Funcionalidade de cupom ainda não implementada');
    });

    document.querySelector('.update-cart')?.addEventListener('click', function() {
        alert('Carrinho atualizado!');
    });

    // Inicializa o carrinho
    loadCart();
    initCart();
});