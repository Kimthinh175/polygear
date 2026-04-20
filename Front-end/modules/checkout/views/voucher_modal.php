<!-- Voucher Select Modal (Shopee Style) -->
<div id="voucherSelectModal"
    class="hidden fixed inset-0 z-999999 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-gray-900/60 backdrop-blur-sm transition-all duration-300 opacity-0">
    <div class="bg-white w-full h-[85vh] sm:h-auto sm:max-h-[85vh] max-w-md rounded-t-3xl sm:rounded-2xl shadow-2xl transform translate-y-full sm:translate-y-0 sm:scale-95 transition-all duration-300 flex flex-col"
        onclick="event.stopPropagation()">

        <!-- Header -->
        <div
            class="p-5 border-b border-gray-100 flex justify-between items-center bg-white rounded-t-3xl sm:rounded-t-2xl relative z-10 shrink-0">
            <div>
                <h3 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                    <i class="fa-solid fa-ticket text-blue-600"></i> Shop Voucher
                </h3>
                <p class="text-[13px] text-gray-500 mt-0.5">Chọn tối đa 1 Voucher cho mỗi đơn hàng</p>
            </div>
            <button type="button"
                class="w-9 h-9 rounded-full bg-gray-50 text-gray-400 hover:text-gray-700 hover:bg-gray-100 flex items-center justify-center transition-colors"
                onclick="voucherUI.closeModal()">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <!-- Voucher Input -->
        <div class="p-4 bg-gray-50/50 border-b border-gray-100 shrink-0">
            <div class="flex gap-2">
                <input type="text" id="manualVoucherCode" placeholder="Mã Voucher (v.d. TECH50)"
                    class="flex-1 px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-blue-500 uppercase text-sm font-medium tracking-wide">
                <button type="button"
                    class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-bold transition-all whitespace-nowrap disabled:opacity-50"
                    onclick="voucherUI.applyManualCode()">Áp dụng</button>
            </div>
            <p id="manualVoucherError" class="text-red-500 text-xs mt-2 hidden"></p>
        </div>

        <!-- Voucher List -->
        <div class="flex-1 overflow-y-auto p-4 bg-gray-50" id="availableVouchersList">
            <!-- Loading -->
            <div class="py-10 text-center">
                <i class="fa-solid fa-circle-notch fa-spin text-blue-500 text-2xl"></i>
                <p class="text-gray-500 text-sm mt-3">Đang tải mã giảm giá...</p>
            </div>
        </div>

        <!-- Footer / Confirm Button -->
        <div class="p-4 bg-white border-t border-gray-100 shrink-0 rounded-t-3xl sm:rounded-t-2xl">
            <button type="button"
                class="w-full py-4 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold shadow-lg shadow-blue-600/20 transition-all text-sm uppercase tracking-wide disabled:opacity-50"
                onclick="voucherUI.confirmSelection()" id="btnConfirmVoucher">Đồng Ý</button>
        </div>
    </div>
</div>

<script src="js/checkout/voucherUI.js" defer></script>