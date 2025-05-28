// Script principal para o catálogo

document.addEventListener('DOMContentLoaded', function() {
    // Inicialização do carrossel de banners
    initSlider();
    
    // Inicialização do FAQ
    initFaq();
    
    // Inicialização do menu mobile
    initMobileMenu();
    
    // Inicialização do modal de produtos
    initProductModal();
});

// Função para controlar o carrossel de banners
function initSlider() {
    const slides = document.querySelectorAll('.slide');
    const dots = document.querySelectorAll('.dot');
    let currentSlide = 0;
    
    // Função para mostrar um slide específico
    function showSlide(n) {
        // Remove a classe active de todos os slides e dots
        slides.forEach(slide => slide.classList.remove('active'));
        dots.forEach(dot => dot.classList.remove('active'));
        
        // Adiciona a classe active ao slide e dot atual
        slides[n].classList.add('active');
        dots[n].classList.add('active');
        
        currentSlide = n;
    }
    
    // Adiciona evento de clique aos dots
    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
            showSlide(index);
        });
    });
    
    // Função para avançar para o próximo slide
    function nextSlide() {
        currentSlide = (currentSlide + 1) % slides.length;
        showSlide(currentSlide);
    }
    
    // Inicia o carrossel automático
    let slideInterval = setInterval(nextSlide, 5000);
    
    // Pausa o carrossel quando o mouse está sobre ele
    const slider = document.querySelector('.slider');
    slider.addEventListener('mouseenter', () => {
        clearInterval(slideInterval);
    });
    
    // Reinicia o carrossel quando o mouse sai
    slider.addEventListener('mouseleave', () => {
        slideInterval = setInterval(nextSlide, 5000);
    });
}

// Função para controlar o FAQ
function initFaq() {
    const faqItems = document.querySelectorAll('.faq-item');
    
    faqItems.forEach(item => {
        const question = item.querySelector('.faq-question');
        
        question.addEventListener('click', () => {
            // Fecha todos os outros itens
            faqItems.forEach(otherItem => {
                if (otherItem !== item) {
                    otherItem.classList.remove('active');
                }
            });
            
            // Alterna o estado do item atual
            item.classList.toggle('active');
        });
    });
}

