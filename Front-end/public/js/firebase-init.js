import { initializeApp } from "https:// www.gstatic.com/firebasejs/10.10.0/firebase-app.js";
import { getMessaging, getToken, onMessage } from "https:// www.gstatic.com/firebasejs/10.10.0/firebase-messaging.js";

const firebaseConfig = {
    apiKey: "AIzaSyA19cqbLvyWdFtbd3uM7a0aYgr4BJzt4x8",
    authDomain: "poly-gear.firebaseapp.com",
    projectId: "poly-gear",
    storageBucket: "poly-gear.firebasestorage.app",
    messagingSenderId: "169981413560",
    appId: "1:169981413560:web:687702b5fbb48b52dbcc4d",
    measurementId: "G-SC2LM28K3E"
};

// initialize firebase
const app = initializeApp(firebaseConfig);
const messaging = getMessaging(app);

// request permission and get token
export async function setupFirebaseMessaging() {
    try {
        console.log("Xác thực quyền nhận Thông báo...");
        const permission = await Notification.requestPermission();
        if (permission === 'granted') {
            console.log("Quyền Thông báo đã được cấp.");

            // request sw first
            const registration = await navigator.serviceWorker.register('/firebase-messaging-sw.js');

            // get fcm token
            const token = await getToken(messaging, {
                serviceWorkerRegistration: registration
            });
            if (token) {
                console.log("FCM Token nhận được:", token);
                // gửi token này lên backend để gắn vào user
                sendTokenToBackend(token);
            } else {
                console.log("Không lấy được FCM Token. (Hãy kiểm tra web push cert nếu cần).");
            }
        } else {
            console.log("Quyền Thông báo bị từ chối.");
        }
    } catch (err) {
        console.log("Lỗi khi request FCM Token:", err);
    }
}

// hàm gởi token lên server để lưu vào db
async function sendTokenToBackend(token) {
    try {
        const res = await fetch("https:// polygearid.ivi.vn/back-end/api/account/update_fcm", {
            credentials: 'include',
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ fcm_token: token })
        });
        const data = await res.json();
        if (data.status === 'success') {
            console.log("Đồng bộ máy chủ FCM thành công.");
        }
    } catch (err) {
        console.log("Không thể cập nhật FCM token", err);
    }
}

// chạy setup
setupFirebaseMessaging();

// 
// in-app notification logic (bell & toast)
// 

// hàm lấy thông báo từ database
async function fetchInAppNotifications() {
    try {
        const res = await fetch("https:// polygearid.ivi.vn/back-end/api/account/notifications", {
            credentials: 'include'
        });
        const data = await res.json();

        if (data.status === 'success') {
            renderNotifications(data.data);
            showLatestUnreadToast(data.data);
        }
    } catch (err) {
        console.log("Không thể lấy log thông báo:", err);
    }
}

// render chuông thông báo
function renderNotifications(notifs) {
    const listEl = document.getElementById("notif-list");
    const badgeEl = document.getElementById("notif-badge");
    if (!listEl || !badgeEl) return;

    if (!notifs || notifs.length === 0) {
        listEl.innerHTML = `<li style="font-size: 13px; color: #666; text-align: center; padding: 10px 0;">Chưa có thông báo nào</li>`;
        badgeEl.style.display = "none";
        return;
    }

    let unreadCount = 0;
    listEl.innerHTML = "";

    notifs.forEach(n => {
        if (n.is_read == 0) unreadCount++;
        const bg = n.is_read == 0 ? "#f0f8ff" : "#fff";
        const fw = n.is_read == 0 ? "bold" : "normal";
        const date = new Date(n.created_at).toLocaleString('vi-VN');

        const li = document.createElement("li");
        li.style.cssText = `padding: 10px; border-bottom: 1px solid #eee; background: ${bg}; font-weight: ${fw};`;
        li.innerHTML = `
            <div style="font-size: 13px; color: #333; margin-bottom: 3px;">${n.title}</div>
            <div style="font-size: 12px; color: #555;">${n.message}</div>
            <div style="font-size: 10px; color: #999; margin-top: 5px;">${date}</div>
        `;
        listEl.appendChild(li);
    });

    if (unreadCount > 0) {
        badgeEl.textContent = unreadCount;
        badgeEl.style.display = "flex";
    } else {
        badgeEl.style.display = "none";
    }
}

