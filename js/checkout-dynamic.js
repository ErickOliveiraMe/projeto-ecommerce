// Script para adaptar as páginas de checkout e pagamento para usar dados dinâmicos
document.addEventListener('DOMContentLoaded', function() {
    // Carregar dados do JSON
    loadCheckoutData();
});

// Função para carregar dados do JSON para o checkout
async function loadCheckoutData() {
    try {
        const response = await fetch('../data/products.json');
        const data = await response.json();
        
        // Verificar em qual página estamos
        const isCheckoutPage = window.location.pathname.includes('checkout.html');
        const isPaymentPage = window.location.pathname.includes('payment.html');
        
        if (isCheckoutPage) {
            // Atualizar itens do carrinho
            updateCartItems(data.cart.items, data.products);
            
            // Atualizar resumo do pedido
            updateOrderSummary(data.cart);
            
            // Inicializar funcionalidades do checkout
            initQuantityControls();
            initFormValidation();
            initMobileMenu();
        } else if (isPaymentPage) {
            // Atualizar resumo do pedido na página de pagamento
            updateOrderSummary(data.cart);
            
            // Atualizar métodos de pagamento
            updatePaymentMethods(data.payment_methods);
            
            // Inicializar funcionalidades da página de pagamento
            initPaymentMethods();
            initCopyButtons();
            initCheckoutButton();
            initMobileMenu();
        }
        
        // Atualizar categorias no menu (comum a ambas as páginas)
        updateCategories(data.categories);
        
    } catch (error) {
        console.error('Erro ao carregar dados para checkout:', error);
    }
}

// Função para atualizar itens do carrinho
function updateCartItems(cartItems, products) {
    const cartItemsContainer = document.querySelector('.cart-items');
    if (!cartItemsContainer) return;
    
    // Limpar container existente
    cartItemsContainer.innerHTML = '';
    
    // Adicionar itens do carrinho
    cartItems.forEach(item => {
        const product = products.find(p => p.id === item.product_id);
        if (!product) return;
        
        const cartItemElement = document.createElement('div');
        cartItemElement.className = 'cart-item';
        cartItemElement.innerHTML = `
            <div class="item-image">
                <img src="../${product.images[0]}" alt="${product.name}">
            </div>
            <div class="item-details">
                <h3>${product.name}</h3>
                <p class="item-variant">Tamanho: ${item.size} | Cor: ${item.color}</p>
                <div class="item-quantity">
                    <button class="quantity-btn minus"><i class="fas fa-minus"></i></button>
                    <input type="number" value="${item.quantity}" min="1" max="10" readonly>
                    <button class="quantity-btn plus"><i class="fas fa-plus"></i></button>
                </div>
            </div>
            <div class="item-price">
                <p>R$ ${(item.price * item.quantity).toFixed(2).replace('.', ',')}</p>
                <button class="remove-item"><i class="fas fa-trash"></i></button>
            </div>
        `;
        
        cartItemsContainer.appendChild(cartItemElement);
    });
}

// Função para atualizar resumo do pedido
function updateOrderSummary(cart) {
    const summaryRows = document.querySelectorAll('.summary-row');
    if (!summaryRows.length) return;
    
    // Atualizar valores
    const subtotalElement = document.querySelector('.summary-row:nth-child(1) span:last-child');
    if (subtotalElement) {
        subtotalElement.textContent = `R$ ${cart.subtotal.toFixed(2).replace('.', ',')}`;
    }
    
    const shippingElement = document.querySelector('.summary-row:nth-child(2) span:last-child');
    if (shippingElement) {
        shippingElement.textContent = `R$ ${cart.shipping.toFixed(2).replace('.', ',')}`;
    }
    
    const discountElement = document.querySelector('.summary-row:nth-child(3) span:last-child');
    if (discountElement) {
        discountElement.textContent = `-R$ ${cart.discount.toFixed(2).replace('.', ',')}`;
    }
    
    const totalElement = document.querySelector('.summary-row.total span:last-child');
    if (totalElement) {
        totalElement.textContent = `R$ ${cart.total.toFixed(2).replace('.', ',')}`;
    }
}