// Função para controlar o menu mobile
function initMobileMenu() {
    const menuButton = document.querySelector('.menu-mobile');
    const menu = document.querySelector('.menu');
    
    menuButton.addEventListener('click', () => {
        // Implementação do menu mobile (toggle de classe)
        menu.classList.toggle('active');
        
        // Alterna o ícone do menu
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

// Função para controlar o modal de produtos
function initProductModal() {
    const productCards = document.querySelectorAll('.product-card');
    const modal = document.getElementById('productModal');
    const closeBtn = document.querySelector('.close-modal');
    
    // Dados dos produtos (simulando um banco de dados)
    const productData = {
        1: {
            title: 'Air Jordan 1 Low Laranja',
            price: 'R$ 599,00',
            description: 'O Air Jordan 1 Low Laranja é um tênis icônico que combina estilo e conforto. Feito com materiais de alta qualidade, este modelo apresenta a clássica silhueta Air Jordan 1 em uma versão low-top com detalhes em laranja vibrante. Perfeito para uso casual e para complementar seu visual streetwear.',
            images: [
                'images/product1.jpg',
                'images/product1.jpg',
                'images/product1.jpg'
            ],
            sizes: ['38', '39', '40', '41', '42'],
            colors: ['#FF5733', '#000000', '#FFFFFF']
        },
        2: {
            title: 'Air Jordan 1 Low Mocha',
            price: 'R$ 599,00',
            description: 'O Air Jordan 1 Low Mocha apresenta uma combinação elegante de cores em tons terrosos. Com acabamento premium e detalhes em marrom mocha, este tênis é versátil e combina com diversos estilos. A entressola Air proporciona amortecimento excepcional para o dia a dia.',
            images: [
                'images/product2.jpg',
                'images/product2.jpg',
                'images/product2.jpg'
            ],
            sizes: ['38', '39', '40', '41', '42'],
            colors: ['#6F4E37', '#000000', '#FFFFFF']
        },
        3: {
            title: 'Conjunto Trapstar',
            price: 'R$ 399,00',
            description: 'Conjunto completo da marca Trapstar, incluindo camiseta e shorts combinando. Confeccionado em algodão de alta qualidade com estampa exclusiva da marca. Perfeito para um visual urbano e confortável.',
            images: [
                'images/product3.jpg',
                'images/product3.jpg',
                'images/product3.jpg'
            ],
            sizes: ['P', 'M', 'G', 'GG'],
            colors: ['#FFFFFF', '#000000', '#808080']
        },
        4: {
            title: 'Conjunto Trapstar Preto/Cinza',
            price: 'R$ 399,00',
            description: 'Conjunto Trapstar na combinação preto e cinza, com logo bordado e acabamento premium. Tecido macio e confortável, ideal para o dia a dia com estilo streetwear autêntico.',
            images: [
                'images/product4.jpg',
                'images/product4.jpg',
                'images/product4.jpg'
            ],
            sizes: ['P', 'M', 'G', 'GG'],
            colors: ['#000000', '#808080', '#333333']
        },
        5: {
            title: 'Touca Synaworld Preta/Verde',
            price: 'R$ 129,00',
            description: 'Touca Synaworld em preto com detalhes em verde neon. Confeccionada em material macio e elástico que proporciona conforto e aquecimento. Logo bordado em destaque.',
            images: [
                'images/product5.jpg',
                'images/product5.jpg',
                'images/product5.jpg'
            ],
            sizes: ['Único'],
            colors: ['#000000', '#00FF00']
        },
        6: {
            title: 'Touca Synaworld Preto/Cinza',
            price: 'R$ 129,00',
            description: 'Touca Synaworld na combinação preto e cinza, perfeita para os dias mais frios. Material de alta qualidade com elasticidade e durabilidade.',
            images: [
                'images/product6.jpg',
                'images/product6.jpg',
                'images/product6.jpg'
            ],
            sizes: ['Único'],
            colors: ['#000000', '#808080']
        },
        7: {
            title: 'Boné Synaworld Cinza',
            price: 'R$ 159,00',
            description: 'Boné Synaworld em cinza com aba curva e ajuste snapback. Logo bordado em relevo e acabamento premium. Perfeito para complementar seu visual streetwear.',
            images: [
                'images/product7.jpg',
                'images/product7.jpg',
                'images/product7.jpg'
            ],
            sizes: ['Único'],
            colors: ['#808080', '#000000']
        },
        8: {
            title: 'Dunk Low Cacao Wow',
            price: 'R$ 549,00',
            description: 'O Nike Dunk Low Cacao Wow apresenta uma combinação única de cores em tons de marrom e bege. Confeccionado em couro premium com detalhes em camurça, este tênis oferece conforto e estilo inigualáveis.',
            images: [
                'images/product8.jpg',
                'images/product8.jpg',
                'images/product8.jpg'
            ],
            sizes: ['38', '39', '40', '41', '42'],
            colors: ['#6F4E37', '#D2B48C', '#FFFFFF']
        }
    };
    
    // Adiciona evento de clique a cada card de produto
    productCards.forEach(card => {
        card.addEventListener('click', () => {
            const productId = card.getAttribute('data-product');
            openProductModal(productId);
        });
    });
    
    // Função para abrir o modal com os dados do produto
    function openProductModal(productId) {
        const product = productData[productId];
        
        if (!product) return;
        
        // Preenche os dados do produto no modal
        document.getElementById('modalTitle').textContent = product.title;
        document.getElementById('modalPrice').textContent = product.price;
        document.getElementById('modalDescription').textContent = product.description;
        
        // Preenche as imagens
        document.getElementById('modalMainImage').src = product.images[0];
        
        const thumbnails = document.querySelectorAll('.thumbnail');
        thumbnails.forEach((thumb, index) => {
            if (product.images[index]) {
                thumb.src = product.images[index];
                thumb.style.display = 'block';
            } else {
                thumb.style.display = 'none';
            }
        });
        
        // Adiciona evento de clique às miniaturas
        thumbnails.forEach((thumb, index) => {
            thumb.addEventListener('click', () => {
                document.getElementById('modalMainImage').src = thumb.src;
                thumbnails.forEach(t => t.classList.remove('active'));
                thumb.classList.add('active');
            });
        });
        
        // Exibe o modal
        modal.classList.add('active');
        document.body.style.overflow = 'hidden'; // Impede a rolagem da página
    }
    
    // Fecha o modal ao clicar no botão de fechar
    closeBtn.addEventListener('click', closeModal);
    
    // Fecha o modal ao clicar fora do conteúdo
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            closeModal();
        }
    });
    
    // Função para fechar o modal
    function closeModal() {
        modal.classList.remove('active');
        document.body.style.overflow = ''; // Restaura a rolagem da página
    }
    
    // Fecha o modal ao pressionar a tecla ESC
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal.classList.contains('active')) {
            closeModal();
        }
    });
}

// Adiciona estilo para o menu mobile quando ativado
document.head.insertAdjacentHTML('beforeend', `
<style>
@media (max-width: 768px) {
    .menu.active {
        display: block;
        position: absolute;
        top: 80px;
        left: 0;
        width: 100%;
        background-color: #000;
        padding: 20px;
        z-index: 1000;
    }
    
    .menu.active ul {
        flex-direction: column;
    }
    
    .menu.active ul li {
        margin: 10px 0;
    }
}
</style>
`);
