// Script para a página de pagamento
document.addEventListener('DOMContentLoaded', function() {
    // Inicialização do menu mobile (reutilizado do script.js)
    initMobileMenu();
    
    // Inicialização dos métodos de pagamento
    initPaymentMethods();
    
    // Inicialização dos botões de cópia e impressão
    initCopyButtons();
    
    // Inicialização do botão de finalizar compra
    initCheckoutButton();
});

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
