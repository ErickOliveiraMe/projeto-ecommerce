// Script para carregar dados dinâmicos do JSON
document.addEventListener('DOMContentLoaded', function() {
    // Carregar dados do JSON
    loadDataFromJSON();
});

// Função para carregar dados do JSON
async function loadDataFromJSON() {
    try {
        const response = await fetch('data/products.json');
        const data = await response.json();
        
        // Atualizar produtos na página inicial
        updateProducts(data.products);
        
        // Atualizar categorias no menu
        updateCategories(data.categories);
        
        // Atualizar marcas no carrossel
        updateBrands(data.brands);
        
        // Inicializar o modal de produtos com dados dinâmicos
        initDynamicProductModal(data.products);
        
        // Inicializar outras funcionalidades do site
        initSlider();
        initFaq();
        initMobileMenu();
    } catch (error) {
        console.error('Erro ao carregar dados:', error);
    }
}

// Função para atualizar produtos na página
function updateProducts(products) {
    const productGrid = document.querySelector('.product-grid');
    if (!productGrid) return;
    
    // Limpar grid existente
    productGrid.innerHTML = '';
    
    // Adicionar produtos do JSON
    products.forEach(product => {
        const productCard = document.createElement('div');
        productCard.className = 'product-card';
        productCard.setAttribute('data-product', product.id);
        
        productCard.innerHTML = `
            <div class="product-image">
                <img src="${product.images[0]}" alt="${product.name}">
            </div>
            <div class="product-info">
                <h3>${product.name}</h3>
                <p class="price">R$ ${product.price.toFixed(2).replace('.', ',')}</p>
            </div>
        `;
        
        productGrid.appendChild(productCard);
    });
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

// Função para atualizar marcas no carrossel
function updateBrands(brands) {
    const brandsTrack = document.querySelector('.brands-track');
    if (!brandsTrack) return;
    
    // Limpar carrossel existente
    brandsTrack.innerHTML = '';
    
    // Adicionar marcas do JSON
    brands.forEach(brand => {
        const brandDiv = document.createElement('div');
        brandDiv.className = 'brand';
        brandDiv.innerHTML = `<img src="${brand.logo}" alt="${brand.name}">`;
        brandsTrack.appendChild(brandDiv);
    });
    
    // Duplicar marcas para efeito infinito
    brands.forEach(brand => {
        const brandDiv = document.createElement('div');
        brandDiv.className = 'brand';
        brandDiv.innerHTML = `<img src="${brand.logo}" alt="${brand.name}">`;
        brandsTrack.appendChild(brandDiv);
    });
    
    // Iniciar animação do carrossel
    initBrandsCarousel();
}

// Função para inicializar o carrossel de marcas
function initBrandsCarousel() {
    const brandsTrack = document.querySelector('.brands-track');
    if (!brandsTrack) return;
    
    // Adicionar animação CSS
    brandsTrack.style.animation = 'scroll 20s linear infinite';
    
    // Adicionar estilo para animação
    const style = document.createElement('style');
    style.textContent = `
        @keyframes scroll {
            0% { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }
    `;
    document.head.appendChild(style);
}

// Função para inicializar o modal de produtos com dados dinâmicos
function initDynamicProductModal(products) {
    const productCards = document.querySelectorAll('.product-card');
    const modal = document.getElementById('productModal');
    const closeBtn = document.querySelector('.close-modal');
    
    if (!modal || !closeBtn) return;
    
    // Adicionar evento de clique a cada card de produto
    productCards.forEach(card => {
        card.addEventListener('click', () => {
            const productId = parseInt(card.getAttribute('data-product'));
            const product = products.find(p => p.id === productId);
            
            if (product) {
                openProductModal(product);
            }
        });
    });
    
    // Função para abrir o modal com os dados do produto
    function openProductModal(product) {
        // Preenche os dados do produto no modal
        document.getElementById('modalTitle').textContent = product.name;
        document.getElementById('modalPrice').textContent = `R$ ${product.price.toFixed(2).replace('.', ',')}`;
        document.getElementById('modalDescription').textContent = product.description;
        
        // Preenche as imagens
        const mainImage = document.getElementById('modalMainImage');
        mainImage.src = product.images[0];
        
        const thumbnails = document.querySelectorAll('.thumbnail');
        thumbnails.forEach((thumb, index) => {
            if (product.images[index]) {
                thumb.src = product.images[index];
                thumb.style.display = 'block';
            } else {
                thumb.style.display = 'none';
            }
        });
        
        // Preenche os tamanhos
        const sizeOptions = document.querySelector('.size-options');
        if (sizeOptions) {
            sizeOptions.innerHTML = '';
            product.sizes.forEach(size => {
                const sizeSpan = document.createElement('span');
                sizeSpan.className = 'size';
                sizeSpan.textContent = size;
                sizeOptions.appendChild(sizeSpan);
            });
        }
        
        // Preenche as cores
        const colorOptions = document.querySelector('.color-options');
        if (colorOptions) {
            colorOptions.innerHTML = '';
            product.colors.forEach(color => {
                const colorSpan = document.createElement('span');
                colorSpan.className = 'color';
                colorSpan.style.backgroundColor = color.code;
                colorSpan.setAttribute('title', color.name);
                colorOptions.appendChild(colorSpan);
            });
        }
        
        // Adiciona evento de clique às miniaturas
        thumbnails.forEach((thumb, index) => {
            thumb.addEventListener('click', () => {
                mainImage.src = thumb.src;
                thumbnails.forEach(t => t.classList.remove('active'));
                thumb.classList.add('active');
            });
        });
        
        // Adiciona evento de clique aos tamanhos
        const sizes = document.querySelectorAll('.size');
        sizes.forEach(size => {
            size.addEventListener('click', () => {
                sizes.forEach(s => s.classList.remove('active'));
                size.classList.add('active');
            });
        });
        
        // Adiciona evento de clique às cores
        const colors = document.querySelectorAll('.color');
        colors.forEach(color => {
            color.addEventListener('click', () => {
                colors.forEach(c => c.classList.remove('active'));
                color.classList.add('active');
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

// Função para controlar o carrossel de banners (mantida do script original)
function initSlider() {
    const slides = document.querySelectorAll('.slide');
    const dots = document.querySelectorAll('.dot');
    let currentSlide = 0;
    
    if (!slides.length || !dots.length) return;
    
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
    if (slider) {
        slider.addEventListener('mouseenter', () => {
            clearInterval(slideInterval);
        });
        
        // Reinicia o carrossel quando o mouse sai
        slider.addEventListener('mouseleave', () => {
            slideInterval = setInterval(nextSlide, 5000);
        });
    }
}

// Função para controlar o FAQ (mantida do script original)
function initFaq() {
    const faqItems = document.querySelectorAll('.faq-item');
    
    faqItems.forEach(item => {
        const question = item.querySelector('.faq-question');
        
        if (question) {
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
        }
    });
}

// Função para controlar o menu mobile (mantida do script original)
function initMobileMenu() {
    const menuButton = document.querySelector('.menu-mobile');
    const menu = document.querySelector('.menu');
    
    if (menuButton && menu) {
        menuButton.addEventListener('click', () => {
            // Implementação do menu mobile (toggle de classe)
            menu.classList.toggle('active');
            
            // Alterna o ícone do menu
            const icon = menuButton.querySelector('i');
            if (icon) {
                if (icon.classList.contains('fa-bars')) {
                    icon.classList.remove('fa-bars');
                    icon.classList.add('fa-times');
                } else {
                    icon.classList.remove('fa-times');
                    icon.classList.add('fa-bars');
                }
            }
        });
    }
}
