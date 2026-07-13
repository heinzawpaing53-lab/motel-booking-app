<div id="systemModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm transition-all duration-300">
    <div id="systemModalContent" class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 p-6 transform transition-all duration-300 scale-95 opacity-0">
        <div class="text-center">
            <div id="modalIconWrapper" class="w-16 h-16 mx-auto mb-4 rounded-full flex items-center justify-center">
                <i id="modalIcon" class="text-2xl"></i>
            </div>
            <h3 id="modalTitle" class="text-xl font-bold text-slate-900 mb-2"></h3>
            <p id="modalMessage" class="text-slate-500 text-sm mb-6"></p>
        </div>
        <div id="modalActions" class="flex gap-3 justify-center"></div>
    </div>
</div>
<script src="<?php echo SITE_URL; ?>assets/js/main.js"></script>
</body>
</html>