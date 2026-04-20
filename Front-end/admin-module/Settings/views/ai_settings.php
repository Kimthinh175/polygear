<main class="main-content">
    <div class="main-content-inner">

        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem;">
            <div>
                <h1 class="page-title" style="margin: 0;">Hiệu chỉnh Hệ thống AI</h1>
                <p class="text-muted text-sm" style="margin-top: 0.25rem;">Quản lý cấu hình Model của trợ lý AI Tư vấn Bán Hàng</p>
            </div>
        </div>

        <div class="card" style="max-width: 800px; margin: 0 auto; padding: 2rem;">
            
            <!-- Warning Alert -->
            <div style="background: linear-gradient(135deg, #fffbeb, #fef3c7); border: 1px solid #fde68a; border-radius: 12px; padding: 1.2rem; display: flex; gap: 1rem; margin-bottom: 2rem;">
                <div style="width: 40px; height: 40px; background: #f59e0b; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <i class="fa-solid fa-robot" style="color: white; font-size: 1.25rem;"></i>
                </div>
                <div>
                    <h4 style="margin:0 0 0.25rem 0; color: #92400e; font-size: 0.95rem; font-weight: 700;">Lưu ý về Bộ nhớ Đệm (Cache)</h4>
                    <p style="margin:0; font-size: 0.85rem; color: #b45309; line-height: 1.5;">Nếu chuyển sang dùng chuẩn mô hình của <strong>Google Gemini</strong>, hệ thống Cache sẽ tự động được làm mới. Nếu là các mô hình <strong>Groq API</strong>, hệ thống sẽ bỏ qua tính năng Cache vì Groq phản hồi Realtime tốc độ cực cao, không cần lưu trữ dữ liệu tĩnh ở hệ thống của họ.</p>
                </div>
            </div>

            <form id="aiConfigForm" style="display: flex; flex-direction: column; gap: 1.5rem;">
                
                <!-- Provider Selection -->
                <div>
                    <label style="font-weight: 600; font-size: 0.9rem; color: #334155; display: block; margin-bottom: 0.5rem;">Hệ thống AI (Provider)</label>
                    <select id="aiProvider" class="form-control" style="font-size: 0.95rem; padding: 0.65rem 1rem;">
                        <option value="google">Google Cloud (Gemini API)</option>
                        <option value="groq">Groq (LPU Inference Engine)</option>
                    </select>
                </div>

                <!-- Google Models -->
                <div id="googleModelGroup">
                    <label style="font-weight: 600; font-size: 0.9rem; color: #334155; display: block; margin-bottom: 0.5rem;">Mô hình Google</label>
                    <select id="googleModel" class="form-control" style="font-size: 0.95rem; padding: 0.65rem 1rem;">
                        <option value="gemini-3-flash-preview">gemini-3-flash-preview (Mặc định)</option>
                        <option value="gemini-2.5-flash">gemini-2.5-flash</option>
                        <option value="gemini-1.5-flash">gemini-1.5-flash</option>
                        <option value="gemini-1.5-pro">gemini-1.5-pro</option>
                    </select>
                </div>

                <!-- Groq Models -->
                <div id="groqModelGroup" style="display: none;">
                    <label style="font-weight: 600; font-size: 0.9rem; color: #334155; display: block; margin-bottom: 0.5rem;">Mô hình Groq</label>
                    <select id="groqModel" class="form-control" style="font-size: 0.95rem; padding: 0.65rem 1rem;">
                        <option value="llama-3.1-70b-versatile">Groq 70B (Llama 3.1 70B)</option>
                        <option value="llama-3.3-70b-specdec">Groq 70B Speculative (Llama 3.3)</option>
                        <option value="groq-120b">Groq 120B (Experimental)</option>
                        <option value="mixtral-8x7b-32768">Groq 46B (Mixtral 8x7B)</option>
                        <option value="gemma2-9b-it">Groq 9B (Gemma 2)</option>
                    </select>
                    <p style="font-size: 0.75rem; color: #64748b; margin-top: 0.5rem;"><i class="fa-solid fa-bolt"></i> Tốc độ sinh text có thể đạt kỉ lục +800 tokens/s.</p>
                </div>

                <!-- API Key -->
                <div>
                    <label style="font-weight: 600; font-size: 0.9rem; color: #334155; display: block; margin-bottom: 0.5rem;">
                        Khóa API (Tuỳ chọn)
                    </label>
                    <input type="password" id="apiKey" class="form-control" placeholder="Để trống để dùng mã gốc từ hệ thống (.env)" style="font-size: 0.95rem; padding: 0.65rem 1rem;">
                    <p style="font-size: 0.75rem; color: #64748b; margin-top: 0.5rem;">Bảo mật: Mã khoá cấp phát sẽ được lưu nội bộ trên máy chủ. Nếu bạn sử dụng Groq mà file .env chưa có khóa GROQ_API_KEY, bạn <strong>bắt buộc phải nhập vào đây</strong>.</p>
                </div>

                <!-- Submit Action -->
                <div style="margin-top: 1rem; border-top: 1px solid #e2e8f0; padding-top: 1.5rem; display: flex; justify-content: flex-end; gap: 1rem;">
                    <button type="button" class="btn btn-secondary" onclick="updateProductCache()" style="padding: 0.65rem 1.5rem; font-size: 1rem; font-weight: 700; background-color: #f1f5f9; color: #475569; border: 1px solid #cbd5e1;">
                        <i class="fa-solid fa-database" style="margin-right: 0.4rem;"></i> Cache Dữ liệu Sản phẩm
                    </button>
                    <button type="button" class="btn btn-primary" onclick="saveSettings()" style="padding: 0.65rem 2rem; font-size: 1rem; font-weight: 700;">
                        <i class="fa-solid fa-floppy-disk" style="margin-right: 0.4rem;"></i> Lưu Cấu Hình
                    </button>
                </div>

            </form>
        </div>

    </div>
