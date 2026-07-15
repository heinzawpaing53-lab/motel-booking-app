<?php
define('PAGE_TITLE', 'Contact Us');
require_once 'config/db.php';
include 'includes/header.php';
?>
<style>
    .btn-primary, a.btn-primary, button.btn-primary {
        background: linear-gradient(135deg, #C8A96A, #A68B5B) !important;
        color: #2C1810 !important;
        border: none !important;
        font-weight: 600;
    }
    .btn-primary:hover, a.btn-primary:hover, button.btn-primary:hover {
        background: linear-gradient(135deg, #A68B5B, #8B7548) !important;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(200,169,106,0.3) !important;
    }
</style>

<section class="py-10 bg-luxury-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-8">
            <span class="text-luxury-400 font-semibold tracking-wider uppercase text-sm">Get In Touch</span>
            <h1 class="font-[Playfair_Display] text-5xl font-bold mt-2 text-luxury-900">Contact Us</h1>
            <p class="text-luxury-300 mt-4 max-w-2xl mx-auto">We'd love to hear from you. Reach out to us for any inquiries, feedback, or assistance with your stay.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">

            <!-- Left Column: Contact Information -->
            <div>
                <h2 class="font-[Playfair_Display] text-2xl font-bold mb-8 text-luxury-900">Contact Information</h2>
                <div class="space-y-8">
                    <div class="flex items-start space-x-5">
                        <div class="w-14 h-14 bg-luxury-800 rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-phone-alt text-luxury-400 text-lg"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold text-lg mb-1 text-luxury-900">Phone</h4>
                            <p class="text-luxury-600">09-333333333</p>
                            <p class="text-luxury-300 text-sm mt-1">Available 24/7 for reservations and inquiries</p>
                        </div>
                    </div>
                    <div class="flex items-start space-x-5">
                        <div class="w-14 h-14 bg-luxury-800 rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-envelope text-luxury-400 text-lg"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold text-lg mb-1 text-luxury-900">Email</h4>
                            <p class="text-luxury-600">info@luxurymotel.com</p>
                            <p class="text-luxury-300 text-sm mt-1">We respond within 24 hours</p>
                        </div>
                    </div>
                    <div class="flex items-start space-x-5">
                        <div class="w-14 h-14 bg-luxury-800 rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-map-marker-alt text-luxury-400 text-lg"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold text-lg mb-1 text-luxury-900">Address</h4>
                            <p class="text-luxury-600">Luxury Motel Street</p>
                            <p class="text-luxury-300 text-sm mt-1">Visit us at our front desk anytime</p>
                        </div>
                    </div>
                    <div class="flex items-start space-x-5">
                        <div class="w-14 h-14 bg-luxury-800 rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-clock text-luxury-400 text-lg"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold text-lg mb-1 text-luxury-900">Office Hours</h4>
                            <p class="text-luxury-600">Monday - Sunday: 8:00 AM - 10:00 PM</p>
                            <p class="text-luxury-300 text-sm mt-1">Front desk open 24 hours</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Message Form -->
            <div class="bg-luxury-800 rounded-xl shadow-sm p-8">
                <h2 class="font-[Playfair_Display] text-2xl font-bold mb-8 text-luxury-100">Send Us a Message</h2>
                <form action="" method="POST" class="space-y-6">
                    <div>
                        <label class="block text-sm font-semibold text-luxury-300 mb-1">Guest Name</label>
                        <input type="text" name="guest_name" required placeholder="Enter your full name" class="w-full px-4 py-3 border border-luxury-600 rounded-lg focus:ring-2 focus:ring-luxury-400 outline-none text-sm transition bg-luxury-700 text-luxury-100 placeholder-luxury-300">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-luxury-300 mb-1">Email</label>
                        <input type="email" name="email" required placeholder="Enter your email address" class="w-full px-4 py-3 border border-luxury-600 rounded-lg focus:ring-2 focus:ring-luxury-400 outline-none text-sm transition bg-luxury-700 text-luxury-100 placeholder-luxury-300">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-luxury-300 mb-1">Subject</label>
                        <input type="text" name="subject" required placeholder="What is this about?" class="w-full px-4 py-3 border border-luxury-600 rounded-lg focus:ring-2 focus:ring-luxury-400 outline-none text-sm transition bg-luxury-700 text-luxury-100 placeholder-luxury-300">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-luxury-300 mb-1">Message</label>
                        <textarea name="message" rows="5" required placeholder="Write your message here..." class="w-full px-4 py-3 border border-luxury-600 rounded-lg focus:ring-2 focus:ring-luxury-400 outline-none text-sm transition resize-none bg-luxury-700 text-luxury-100 placeholder-luxury-300"></textarea>
                    </div>
                    <button type="submit" class="w-full btn-primary text-center">
                        <i class="fas fa-paper-plane mr-2"></i>Send Message
                    </button>
                </form>
            </div>

        </div>

        <!-- Google Map -->
        <div class="mt-10">
            <h2 class="font-[Playfair_Display] text-3xl font-bold mb-6 text-center text-luxury-900">Find Us</h2>
            <div class="rounded-xl overflow-hidden shadow-lg border border-luxury-600">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3022.9663095919367!2d-73.985428!3d40.748817!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zNDDCsDQ0JzU1LjciTiA3M8KwNTknMDcuNSJX!5e0!3m2!1sen!2sus!4v1" width="100%" height="400" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
