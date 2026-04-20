window.initAIChat = () => {
    const container = document.getElementById('ai-chat-widget');
    if (!container) return;
    if (window.aiChatInitialized) return;
    window.aiChatInitialized = true;

    const API = 'https:// polygearid.ivi.vn/back-end';

    container.innerHTML = `
        <!-- Toggle Button -->
        <div class="ai-chat-btn" id="aiChatBtn" title="Chat với trợ lý AI">
            <i class="fa-solid fa-robot"></i>
            <span class="ai-badge" id="aiBadge" style="display:none">1</span>
        </div>

        <!-- Chat Box -->
        <div class="ai-chat-box" id="aiChatBox">

            <!-- Header -->
            <div class="ai-chat-header">
                <div class="ai-chat-avatar"><i class="fa-solid fa-microchip"></i></div>
                <div class="ai-chat-header-info">
                    <h3>PolyGear AI</h3>
                    <div class="ai-status">
                        <span class="ai-status-dot"></span>
                        Trực tuyến · Sẵn sàng tư vấn
                    </div>
                </div>
                <button id="aiChatClose" title="Đóng"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <!-- Messages -->
            <div class="ai-chat-body" id="aiChatBody">

                <!-- Welcome -->
                <div class="ai-date-sep">Hôm nay</div>
                <div class="ai-msg-row bot">
                    <div class="ai-msg-avatar-small"><i class="fa-solid fa-robot"></i></div>
                    <div class="ai-msg bot">
                        Xin chào! Tôi là trợ lý AI của <strong>PolyGear</strong> 👋<br>
                        Bạn cần tư vấn linh kiện gì hôm nay?
                    </div>
                </div>

                <!-- Quick chips -->
                <div class="ai-chips" id="aiChips">
                    <span class="ai-chip" onclick="aiChipClick(this)">🖥️ Build PC gaming</span>
                    <span class="ai-chip" onclick="aiChipClick(this)">💡 Nâng cấp RAM</span>
                    <span class="ai-chip" onclick="aiChipClick(this)">🖱️ Chuột gaming</span>
                    <span class="ai-chip" onclick="aiChipClick(this)">💾 Tìm SSD nhanh</span>
                </div>

                <!-- Thinking indicator (always last) -->
                <div class="ai-thinking-row" id="aiThinkingRow">
                    <div class="ai-msg-avatar-small"><i class="fa-solid fa-robot"></i></div>
                    <div class="ai-thinking-bubble">
                        <div class="ai-thinking-icon"></div>
                        <div class="ai-thinking-text"><span id="aiThinkingText">Đang suy nghĩ...</span></div>
                    </div>
                </div>
            </div>

            <!-- Input -->
            <div class="ai-chat-input">
                <input type="text" id="aiChatInput" placeholder="Nhập câu hỏi..." autocomplete="off">
                <button id="aiChatSubmit" title="Gửi">
                    <i class="fa-solid fa-paper-plane"></i>
                </button>
            </div>
        </div>
    `;

    const btn = document.getElementById('aiChatBtn');
    const box = document.getElementById('aiChatBox');
    const closeBtn = document.getElementById('aiChatClose');
    const body = document.getElementById('aiChatBody');
    const input = document.getElementById('aiChatInput');
    const submit = document.getElementById('aiChatSubmit');
    const typingRow = document.getElementById('aiThinkingRow');
    const thinkingText = document.getElementById('aiThinkingText');
    const badge = document.getElementById('aiBadge');

    // thinking stage messages
    const THINKING_STAGES = [
        'Đang suy nghĩ...',
        'Đang phân tích yêu cầu...',
        'Đang tìm kiếm sản phẩm...',
        'Đang tổng hợp tư vấn...'
    ];
    let thinkingTimer = null;

    const startThinking = () => {
        let idx = 0;
        thinkingText.textContent = THINKING_STAGES[0];
        typingRow.classList.add('active');
        body.scrollTop = body.scrollHeight;
        thinkingTimer = setInterval(() => {
            idx = (idx + 1) % THINKING_STAGES.length;
            thinkingText.textContent = THINKING_STAGES[idx];
        }, 4000);
    };

    const stopThinking = () => {
        clearInterval(thinkingTimer);
        typingRow.classList.remove('active');
    };

    let isOpen = false;

    // toggle open / close
    btn.addEventListener('click', () => {
        isOpen = !isOpen;
        box.classList.toggle('open', isOpen);
        badge.style.display = 'none';
        if (isOpen) {
            input.focus();
            if (!window.aiChatLoaded) loadHistory();
        }
    });
    closeBtn.addEventListener('click', () => {
        isOpen = false;
        box.classList.remove('open');
    });

    // format currency
    const fmt = (p) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(p);

    // append message row
    const appendMsg = (role, html, products) => {
        const row = document.createElement('div');
        row.className = `ai-msg-row ${role}`;

        const avatarHTML = role === 'bot'
            ? `<div class="ai-msg-avatar-small"><i class="fa-solid fa-robot"></i></div>`
            : `<div class="ai-msg-avatar-small"><i class="fa-solid fa-user"></i></div>`;

        row.innerHTML = `${role === 'bot' ? avatarHTML : ''}<div class="ai-msg ${role}">${html}</div>${role === 'user' ? avatarHTML : ''}`;

        body.insertBefore(row, typingRow);

        // product slider with prev/next
        if (products && products.length > 0) {
            const slider = document.createElement('div');
            slider.className = 'ai-product-slider';

            const CARD_W = 140; // px — must match css
            const GAP    = 10;  // px gap between cards
            const PER_PAGE = 2;
            let currentIdx = 0;

            // carousel + window wrapper (window clips, carousel slides)
            const window_ = document.createElement('div');
            window_.className = 'ai-carousel-window';

            const carousel = document.createElement('div');
            carousel.className = 'ai-product-carousel';

            products.forEach(p => {
                const price = (p.sale_price && +p.sale_price > 0) ? p.sale_price : p.price;
                const hasSale = p.sale_price && +p.sale_price > 0 && +p.sale_price < +p.price;

                const card = document.createElement('div');
                card.className = 'ai-product-card';
                card.onclick = (e) => {
                    if (e.target.closest('.add-cart-btn')) return;
                    window.location.href = `/detail/${p.sku}`;
                };
                card.innerHTML = `
                    <img src="${p.main_image_url || ''}" alt="${p.name}" onerror="this.src='/img/placeholder.png'">
                    <div class="p-cat">${p.category_name || ''}</div>
                    <div class="p-name" title="${p.name}">${p.name}</div>
                    <div class="p-price ${hasSale ? 'sale' : ''}">${fmt(price)}</div>
                    <button class="add-cart-btn" onclick="aiAddToCart(${JSON.stringify(p).replace(/"/g, '&quot;')}, this)">
                        <i class="fa-solid fa-cart-plus"></i> Chọn Mua
                    </button>
                `;
                carousel.appendChild(card);
            });

            window_.appendChild(carousel);

            // prev/next buttons
            const prevBtn = document.createElement('button');
            prevBtn.className = 'ai-slider-btn prev';
            prevBtn.innerHTML = '<i class="fa-solid fa-chevron-left"></i>';

            const nextBtn = document.createElement('button');
            nextBtn.className = 'ai-slider-btn next';
            nextBtn.innerHTML = '<i class="fa-solid fa-chevron-right"></i>';

            // dot indicators
            const dotsEl = document.createElement('div');
            dotsEl.className = 'ai-slider-dots';
            const totalPages = Math.ceil(products.length / PER_PAGE);
            for (let i = 0; i < totalPages; i++) {
                const dot = document.createElement('span');
                dot.className = 'ai-slider-dot' + (i === 0 ? ' active' : '');
                dot.addEventListener('click', () => goTo(i));
                dotsEl.appendChild(dot);
            }

            const goTo = (idx) => {
                currentIdx = Math.max(0, Math.min(idx, totalPages - 1));
                const offset = currentIdx * PER_PAGE * (CARD_W + GAP);
                carousel.style.transform = `translateX(-${offset}px)`;
                dotsEl.querySelectorAll('.ai-slider-dot').forEach((d, i) => {
                    d.classList.toggle('active', i === currentIdx);
                });
                prevBtn.classList.toggle('disabled', currentIdx === 0);
                nextBtn.classList.toggle('disabled', currentIdx >= totalPages - 1);
            };

            prevBtn.addEventListener('click', () => goTo(currentIdx - 1));
            nextBtn.addEventListener('click', () => goTo(currentIdx + 1));

            prevBtn.classList.add('disabled');
            if (totalPages <= 1) {
                nextBtn.classList.add('disabled');
                prevBtn.style.display = 'none';
                nextBtn.style.display = 'none';
                dotsEl.style.display = 'none';
                slider.style.padding = '0';
            }

            slider.appendChild(prevBtn);
            slider.appendChild(window_);
            slider.appendChild(nextBtn);
            body.insertBefore(slider, typingRow);
            body.insertBefore(dotsEl, typingRow);
        }

        body.scrollTop = body.scrollHeight;
    };

    // load history from session
    const loadHistory = async () => {
        window.aiChatLoaded = true;
        try {
            const res = await fetch(`${API}/api/ai/chat/history`, { credentials: 'include' });
            const data = await res.json();
            if (data.status === 'success' && data.data?.length) {
                data.data.forEach(m => {
                    appendMsg(m.role === 'user' ? 'user' : 'bot', m.content, m.products);
                    if (m.pc_configs && m.pc_configs.length > 0) {
                        m.pc_configs.forEach(config => renderPcConfig(config));
                    }
                });
            }
        } catch (e) { console.warn('History load failed', e); }
    };

    // send message
    const sendMessage = async (text) => {
        text = text || input.value.trim();
        if (!text) return;

        input.value = '';
        const chips = document.getElementById('aiChips');
        if (chips) chips.remove();

        appendMsg('user', text);
        startThinking();

        try {
            const req = await fetch(`${API}/api/ai/chat/send`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify({ message: text })
            });
            const data = await req.json();
            stopThinking();

            if (data.status === 'success') {
                // capture anchor before inserting anything
                const scrollAnchor = typingRow.previousElementSibling;

                appendMsg('bot', data.answer, data.suggested_products);
                // render separate pc build config blocks if present
                if (data.pc_configs && data.pc_configs.length > 0) {
                    data.pc_configs.forEach(config => renderPcConfig(config));
                }

                // scroll to the first new message, not the last
                setTimeout(() => {
                    const firstNew = scrollAnchor
                        ? scrollAnchor.nextElementSibling
                        : body.firstElementChild;
                    if (firstNew) firstNew.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }, 50);
            } else {
                appendMsg('bot', `⚠️ ${data.message || 'Lỗi không xác định'}`);
            }
        } catch (e) {
            stopThinking();
            appendMsg('bot', '⚠️ Mất kết nối mạng, vui lòng thử lại.');
            console.error(e);
        }
    };

    submit.addEventListener('click', () => sendMessage());
    input.addEventListener('keypress', e => { if (e.key === 'Enter') sendMessage(); });

    window.aiChipClick = (el) => sendMessage(el.textContent.replace(/^[^\w]+/, '').trim());

    // render a single pc build config block
    const renderPcConfig = (config) => {
        const fmt = (p) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(p);
        const products = config.products || [];
        const title = config.title || 'Cấu hình';
        const description = config.description || '';
        const total = config.total || 0;

        // 1. description bubble (bot message)
        const descHtml = `
            <div style="font-size:13.5px; line-height:1.6;">
                <div style="font-weight:700; color:#1544b7; margin-bottom:4px; display:flex; align-items:center; gap:5px;">
                    <i class="fa-solid fa-desktop"></i> ${title}
                </div>
                <div style="color:#334155;">${description}</div>
            </div>`;
        appendMsg('bot', descHtml);

        // 2. product card block
        const block = document.createElement('div');
        block.className = 'ai-pc-config-block';
        block.style.cssText = 'background:#fff; border:1.5px solid #e2e8f0; border-radius:14px; padding:14px; margin-top:4px; box-shadow:0 4px 12px rgba(21,68,183,0.07); animation: msg-in 0.3s ease both;';

        // product slider
        const slider = document.createElement('div');
        slider.className = 'ai-product-slider';

        const CARD_W = 140;
        const GAP    = 10;
        const PER_PAGE = 2;
        let currentIdx = 0;

        const window_ = document.createElement('div');
        window_.className = 'ai-carousel-window';

        const carousel = document.createElement('div');
        carousel.className = 'ai-product-carousel';

        products.forEach(p => {
            const price = (p.sale_price && +p.sale_price > 0) ? p.sale_price : p.price;
            const hasSale = p.sale_price && +p.sale_price > 0 && +p.sale_price < +p.price;
            const card = document.createElement('div');
            card.className = 'ai-product-card';
            card.onclick = (e) => {
                if (e.target.closest('.add-cart-btn')) return;
                window.location.href = `/detail/${p.sku}`;
            };
            card.innerHTML = `
                <img src="${p.main_image_url || ''}" alt="${p.name}" onerror="this.src='/img/placeholder.png'">
                <div class="p-cat">${p.category_name || ''}</div>
                <div class="p-name" title="${p.name}">${p.name}</div>
                <div class="p-price ${hasSale ? 'sale' : ''}">${fmt(price)}</div>
                <button class="add-cart-btn" onclick="aiAddToCart(${JSON.stringify(p).replace(/"/g, '&quot;')}, this)">
                    <i class="fa-solid fa-cart-plus"></i> Mua
                </button>
            `;
            carousel.appendChild(card);
        });

        window_.appendChild(carousel);

        const prevBtn = document.createElement('button');
        prevBtn.className = 'ai-slider-btn prev';
        prevBtn.innerHTML = '<i class="fa-solid fa-chevron-left"></i>';

        const nextBtn = document.createElement('button');
        nextBtn.className = 'ai-slider-btn next';
        nextBtn.innerHTML = '<i class="fa-solid fa-chevron-right"></i>';

        const dotsEl = document.createElement('div');
        dotsEl.className = 'ai-slider-dots';
        const totalPages = Math.ceil(products.length / PER_PAGE);
        for (let i = 0; i < totalPages; i++) {
            const dot = document.createElement('span');
            dot.className = 'ai-slider-dot' + (i === 0 ? ' active' : '');
            dot.addEventListener('click', () => goTo(i));
            dotsEl.appendChild(dot);
        }

        const goTo = (idx) => {
            currentIdx = Math.max(0, Math.min(idx, totalPages - 1));
            const offset = currentIdx * PER_PAGE * (CARD_W + GAP);
            carousel.style.transform = `translateX(-${offset}px)`;
            dotsEl.querySelectorAll('.ai-slider-dot').forEach((d, i) => d.classList.toggle('active', i === currentIdx));
            prevBtn.classList.toggle('disabled', currentIdx === 0);
            nextBtn.classList.toggle('disabled', currentIdx >= totalPages - 1);
        };
        prevBtn.addEventListener('click', () => goTo(currentIdx - 1));
        nextBtn.addEventListener('click', () => goTo(currentIdx + 1));
        prevBtn.classList.add('disabled');
        if (totalPages <= 1) {
            prevBtn.style.display = 'none';
            nextBtn.style.display = 'none';
            dotsEl.style.display = 'none';
        }

        slider.appendChild(prevBtn);
        slider.appendChild(window_);
        slider.appendChild(nextBtn);
        block.appendChild(slider);
        block.appendChild(dotsEl);

        // footer: total + buy button
        const footer = document.createElement('div');
        footer.style.cssText = 'display:flex; justify-content:space-between; align-items:center; border-top:1px dashed #e2e8f0; padding-top:10px; margin-top:8px;';
        footer.innerHTML = `
            <div>
                <div style="font-size:11px; color:#64748b;">Tổng tiền</div>
                <strong style="color:#ef4444; font-size:15px;">${fmt(total)}</strong>
            </div>
            <button onclick="window.aiAddMultiToCartAndCheckout(this, ${JSON.stringify(products).replace(/"/g, '&quot;')})" style="background:linear-gradient(135deg,#1544b7,#2a83e9); color:#fff; border:none; padding:7px 13px; border-radius:8px; font-weight:700; font-size:12px; cursor:pointer; display:flex; align-items:center; gap:5px;">
                <i class="fa-solid fa-cart-arrow-down"></i> Mua ngay
            </button>
        `;
        block.appendChild(footer);

        body.insertBefore(block, typingRow);
        // do not auto-scroll — sendmessage handles scroll to first new element
    };
};

