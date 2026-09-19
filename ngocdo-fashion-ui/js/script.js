// ===== Qunec - Standalone UI Script =====

// --- Cart State ---
let cart = [];

// --- DOM Elements ---
const cartToggle = document.getElementById('cartToggle');
const cartSidebar = document.getElementById('cartSidebar');
const cartOverlay = document.getElementById('cartOverlay');
const cartClose = document.getElementById('cartClose');
const cartBody = document.getElementById('cartBody');
const cartCount = document.getElementById('cartCount');
const cartTotal = document.getElementById('cartTotal');
const searchToggle = document.getElementById('searchToggle');
const searchOverlay = document.getElementById('searchOverlay');
const searchClose = document.getElementById('searchClose');
const menuToggle = document.getElementById('menuToggle');
const nav = document.getElementById('nav');
const header = document.getElementById('header');
const backToTop = document.getElementById('backToTop');

// --- Cart Functions ---
function openCart() {
  cartSidebar?.classList.add('active');
  cartOverlay?.classList.add('active');
  document.body.style.overflow = 'hidden';
}
function closeCart() {
  cartSidebar?.classList.remove('active');
  cartOverlay?.classList.remove('active');
  document.body.style.overflow = '';
}
function updateCartUI() {
  if (cartCount) cartCount.textContent = cart.reduce((acc, item) => acc + item.quantity, 0);
  if (!cartBody) return;
  
  if (cart.length === 0) {
    cartBody.innerHTML = '<p class="cart-empty">Giỏ hàng trống</p>';
    if (cartTotal) cartTotal.textContent = '0₫';
    return;
  }
  let total = 0;
  cartBody.innerHTML = cart.map((item, i) => {
    total += item.price * item.quantity;
    return `<div class="cart-item">
      <img src="${item.image}" alt="${item.name}" style="width:40px; height:50px; object-fit:cover; border-radius:4px">
      <div class="cart-item-info">
        <h4>${item.name}</h4>
        <p>${item.quantity} x ${item.price.toLocaleString('vi-VN')}₫</p>
      </div>
      <button class="cart-item-remove" onclick="removeFromCart(${i})"><i class="fas fa-trash-alt"></i></button>
    </div>`;
  }).join('');
  if (cartTotal) cartTotal.textContent = total.toLocaleString('vi-VN') + '₫';
}
function removeFromCart(index) {
  cart.splice(index, 1);
  updateCartUI();
}
function showToast(msg) {
  let toast = document.createElement('div');
  toast.className = 'toast';
  toast.innerHTML = `<i class="fas fa-check-circle"></i> ${msg}`;
  document.body.appendChild(toast);
  setTimeout(() => toast.classList.add('show'), 50);
  setTimeout(() => {
    toast.classList.remove('show');
    setTimeout(() => toast.remove(), 400);
  }, 2200);
}

// --- Event Listeners ---
cartToggle?.addEventListener('click', openCart);
cartClose?.addEventListener('click', closeCart);
cartOverlay?.addEventListener('click', closeCart);

// Add to cart buttons
document.addEventListener('click', (e) => {
  if (e.target.closest('.add-to-cart-btn')) {
    const btn = e.target.closest('.add-to-cart-btn');
    const card = btn.closest('.product-card');
    const id = card.dataset.id;
    const name = card.dataset.name;
    const price = parseInt(card.dataset.price);
    const image = card.querySelector('img').src;

    const existing = cart.find(item => item.id === id);
    if (existing) {
      existing.quantity++;
    } else {
      cart.push({ id, name, price, image, quantity: 1 });
    }
    updateCartUI();
    showToast(`Đã thêm "${name}" vào giỏ hàng`);
  }
});

// Search
searchToggle?.addEventListener('click', () => {
  searchOverlay?.classList.toggle('active');
  if (searchOverlay?.classList.contains('active')) {
    document.getElementById('searchInput')?.focus();
  }
});
searchClose?.addEventListener('click', () => searchOverlay?.classList.remove('active'));

// Mobile menu
menuToggle?.addEventListener('click', () => nav?.classList.toggle('open'));

// Header scroll shadow
window.addEventListener('scroll', () => {
  if (header) header.classList.toggle('scrolled', window.scrollY > 30);
  if (backToTop) backToTop.classList.toggle('visible', window.scrollY > 400);
});

// Back to top
backToTop?.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));

// Smooth scroll
document.querySelectorAll('a[href^="#"]').forEach(a => {
  a.addEventListener('click', e => {
    const href = a.getAttribute('href');
    if (href.startsWith('#')) {
        const id = href.substring(1);
        const el = document.getElementById(id);
        if (el) { 
            e.preventDefault(); 
            el.scrollIntoView({ behavior: 'smooth' }); 
            nav?.classList.remove('open'); 
        }
    }
  });
});

// --- Parallax & Scroll Effects ---
window.addEventListener('scroll', () => {
  const scroll = window.pageYOffset;
  
  // Hero Parallax
  const heroContent = document.querySelector('.hero-content');
  const heroSlide = document.querySelector('.hero-slide');
  if (heroContent && heroSlide) {
    heroContent.style.transform = `translateY(${scroll * 0.3}px)`;
    heroSlide.style.backgroundPositionY = `${scroll * 0.5}px`;
  }
  
  // Decorative Blobs Parallax
  document.querySelectorAll('.blob').forEach((blob, index) => {
    const speed = (index + 1) * 0.1;
    blob.style.transform = `translateY(${scroll * speed}px) rotate(${scroll * 0.05}deg)`;
  });
});

// Newsletter (Mock)
document.getElementById('newsletterForm')?.addEventListener('submit', e => {
  e.preventDefault();
  showToast('Đăng ký thành công! (Mock)');
  e.target.reset();
});

// Contact form (Mock)
document.getElementById('contactForm')?.addEventListener('submit', async e => {
  e.preventDefault();
  const btn = e.target.querySelector('button');
  const originalText = btn.innerHTML;
  btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang gửi...';
  btn.disabled = true;

  setTimeout(() => {
    showToast('Tin nhắn của bạn đã được gửi! (Mock UI Only)');
    e.target.reset();
    btn.innerHTML = originalText;
    btn.disabled = false;
  }, 1000);
});

// Countdown timer
(function countdown() {
  const cdDays = document.getElementById('cd-days');
  if (!cdDays) return;
  const end = new Date().getTime() + 7 * 24 * 60 * 60 * 1000;
  function tick() {
    const now = new Date().getTime();
    const diff = end - now;
    if (diff <= 0) return;
    document.getElementById('cd-days').textContent = String(Math.floor(diff / 86400000)).padStart(2, '0');
    document.getElementById('cd-hours').textContent = String(Math.floor((diff % 86400000) / 3600000)).padStart(2, '0');
    document.getElementById('cd-mins').textContent = String(Math.floor((diff % 3600000) / 60000)).padStart(2, '0');
    document.getElementById('cd-secs').textContent = String(Math.floor((diff % 60000) / 1000)).padStart(2, '0');
    requestAnimationFrame(tick);
  }
  tick();
})();

// Scroll reveal
const observer = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.style.opacity = '1';
      entry.target.style.transform = 'translateY(0)';
    }
  });
}, { threshold: 0.1 });

document.querySelectorAll('.product-card, .category-card, .feature-item, .ci-item').forEach(el => {
  el.style.opacity = '0';
  el.style.transform = 'translateY(30px)';
  el.style.transition = 'opacity .6s ease, transform .6s ease';
  observer.observe(el);
});
