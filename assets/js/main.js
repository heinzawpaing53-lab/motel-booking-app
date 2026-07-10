// Mobile menu toggle
document.addEventListener('DOMContentLoaded', function() {
    const menuBtn = document.getElementById('mobile-menu-btn');
    const mobileMenu = document.getElementById('mobile-menu');
    if (menuBtn && mobileMenu) {
        menuBtn.addEventListener('click', function() {
            mobileMenu.classList.toggle('hidden');
        });
    }

    // Sidebar toggle for admin
    const sidebarToggle = document.getElementById('sidebarToggle');
    const adminSidebar = document.getElementById('adminSidebar');
    if (sidebarToggle && adminSidebar) {
        sidebarToggle.addEventListener('click', function() {
            adminSidebar.classList.toggle('-translate-x-full');
        });
    }

    // Persist scroll position across admin page navigations
    (function() {
        var savedScroll = sessionStorage.getItem('admin_scroll_pos');
        if (savedScroll !== null) {
            sessionStorage.removeItem('admin_scroll_pos');
            window.scrollTo({ top: parseInt(savedScroll, 10), behavior: 'auto' });
        }
    })();
    window.addEventListener('beforeunload', function() {
        sessionStorage.setItem('admin_scroll_pos', window.scrollY);
    });

    // Hero slider
    initHeroSlider();

    // Auto-hide alerts
    document.querySelectorAll('.alert-auto-hide').forEach(function(alert) {
        setTimeout(function() {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(function() { alert.remove(); }, 500);
        }, 4000);
    });

    // Calculate total nights and price on reservation form
    const checkIn = document.getElementById('check_in');
    const checkOut = document.getElementById('check_out');
    const priceInput = document.getElementById('room_price');
    const totalNights = document.getElementById('total_nights');
    const totalPrice = document.getElementById('total_price');
    const estimatedTotal = document.getElementById('estimated_total');

    function calcTotal() {
        if (checkIn && checkOut && checkIn.value && checkOut.value) {
            const d1 = new Date(checkIn.value);
            const d2 = new Date(checkOut.value);
            if (d2 > d1) {
                const diff = Math.ceil((d2 - d1) / (1000 * 60 * 60 * 24));
                if (totalNights) totalNights.value = diff;
                if (priceInput && totalPrice) {
                    const price = parseFloat(priceInput.value) || 0;
                    const total = price * diff;
                    totalPrice.value = total.toFixed(2);
                    if (estimatedTotal) estimatedTotal.textContent = '$' + total.toFixed(2);
                }
            }
        }
    }

    if (checkIn) checkIn.addEventListener('change', calcTotal);
    if (checkOut) checkOut.addEventListener('change', calcTotal);

    // Confirm delete
    document.querySelectorAll('.confirm-delete').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var href = btn.getAttribute('href');
            showSystemModal('Confirm Delete', 'Are you sure you want to delete this? This action cannot be undone.', 'error', function() {
                window.location.href = href;
            });
        });
    });
});

function initHeroSlider() {
    const slides = document.querySelectorAll('.hero-slide');
    if (slides.length === 0) return;
    let current = 0;
    slides[0].classList.add('active');
    setInterval(function() {
        slides[current].classList.remove('active');
        current = (current + 1) % slides.length;
        slides[current].classList.add('active');
    }, 5000);
}

function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.classList.remove('hidden');
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function printReservation() {
    window.print();
}

var systemModalCallback = null;

function showSystemModal(title, message, type, callback) {
    if (type === undefined) type = 'info';
    var el = document.getElementById('systemModal');
    var content = document.getElementById('systemModalContent');
    var iconWrapper = document.getElementById('modalIconWrapper');
    var icon = document.getElementById('modalIcon');
    var titleEl = document.getElementById('modalTitle');
    var msgEl = document.getElementById('modalMessage');
    var actions = document.getElementById('modalActions');

    var config = {
        info: { bg: 'bg-blue-100', icon: 'fa-solid fa-circle-info text-blue-600', btn: 'bg-blue-600 hover:bg-blue-700' },
        success: { bg: 'bg-green-100', icon: 'fa-solid fa-check-circle text-green-600', btn: 'bg-green-600 hover:bg-green-700' },
        error: { bg: 'bg-rose-100', icon: 'fa-solid fa-exclamation-triangle text-rose-600', btn: 'bg-rose-600 hover:bg-rose-700' }
    };
    var c = config[type] || config.info;

    iconWrapper.className = 'w-16 h-16 mx-auto mb-4 rounded-full flex items-center justify-center ' + c.bg;
    icon.className = c.icon;
    titleEl.textContent = title;
    msgEl.textContent = message;

    systemModalCallback = callback || null;

    actions.innerHTML = '';
    if (callback) {
        var cancelBtn = document.createElement('button');
        cancelBtn.textContent = 'Cancel';
        cancelBtn.className = 'px-6 py-2.5 rounded-xl font-semibold transition border border-gray-200 text-gray-600 hover:bg-gray-50';
        cancelBtn.onclick = closeSystemModal;
        actions.appendChild(cancelBtn);

        var confirmBtn = document.createElement('button');
        confirmBtn.textContent = 'Confirm';
        confirmBtn.className = 'px-6 py-2.5 rounded-xl font-semibold transition text-white ' + c.btn;
        confirmBtn.onclick = function() {
            if (systemModalCallback) systemModalCallback();
            closeSystemModal();
        };
        actions.appendChild(confirmBtn);
    } else {
        var closeBtn = document.createElement('button');
        closeBtn.textContent = 'Close';
        closeBtn.className = 'px-6 py-2.5 rounded-xl font-semibold transition ' + c.btn + ' text-white';
        closeBtn.onclick = closeSystemModal;
        actions.appendChild(closeBtn);
    }

    el.classList.remove('hidden');
    el.classList.add('flex');
    setTimeout(function() {
        content.classList.remove('scale-95', 'opacity-0');
        content.classList.add('scale-100', 'opacity-100');
    }, 10);
}

function closeSystemModal() {
    var el = document.getElementById('systemModal');
    var content = document.getElementById('systemModalContent');
    content.classList.remove('scale-100', 'opacity-100');
    content.classList.add('scale-95', 'opacity-0');
    setTimeout(function() {
        el.classList.add('hidden');
        el.classList.remove('flex');
        systemModalCallback = null;
    }, 200);
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        var el = document.getElementById('systemModal');
        if (!el.classList.contains('hidden')) closeSystemModal();
    }
});

document.addEventListener('click', function(e) {
    var el = document.getElementById('systemModal');
    if (e.target === el) closeSystemModal();
});
