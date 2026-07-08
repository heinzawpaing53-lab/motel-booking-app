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
            if (!confirm('Are you sure you want to delete this? This action cannot be undone.')) {
                e.preventDefault();
            }
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