// add to cart from chat
window.aiAddToCart = async (product, btn) => {
    const sku = product.sku;
    const name = product.name;
    const price = (product.sale_price && +product.sale_price > 0) ? +product.sale_price : +product.price;
    const originalPrice = +product.price;
    const img = product.main_image_url;

    btn.disabled = true;
    const originalHTML = btn.innerHTML;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';

    try {
        const authRes = await fetch("https:// polygearid.ivi.vn/back-end/api/auth/islogin", { credentials: 'include' });
        const authData = await authRes.json();

        if (authData.status === "success") {
            const res = await fetch('https:// polygearid.ivi.vn/back-end/api/cart/addtocart', {
                method: 'POST',
                body: JSON.stringify({ sku: sku }),
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include'
            });
            const data = await res.json();

            if (data.status === 'success') {
                btn.innerHTML = '<i class="fa-solid fa-check"></i> Đã thêm';
                btn.style.background = '#22c55e';
                const cartQuantity = document.getElementById("cart-quantity");
                if (cartQuantity) {
                    cartQuantity.innerText = data.quantity;
                    cartQuantity.style.display = "flex";
                }
                document.dispatchEvent(new CustomEvent("cartUpdated"));
                setTimeout(() => {
                    btn.innerHTML = originalHTML;
                    btn.style.background = '';
                    btn.disabled = false;
                }, 2000);
            } else {
                throw new Error(data.message);
            }
        } else {
            let localCart = JSON.parse(localStorage.getItem("cartData")) || { items: {} };
            if (localCart.items[sku]) {
                localCart.items[sku].quantity += 1;
            } else {
                localCart.items[sku] = {
                    sku: sku,
                    name: name,
                    price: price,
                    originalPrice: originalPrice,
                    main_img_url: img,
                    quantity: 1,
                    selected: true,
                };
            }
            localStorage.setItem("cartData", JSON.stringify(localCart));
            localStorage.setItem("cartChangeTime", Date.now());

            const cartQuantity = document.getElementById("cart-quantity");
            if (cartQuantity) {
                let totalQty = 0;
                Object.values(localCart.items).forEach(item => totalQty += item.quantity);
                cartQuantity.innerText = totalQty;
                cartQuantity.style.display = "flex";
            }

            btn.innerHTML = '<i class="fa-solid fa-check"></i> Đã thêm';
            btn.style.background = '#22c55e';
            setTimeout(() => {
                btn.innerHTML = originalHTML;
                btn.style.background = '';
                btn.disabled = false;
            }, 2000);
        }
    } catch (e) {
        console.error("Add to cart error:", e);
        btn.innerHTML = '<i class="fa-solid fa-xmark"></i> Lỗi';
        btn.style.background = '#ef4444';
        setTimeout(() => {
            btn.innerHTML = originalHTML;
            btn.style.background = '';
            btn.disabled = false;
        }, 2000);
    }
};