// Função para atualizar métodos de pagamento
function updatePaymentMethods(paymentMethods) {
    // Esta função seria implementada para atualizar dinamicamente os métodos de pagamento
    // Por enquanto, usamos os métodos estáticos já definidos no HTML
}

// Função para atualizar categorias no menu
function updateCategories(categories) {
    const menuList = document.querySelector('.menu ul');
    if (!menuList) return;
    
    // Limpar menu existente
    menuList.innerHTML = '';
    
    // Adicionar link para página inicial
    const homeItem = document.createElement('li');
    homeItem.innerHTML = '<a href="index.html">Início</a>';
    menuList.appendChild(homeItem);
    
    // Adicionar categorias do JSON
    categories.forEach(category => {
        const menuItem = document.createElement('li');
        menuItem.innerHTML = `<a href="#${category.slug}">${category.name}</a>`;
        menuList.appendChild(menuItem);
    });
}

// Função para controlar a quantidade de itens
function initQuantityControls() {
    const minusButtons = document.querySelectorAll('.quantity-btn.minus');
    const plusButtons = document.querySelectorAll('.quantity-btn.plus');
    
    minusButtons.forEach(button => {
        button.addEventListener('click', function() {
            const input = this.nextElementSibling;
            let value = parseInt(input.value);
            if (value > 1) {
                input.value = value - 1;
                updateItemTotal(this.closest('.cart-item'));
            }
        });
    });
    
    plusButtons.forEach(button => {
        button.addEventListener('click', function() {
            const input = this.previousElementSibling;
            let value = parseInt(input.value);
            if (value < 10) {
                input.value = value + 1;
                updateItemTotal(this.closest('.cart-item'));
            }
        });
    });
    
    // Remover item do carrinho
    const removeButtons = document.querySelectorAll('.remove-item');
    removeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const cartItem = this.closest('.cart-item');
            cartItem.style.opacity = '0';
            setTimeout(() => {
                cartItem.remove();
                updateCartTotals();
            }, 300);
        });
    });
}

// Função para atualizar o total de um item
function updateItemTotal(cartItem) {
    // Esta função seria implementada com dados reais do backend
    // Por enquanto, apenas simula a atualização
    updateCartTotals();
}

// Função para atualizar os totais do carrinho
function updateCartTotals() {
    // Esta função seria implementada com dados reais do backend
    // Por enquanto, apenas simula a atualização
    console.log('Totais do carrinho atualizados');
}

