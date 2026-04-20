document.addEventListener("DOMContentLoaded", async () => {
  const list = document.getElementById("vouchers-list");
  const empty = document.getElementById("vouchers-empty");
  const toast = document.getElementById("copy-toast");

  function formatMoney(n) {
    return Number(n).toLocaleString("vi-VN") + "đ";
  }

  function formatDate(dateStr) {
    if (!dateStr) return null;
    return new Date(dateStr).toLocaleDateString("vi-VN", { day: "2-digit", month: "2-digit", year: "numeric" });
  }

  function showToast() {
    toast.classList.remove("opacity-0");
    toast.classList.add("opacity-100");
    setTimeout(() => {
      toast.classList.remove("opacity-100");
      toast.classList.add("opacity-0");
    }, 2000);
  }

  function copyCode(code) {
    navigator.clipboard.writeText(code).then(showToast);
  }

  function isExpiringSoon(timeEnd) {
    if (!timeEnd) return false;
    const diff = (new Date(timeEnd) - Date.now()) / (1000 * 60 * 60 * 24);
    return diff <= 3;
  }

  try {
    const res = await fetch("https:// polygearid.ivi.vn/back-end/api/vouchers", { credentials: "include" });
    const data = await res.json();

    list.innerHTML = "";

    if (!data.data || data.data.length === 0) {
      empty.classList.remove("hidden");
      return;
    }

    data.data.forEach(v => {
      const value = parseInt(v.value) || 0;
      const isPercent = value <= 100;
      const displayValue = isPercent ? `${value}%` : formatMoney(value);

      let conditionText = "Không giới hạn";
      if (v.condition) {
        try {
          const cond = JSON.parse(v.condition);
          const parts = [];
          if (cond.min_order_value) parts.push(`Đơn tối thiểu ${formatMoney(cond.min_order_value)}`);
          if (cond.user_limit) parts.push(`Mỗi KH dùng tối đa ${cond.user_limit} lần`);
          if (cond.global_limit) parts.push(`Tổng ${cond.global_limit} lượt sử dụng`);
          if (parts.length > 0) conditionText = parts.join(" · ");
        } catch (e) { conditionText = v.condition; }
      }

      const endDate = formatDate(v.time_end);
      const expiringSoon = isExpiringSoon(v.time_end);
      const endLabel = endDate
        ? expiringSoon
          ? `<span class="text-red-500 font-bold text-xs">⚡ Hết hạn: ${endDate}</span>`
          : `<span class="text-slate-400 text-xs">HSD: ${endDate}</span>`
        : `<span class="text-green-600 font-bold text-xs">Không giới hạn thời gian</span>`;

      list.innerHTML += `
        <div class="relative border ${expiringSoon ? "border-red-200 bg-red-50/30" : "border-dashed border-slate-200 bg-white"} rounded-2xl overflow-hidden group hover:shadow-md transition-all duration-200">
          <!-- Color strip left -->
          <div class="absolute left-0 top-0 bottom-0 w-1.5 ${expiringSoon ? "bg-red-400" : "bg-primary"} rounded-l-2xl"></div>
          
          <div class="flex items-stretch pl-4">
            <!-- Left discount value -->
            <div class="flex-shrink-0 flex flex-col items-center justify-center w-24 py-5 border-r border-dashed border-slate-200 pr-4">
              <span class="text-2xl font-black ${expiringSoon ? "text-red-500" : "text-primary"}">${displayValue}</span>
              <span class="text-[10px] font-bold uppercase text-slate-400 mt-0.5">${isPercent ? "Giảm" : "Khấu trừ"}</span>
            </div>

            <!-- Right details -->
            <div class="flex-1 p-4">
              <div class="flex items-center justify-between gap-2 mb-1">
                <div class="flex items-center gap-2">
                  <code class="font-black text-slate-800 tracking-widest text-sm bg-slate-100 px-2 py-0.5 rounded-md">${v.code}</code>
                  ${expiringSoon ? '<span class="text-[10px] font-bold bg-red-100 text-red-500 px-2 py-0.5 rounded-full uppercase">Sắp hết hạn</span>' : ""}
                </div>
                <button onclick="copyCode('${v.code}')" 
                  class="flex items-center gap-1 text-xs font-bold text-primary hover:bg-primary/10 px-3 py-1.5 rounded-lg transition-colors">
                  <span class="material-symbols-outlined text-[14px]">content_copy</span>
                  Sao chép
                </button>
              </div>
              <p class="text-xs text-slate-500 leading-relaxed mb-2">${conditionText}</p>
              ${endLabel}
            </div>
          </div>
        </div>`;
    });

    // expose copycode to global scope for onclick
    window.copyCode = copyCode;

  } catch (err) {
    list.innerHTML = '<div class="col-span-2 text-center py-12 text-red-400 text-sm">Lỗi kết nối, vui lòng thử lại.</div>';
  }
});
