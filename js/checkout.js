// Script para a página de checkout
document.addEventListener('DOMContentLoaded', function() {
    // Inicialização do menu mobile (reutilizado do script.js)
    initMobileMenu();
    
    // Controle de quantidade de itens
    initQuantityControls();
    
    // Atualização de valores do carrinho
    initCartUpdates();
    
    // Validação de formulário
    initFormValidation();
});

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
    if (proceedButton) {
        proceedButton.addEventListener('click', function(e) {
            if (form) {
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
            }
        });
    }
}

// Função para controlar o menu mobile (reutilizada do script.js)
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

// Função para atualizar o carrinho
function initCartUpdates() {
    const updateCartButton = document.querySelector('.update-cart');
    if (updateCartButton) {
        updateCartButton.addEventListener('click', function() {
            // Simulação de atualização do carrinho
            alert('Carrinho atualizado com sucesso!');
            updateCartTotals();
        });
    }
    
    // Aplicar cupom de desconto
    const couponButton = document.querySelector('.coupon button');
    if (couponButton) {
        couponButton.addEventListener('click', function() {
            const couponInput = document.querySelector('.coupon input');
            if (couponInput && couponInput.value.trim()) {
                // Simulação de aplicação de cupom
                alert('Cupom aplicado com sucesso!');
                // Atualizar valores
                const discountElement = document.querySelector('.summary-row:nth-child(3) span:last-child');
                if (discountElement) {
                    discountElement.textContent = '-R$ 50,00';
                }
                
                const totalElement = document.querySelector('.summary-row.total span:last-child');
                if (totalElement) {
                    totalElement.textContent = 'R$ 973,00';
                }
            } else {
                alert('Por favor, insira um código de cupom válido.');
            }
        });
    }
}