</main>

<script>
document.addEventListener("DOMContentLoaded", () => {
    // ui select toggle
    const providerSel = document.getElementById("aiProvider");
    const gooGrp = document.getElementById("googleModelGroup");
    const groqGrp = document.getElementById("groqModelGroup");

    providerSel.addEventListener("change", () => {
        if (providerSel.value === 'groq') {
            gooGrp.style.display = 'none';
            groqGrp.style.display = 'block';
        } else {
            gooGrp.style.display = 'block';
            groqGrp.style.display = 'none';
        }
    });

    // load initial settings
    fetch('https:// polygearid.ivi.vn/back-end/api/admin/ai/settings', {credentials: 'include'})
        .then(res => res.json())
        .then(res => {
            if (res.status === 'success' && res.data) {
                const config = res.data;
                providerSel.value = config.provider || 'google';
                if (providerSel.value === 'groq') {
                    gooGrp.style.display = 'none';
                    groqGrp.style.display = 'block';
                    document.getElementById('groqModel').value = config.model;
                } else {
                    document.getElementById('googleModel').value = config.model;
                }
                document.getElementById('apiKey').value = config.api_key || '';
            }
        }).catch(err => console.error("Lỗi lấy cấu hình", err));
});

function saveSettings() {
    const provider = document.getElementById("aiProvider").value;
    const model = provider === 'groq' ? document.getElementById("groqModel").value : document.getElementById("googleModel").value;
    const apiKey = document.getElementById("apiKey").value.trim();

    fetch('https:// polygearid.ivi.vn/back-end/api/admin/ai/settings', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        credentials: 'include',
        body: JSON.stringify({provider, model, api_key: apiKey})
    })
    .then(r => r.json())
    .then(data => {
        if(data.status === 'success') {
            alert(data.message);
            // trigger cache update if google
            if (provider === 'google') {
                console.log("Đang Cập nhật lại kho lưu trữ Cache cho mô hình Google...");
                fetch('https:// polygearid.ivi.vn/back-end/api/ai/cacheaidata', { method: 'post', credentials: 'include' })
                    .then(res=>res.json())
                    .then(c => {
                        console.log("Cache Result:", c);
                        alert("Đã phân bổ lại Cache cấu hình kiến thức cho siêu AI Gemini!");
                    });
            }
        }
    }).catch(e => {
        alert("Có lỗi khi lưu " + e);
    })
}

function updateProductCache() {
    const btn = document.querySelector('button[onclick="updateProductCache()"]');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin" style="margin-right: 0.4rem;"></i> Đang cập nhật...';
    btn.disabled = true;

    fetch('https:// polygearid.ivi.vn/back-end/api/ai/cacheproducts', {
        method: 'POST',
        credentials: 'include'
    })
    .then(r => r.json())
    .then(data => {
        if(data.status === 'success') {
            alert(data.message);
        } else {
            alert("Lỗi: " + (data.message || 'Không thể cập nhật'));
        }
    })
    .catch(e => {
        alert("Có lỗi xảy ra: " + e);
    })
    .finally(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}
</script>
