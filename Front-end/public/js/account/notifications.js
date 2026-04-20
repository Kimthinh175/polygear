document.addEventListener("DOMContentLoaded", async () => {
  const list = document.getElementById("notifications-list");
  const empty = document.getElementById("notifications-empty");
  const markAllBtn = document.getElementById("mark-all-read-btn");

  function timeAgo(dateStr) {
    const diff = Math.floor((Date.now() - new Date(dateStr)) / 1000);
    if (diff < 60) return "Vừa xong";
    if (diff < 3600) return `${Math.floor(diff / 60)} phút trước`;
    if (diff < 86400) return `${Math.floor(diff / 3600)} giờ trước`;
    if (diff < 604800) return `${Math.floor(diff / 86400)} ngày trước`;
    return new Date(dateStr).toLocaleDateString("vi-VN", { day: "2-digit", month: "2-digit", year: "numeric" });
  }

  function getIcon(title) {
    const t = (title || "").toLowerCase();
    if (t.includes("đơn") || t.includes("order")) return { icon: "shopping_bag", bg: "bg-blue-50", color: "text-blue-500" };
    if (t.includes("giao") || t.includes("vận chuyển")) return { icon: "local_shipping", bg: "bg-green-50", color: "text-green-500" };
    if (t.includes("hủy") || t.includes("cancel")) return { icon: "cancel", bg: "bg-red-50", color: "text-red-400" };
    if (t.includes("voucher") || t.includes("khuyến mãi") || t.includes("ưu đãi")) return { icon: "confirmation_number", bg: "bg-amber-50", color: "text-amber-500" };
    if (t.includes("hoàn thành")) return { icon: "check_circle", bg: "bg-emerald-50", color: "text-emerald-500" };
    return { icon: "notifications", bg: "bg-slate-100", color: "text-slate-500" };
  }

  try {
    const res = await fetch("https:// polygearid.ivi.vn/back-end/api/account/notifications", { credentials: "include" });
    const data = await res.json();

    list.innerHTML = "";

    if (!data.data || data.data.length === 0) {
      empty.classList.remove("hidden");
      return;
    }

    data.data.forEach(n => {
      const { icon, bg, color } = getIcon(n.title);
      const isUnread = n.is_read == 0;

      list.innerHTML += `
        <div class="flex gap-4 px-6 py-5 ${isUnread ? "bg-blue-50/40" : "bg-white"} hover:bg-slate-50 transition-colors">
          <div class="w-10 h-10 rounded-full ${bg} flex-shrink-0 flex items-center justify-center mt-0.5">
            <span class="material-symbols-outlined ${color} text-[20px]" style="font-variation-settings: 'FILL' 1">${icon}</span>
          </div>
          <div class="flex-1 min-w-0">
            <div class="flex items-start justify-between gap-2">
              <p class="font-bold text-slate-800 text-sm leading-snug ${isUnread ? "" : "font-semibold text-slate-700"}">${n.title || "Thông báo"}</p>
              ${isUnread ? '<span class="w-2 h-2 rounded-full bg-blue-500 flex-shrink-0 mt-1.5"></span>' : ""}
            </div>
            <p class="text-slate-500 text-sm mt-0.5 leading-relaxed">${n.message || ""}</p>
            <p class="text-xs text-slate-400 font-medium mt-2">${timeAgo(n.created_at)}</p>
          </div>
        </div>`;
    });

  } catch (err) {
    list.innerHTML = '<div class="text-center py-12 text-red-400 text-sm">Lỗi kết nối, vui lòng thử lại.</div>';
  }

  markAllBtn.addEventListener("click", async () => {
    markAllBtn.textContent = "Đang xử lý...";
    await fetch("https:// polygearid.ivi.vn/back-end/api/account/notifications?mark_read=1", { credentials: "include" });
    // remove unread indicators
    document.querySelectorAll(".bg-blue-50\\/40").forEach(el => {
      el.classList.remove("bg-blue-50/40");
      el.classList.add("bg-white");
    });
    document.querySelectorAll(".w-2.h-2.rounded-full.bg-blue-500").forEach(el => el.remove());
    markAllBtn.textContent = "Tất cả đã đọc ✓";
    markAllBtn.classList.remove("text-blue-600", "hover:text-blue-700");
    markAllBtn.classList.add("text-slate-400", "cursor-default");
    markAllBtn.disabled = true;
  });
});