// multi add to cart from ai pc build
window.aiAddMultiToCartAndCheckout = async (btn, products) => {
    if (!products || !products.length) return;
    
    const originalHTML = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang xử lý...';

    try {
        const authRes = await fetch("https:// polygearid.ivi.vn/back-end/api/auth/islogin", { credentials: 'include' });
        const authData = await authRes.json();

        if (authData.status !== "success") {
            // chưa login -> bật modal login
            btn.innerHTML = originalHTML;
            btn.disabled = false;
            const loginBtn = document.getElementById("btnOpenLogin");
            if (loginBtn) {
                loginBtn.click();
            } else {
                alert("Vui lòng đăng nhập để thanh toán!");
            }
            return; // dừng lại, không add cart hay checkout
        }

        // đã login -> lưu vào db
        for (const p of products) {
            await fetch('https:// polygearid.ivi.vn/back-end/api/cart/addtocart', {
                method: 'POST',
                body: JSON.stringify({ sku: p.sku }),
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include'
            });
        }

        // cập nhật localstorage selection để qua trang checkout nhận diện
        let localCart = JSON.parse(localStorage.getItem("cartData")) || { items: {} };
        
        for (let key in localCart.items) {
            localCart.items[key].selected = false;
        }

        for (const p of products) {
            const sku = p.sku;
            const price = (p.sale_price && +p.sale_price > 0) ? +p.sale_price : +p.price;
            
            if (localCart.items[sku]) {
                localCart.items[sku].selected = true;
            } else {
                localCart.items[sku] = {
                    sku: sku,
                    name: p.name,
                    price: price,
                    originalPrice: +p.price,
                    main_img_url: p.main_image_url,
                    quantity: 1,
                    selected: true,
                };
            }
        }

        let selectedCount = 0;
        for (let key in localCart.items) {
            if (localCart.items[key].selected) selectedCount++;
        }
        localCart.totalSelectedCount = selectedCount;

        localStorage.setItem("cartData", JSON.stringify(localCart));
        localStorage.setItem("cartChangeTime", Date.now());

        btn.innerHTML = '<i class="fa-solid fa-check"></i> Chuyển trang...';
        btn.style.background = '#22c55e';

        setTimeout(() => {
            window.location.href = '/checkout';
        }, 800);

    } catch (e) {
        console.error("Multi add to cart error:", e);
        btn.innerHTML = '<i class="fa-solid fa-xmark"></i> Lỗi';
        btn.style.background = '#ef4444';
        setTimeout(() => {
            btn.innerHTML = originalHTML;
            btn.style.background = '';
            btn.disabled = false;
        }, 2000);
    }
};