// hiển thị toast popup của thông báo chưa đọc mới nhất trong 3 giây
function showLatestUnreadToast(notifs) {
    if (!notifs || notifs.length === 0) return;

    // tìm cái chưa đọc mới nhất
    const latestUnread = notifs.find(n => n.is_read == 0);
    if (!latestUnread) return;

    // xem localstorage nó có pop lên chưa để tránh mỗi lần f5 pop mãi
    const key = "toast_shown_" + latestUnread.id;
    if (localStorage.getItem(key)) return;

    // tạo toast
    createToast(latestUnread.title, latestUnread.message);
    localStorage.setItem(key, "1");
}

function createToast(title, message) {
    const toast = document.createElement("div");
    toast.style.cssText = `
        position: fixed; top: 100px; right: -350px; width: 300px; background: #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.2); 
        border-radius: 8px; padding: 15px; border-left: 5px solid #007bff; z-index: 9999; transition: right 0.5s ease-in-out;
        display: flex; flex-direction: column; gap: 5px; cursor: pointer;
    `;
    toast.innerHTML = `
        <div style="display:flex; justify-content:space-between; align-items:center;">
            <strong style="font-size:14px; color:#333;">${title}</strong>
            <i class="fa-solid fa-xmark close-toast" style="color:#999;"></i>
        </div>
        <div style="font-size:13px; color:#666;">${message}</div>
    `;

    document.body.appendChild(toast);

    // animate vào
    setTimeout(() => { toast.style.right = "20px"; }, 100);

    // click toast dẫn tới đơn mua
    toast.onclick = (e) => {
        if (!e.target.classList.contains("close-toast")) {
            window.location.href = "/history";
        }
    };

    const closeBtn = toast.querySelector(".close-toast");
    closeBtn.onclick = (e) => {
        e.stopPropagation();
        toast.style.right = "-350px";
        setTimeout(() => toast.remove(), 500);
    };

    // tự biến mất trong 3-5 giây
    setTimeout(() => {
        if (document.body.contains(toast)) {
            toast.style.right = "-350px";
            setTimeout(() => {
                if (document.body.contains(toast)) toast.remove();
            }, 500);
        }
    }, 4000); // 4 giây
}

// lắng nghe message khi đang mở web
onMessage(messaging, (payload) => {
    console.log("Message received in foreground: ", payload);
    const notificationTitle = payload.notification?.title || 'Thông báo mới từ PolyGear';
    const notificationOptions = {
        body: payload.notification?.body,
        icon: '/img/logo.png'
    };

    // đẩy notification hệ thống
    if (Notification.permission === 'granted') {
        new Notification(notificationTitle, notificationOptions);
    }

    // đẩy in-app toast mới
    createToast(notificationTitle, payload.notification?.body);

    // lấy lại danh sách chuông
    fetchInAppNotifications();
});

// ui event: toggle dropdown & mark read
document.addEventListener("DOMContentLoaded", () => {
    // chỉ chạy nếu đang có ui chuông
    const bellContainer = document.querySelector(".notification-dropdown");
    if (bellContainer) {
        fetchInAppNotifications();

        const menu = bellContainer.querySelector(".notif-dropdown-menu");

        bellContainer.addEventListener("click", async (e) => {
            if (e.target.closest("#notif-list")) return;

            const isOpening = menu.style.display === "none";
            menu.style.display = isOpening ? "block" : "none";

            // nếu đang mở ra, tự động đánh dấu đã đọc ngầm
            if (isOpening) {
                try {
                    await fetch("https:// polygearid.ivi.vn/back-end/api/account/notifications?mark_read=1", { credentials: 'include' });
                    // refresh lại số badge ngầm sau khi đánh dấu
                    const badgeEl = document.getElementById("notif-badge");
                    if (badgeEl) badgeEl.style.display = "none";

                    // cập nhật lại màu nền các item trong list thành trắng (đã đọc)
                    const items = document.querySelectorAll("#notif-list li");
                    items.forEach(li => {
                        li.style.background = "#fff";
                        li.style.fontWeight = "normal";
                    });
                } catch (err) {
                    console.log("Auto-mark read failed:", err);
                }
            }
        });

        // đóng chuông khi click ra ngoài
        document.addEventListener("click", (e) => {
            if (!bellContainer.contains(e.target)) {
                menu.style.display = "none";
            }
        });

        const markReadBtn = document.getElementById("mark-all-read");
        if (markReadBtn) {
            markReadBtn.addEventListener("click", async () => {
                await fetch("https:// polygearid.ivi.vn/back-end/api/account/notifications?mark_read=1", { credentials: 'include' });
                menu.style.display = "none";
                fetchInAppNotifications();
            });
        }
    }
});