// Função para validação do formulário
function initFormValidation() {
    const form = document.querySelector('.checkout-form form');
    if (!form) return;
    
    const cepInput = document.getElementById('cep');
    if (cepInput) {
        cepInput.addEventListener('blur', function() {
            // Simulação de busca de CEP
            if (this.value.length === 8) {
                // Aqui seria feita uma requisição para uma API de CEP
                console.log('Buscando endereço pelo CEP:', this.value);
                // Simulando preenchimento automático
                setTimeout(() => {
                    document.getElementById('address').value = 'Rua Exemplo';
                    document.getElementById('neighborhood').value = 'Bairro Teste';
                    document.getElementById('city').value = 'São Paulo';
                    document.getElementById('state').value = 'SP';
                }, 500);
            }
        });
    }
    
    // Botão de continuar para pagamento
    const proceedButton = document.querySelector('.proceed-button');
    if (proceedButton && form) {
        proceedButton.addEventListener('click', function(e) {
            // Validação básica do formulário
            const requiredInputs = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredInputs.forEach(input => {
                if (!input.value.trim()) {
                    isValid = false;
                    input.classList.add('error');
                } else {
                    input.classList.remove('error');
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Por favor, preencha todos os campos obrigatórios.');
            }
        });
    }
}

// Função para controlar os métodos de pagamento
function initPaymentMethods() {
    const paymentHeaders = document.querySelectorAll('.payment-method-header');
    
    paymentHeaders.forEach(header => {
        header.addEventListener('click', function() {
            const method = this.closest('.payment-method');
            const radio = this.querySelector('input[type="radio"]');
            
            // Desativa todos os métodos
            document.querySelectorAll('.payment-method').forEach(m => {
                m.classList.remove('active');
            });
            
            // Desmarca todos os radios
            document.querySelectorAll('.payment-method-header input[type="radio"]').forEach(r => {
                r.checked = false;
            });
            
            // Ativa o método clicado
            method.classList.add('active');
            radio.checked = true;
        });
    });
}

// Função para os botões de cópia e impressão
function initCopyButtons() {
    const copyButtons = document.querySelectorAll('.copy-button');
    
    copyButtons.forEach(button => {
        button.addEventListener('click', function() {
            const icon = this.querySelector('i');
            
            if (icon.classList.contains('fa-copy')) {
                // Botão de copiar código PIX
                const pixCode = document.querySelector('.pix-code');
                if (pixCode) {
                    navigator.clipboard.writeText(pixCode.textContent.trim())
                        .then(() => {
                            // Feedback visual
                            this.innerHTML = '<i class="fas fa-check"></i> Copiado!';
                            setTimeout(() => {
                                this.innerHTML = '<i class="fas fa-copy"></i> Copiar código';
                            }, 2000);
                        })
                        .catch(err => {
                            console.error('Erro ao copiar texto: ', err);
                            alert('Não foi possível copiar o código. Por favor, copie manualmente.');
                        });
                }
            } else if (icon.classList.contains('fa-print')) {
                // Botão de imprimir boleto
                alert('O boleto será aberto em uma nova janela para impressão.');
                // Aqui seria implementada a lógica para abrir o boleto em uma nova janela
                // window.open('boleto.pdf', '_blank');
            }
        });
    });
}

// Função para o botão de finalizar compra
function initCheckoutButton() {
    const checkoutButton = document.querySelector('.proceed-button');
    
    if (checkoutButton) {
        checkoutButton.addEventListener('click', function() {
            // Verificar qual método de pagamento está selecionado
            const selectedMethod = document.querySelector('.payment-method.active');
            
            if (!selectedMethod) {
                alert('Por favor, selecione um método de pagamento.');
                return;
            }
            
            const methodId = selectedMethod.querySelector('input[type="radio"]').id;
            
            // Validação específica para cada método
            if (methodId === 'credit-card') {
                // Validar campos do cartão
                const cardNumber = document.getElementById('card-number');
                const cardName = document.getElementById('card-name');
                const cardExpiry = document.getElementById('card-expiry');
                const cardCvv = document.getElementById('card-cvv');
                
                if (!cardNumber.value || !cardName.value || !cardExpiry.value || !cardCvv.value) {
                    alert('Por favor, preencha todos os dados do cartão.');
                    return;
                }
                
                // Simulação de processamento de pagamento
                processPayment('credit-card');
            } else if (methodId === 'pix') {
                // Simulação de processamento de pagamento PIX
                alert('Seu pedido foi registrado! Por favor, realize o pagamento via PIX usando o código ou QR code fornecido.');
                // Aqui seria implementada a lógica para registrar o pedido e aguardar confirmação do PIX
            } else if (methodId === 'boleto') {
                // Simulação de geração de boleto
                alert('Seu pedido foi registrado! O boleto foi gerado e pode ser impresso ou enviado para seu e-mail.');
                // Aqui seria implementada a lógica para gerar o boleto
            }
        });
    }
}

// Função para simular processamento de pagamento
function processPayment(method) {
    // Esta função seria implementada com integração real de gateway de pagamento
    // Por enquanto, apenas simula o processamento
    
    // Mostrar loading
    const button = document.querySelector('.proceed-button');
    const originalText = button.textContent;
    button.textContent = 'Processando...';
    button.disabled = true;
    
    // Simular processamento
    setTimeout(() => {
        // Simular sucesso
        alert('Pagamento processado com sucesso! Seu pedido foi confirmado.');
        
        // Redirecionar para página de confirmação
        // window.location.href = 'confirmation.html';
        
        // Ou apenas resetar o botão para demonstração
        button.textContent = originalText;
        button.disabled = false;
    }, 2000);
}

// Função para controlar o menu mobile
function initMobileMenu() {
    const menuButton = document.querySelector('.menu-mobile');
    const menu = document.querySelector('.menu');
    
    if (menuButton && menu) {
        menuButton.addEventListener('click', () => {
            menu.classList.toggle('active');
            
            const icon = menuButton.querySelector('i');
            if (icon.classList.contains('fa-bars')) {
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-times');
            } else {
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            }
        });
    }
}
