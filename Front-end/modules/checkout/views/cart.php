<title>Giỏ hàng | PolyGear<?= ' | ' . $_SESSION['user']['name'] ?? '' ?></title>
<link href="https:// fonts.googleapis.com/css2?family=material+symbols+outlined:wght,fill@100..700,0..1&amp;display=swap"
  rel="stylesheet" />
<!-- Tailwind CSS Local -->
<link rel="stylesheet" href="css/tailwind.css?v=1.0.4">
<link rel="stylesheet" href="css/layout/swiper.css">
<script src="js/layout/swiper.js"></script>
<style>
  body {
    font-family: "Inter", sans-serif;
  }

  .material-symbols-outlined {
    font-variation-settings:
      "FILL" 0,
      "wght" 400,
      "GRAD" 0,
      "opsz" 24;
  }
</style>

<body class="bg-background-light text-slate-900 font-display">
  <!-- Top Navigation Bar -->

  <nav class="">
    <div class="max-w-7xl mx-auto px-4 py-4 flex items-center gap-2 text-sm pt-4 pb-2">
      <a class="text-slate-500 hover:text-primary transition-colors flex items-center gap-1" href="#">
        <span class="material-symbols-outlined text-lg">home</span>
        Trang chủ
      </a>
      <span class="material-symbols-outlined text-slate-300 text-sm">chevron_right</span>
      <span class="font-medium text-slate-900">Giỏ hàng</span>
    </div>
  </nav>
  <main class="max-w-7xl mx-auto px-4 pt-4 pb-10">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 pb-40">
      <!-- list sản phẩm trong giỏ hàng -->
      <div id="list-product-cart" class="lg:col-span-2 space-y-4">
        <!-- Cart Item 1 -->
        <?php foreach ($data as $product):
          $price = number_format($product['price'], 0, ',', '.');
          $sale = (!empty($product['sale_price']) && $product['sale_price'] > 0) ? number_format($product['sale_price'], 0, ',', '.') : null;
          ?>
          <div
            class="bg-white p-4 rounded-xl shadow-sm border border-slate-200 flex flex-col sm:flex-row gap-4 items-center">
            <div class="flex items-center pr-2">
              <input
                class="item-checkbox rounded text-primary focus:ring-primary border-2 border-primary bg-gray-300 size-5 cursor-pointer transition-all"
                type="checkbox" data-id="<?= $product['sku'] ?>" data-price="<?= $product['price'] ?>"
                data-sale-price="<?= (!empty($product['sale_price']) && $product['sale_price'] > 0) ? $product['sale_price'] : $product['price'] ?>" />
            </div>
            <div class="size-24 bg-slate-100 rounded-lg flex-shrink-0">
              <img alt="Bàn phím" class="w-full h-full object-contain p-2"
                data-alt="Mechanical keyboard with RGB lighting background" src="<?= $product['main_image_url'] ?>" />
            </div>
            <div class="flex-1 min-w-0">
              <a href="/detail/<?= $product['sku'] ?>">
                <h3 class="font-bold text-lg text-slate-900 truncate">
                  <?= $product['variant_name'] ?>
                </h3>
              </a>

              <p class="text-slate-500 text-sm mb-2">
                Màu sắc: Black &amp; Cyan | Switch: Akko CS Jelly Pink
              </p>
              <div class="flex items-center gap-4">
                <span class="font-bold text-primary"><?= $sale ? $sale : $price ?>đ</span>
                <?php if ($sale): ?>
                  <span class="text-slate-400 line-through text-xs"><?= $price ?>đ</span>
                <?php endif; ?>
              </div>
            </div>
            <div class="flex items-center gap-6 w-full sm:w-auto justify-between">
              <div class="flex items-center bg-slate-100 rounded-lg p-1">
                <button data-phone="<?= $_SESSION['user']['phone'] ?>" data-sku="<?= $product['sku'] ?>"
                  class="btn-minus size-8 flex items-center justify-center hover:bg-white:bg-slate-700 rounded-md transition-all">
                  <span id="dec" class="material-symbols-outlined text-sm">remove</span>
                </button>
                <span class="quantity-val w-10 text-center font-bold"><?= $product['quantity'] ?></span>
                <button data-phone="<?= $_SESSION['user']['phone'] ?>" data-sku="<?= $product['sku'] ?>"
                  class="btn-plus size-8 flex items-center justify-center hover:bg-white:bg-slate-700 rounded-md transition-all text-primary">
                  <span id="add" class="material-symbols-outlined text-sm font-bold">add</span>
                </button>
              </div>
              <button class="remove" data-phone="<?= $_SESSION['user']['phone'] ?>" data-sku="<?= $product['sku'] ?>"
                class="text-slate-400 hover:text-red-500 transition-colors">
                <span class="material-symbols-outlined">delete</span>
              </button>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
      <!-- AI Build PC Panel -->
      <div id="ai-consultation-wrapper" style="position:sticky;top:16px;align-self:start;">
        <div id="ai-build-box" style="border-radius:16px;overflow:hidden;box-shadow:0 20px 40px rgba(0,0,0,0.12);border:1px solid #e2e8f0;background:#fff;">

          <!-- Header -->
          <div style="position:relative;background:linear-gradient(135deg,#0f1b3d,#1a2f6b,#2546a8);padding:20px;overflow:hidden;">
            <div style="position:absolute;top:-24px;right:-24px;width:128px;height:128px;background:rgba(255,255,255,0.05);border-radius:50%;"></div>
            <div style="position:absolute;top:32px;right:40px;width:64px;height:64px;background:rgba(96,165,250,0.1);border-radius:50%;"></div>
            <div style="position:relative;">
              <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
                <div style="width:32px;height:32px;background:rgba(255,255,255,0.1);border-radius:8px;display:flex;align-items:center;justify-content:center;">
                  <span class="material-symbols-outlined" style="color:#fff;font-size:20px;font-variation-settings:'FILL' 1;">smart_toy</span>
                </div>
                <span style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;color:#93c5fd;">AI Assistant</span>
              </div>
              <h2 style="font-size:18px;font-weight:900;color:#fff;line-height:1.3;margin:0 0 6px;">Build PC thông minh<br><span style="color:#93c5fd;">theo ngân sách của bạn</span></h2>
              <p style="font-size:11px;color:rgba(191,219,254,0.7);line-height:1.5;margin:0;">Nhập ngân sách, AI sẽ tự chọn linh kiện phù hợp và thêm vào giỏ</p>
            </div>
            <div style="display:flex;gap:6px;margin-top:14px;flex-wrap:wrap;">
              <span style="background:rgba(255,255,255,0.1);color:#bfdbfe;font-size:10px;font-weight:600;padding:4px 10px;border-radius:999px;border:1px solid rgba(255,255,255,0.1);">⚡ Tự động chọn linh kiện</span>
              <span style="background:rgba(255,255,255,0.1);color:#bfdbfe;font-size:10px;font-weight:600;padding:4px 10px;border-radius:999px;border:1px solid rgba(255,255,255,0.1);">🎯 Tối ưu ngân sách</span>
            </div>
          </div>

          <!-- Body -->
          <div style="padding:20px;">

            <!-- CTA ban đầu -->
            <div id="ai-build-cta">
              <button id="btn-expand-ai-build" style="width:100%;display:flex;align-items:center;justify-content:center;gap:8px;background:linear-gradient(90deg,#1544b7,#2546a8);color:#fff;font-weight:700;font-size:14px;padding:12px;border-radius:12px;border:none;cursor:pointer;box-shadow:0 8px 20px rgba(21,68,183,0.3);">
                <span class="material-symbols-outlined" style="font-size:20px;font-variation-settings:'FILL' 1;">auto_awesome</span>
                Bắt đầu Build PC
              </button>
            </div>

            <!-- Form -->
            <div id="ai-build-form" style="display:none;flex-direction:column;gap:16px;">

              <!-- Budget -->
              <div>
                <label style="display:block;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:6px;">
                  <span class="material-symbols-outlined" style="font-size:14px;vertical-align:middle;color:#3b82f6;font-variation-settings:'FILL' 1;">payments</span>
                  Tổng ngân sách
                </label>
                <div style="position:relative;">
                  <input type="number" id="ai-budget" placeholder="VD: 15000000"
                    style="width:100%;box-sizing:border-box;padding:10px 52px 10px 14px;border:2px solid #e2e8f0;border-radius:12px;outline:none;font-size:13px;font-weight:600;"
                    onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e2e8f0'">
                  <span style="position:absolute;right:12px;top:50%;transform:translateY(-50%);font-size:11px;font-weight:700;color:#94a3b8;">VNĐ</span>
                </div>
                <div style="display:flex;gap:6px;margin-top:8px;flex-wrap:wrap;">
                  <?php foreach ([['5tr','5000000'],['10tr','10000000'],['15tr','15000000'],['20tr','20000000']] as [$lbl,$val]): ?>
                  <button type="button" onclick="document.getElementById('ai-budget').value='<?= $val ?>'"
                    style="font-size:11px;font-weight:700;padding:4px 10px;border-radius:8px;background:#f1f5f9;border:1px solid #e2e8f0;color:#475569;cursor:pointer;"
                    onmouseover="this.style.background='#eff6ff';this.style.color='#2563eb';"
                    onmouseout="this.style.background='#f1f5f9';this.style.color='#475569';"><?= $lbl ?></button>
                  <?php endforeach; ?>
                </div>
              </div>

              <!-- Level -->
              <div>
                <label style="display:block;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:6px;">
                  <span class="material-symbols-outlined" style="font-size:14px;vertical-align:middle;color:#3b82f6;font-variation-settings:'FILL' 1;">tune</span>
                  Phân khúc linh kiện
                </label>
                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px;">
                  <?php foreach ([['Cơ bản','eco'],['Trung bình','speed'],['Cao cấp','rocket_launch']] as [$lvl,$ico]): ?>
                  <label style="cursor:pointer;" onclick="selectLevel(this,'<?= $lvl ?>')">
                    <input type="radio" name="ai-level-radio" value="<?= $lvl ?>" <?= $lvl==='Trung bình'?'checked':'' ?> style="display:none;">
                    <div class="level-card" style="display:flex;flex-direction:column;align-items:center;gap:4px;padding:10px 6px;border-radius:12px;border:2px solid <?= $lvl==='Trung bình'?'#3b82f6':'#e2e8f0' ?>;background:<?= $lvl==='Trung bình'?'#eff6ff':'#fff' ?>;text-align:center;">
                      <span class="material-symbols-outlined" style="font-size:20px;color:<?= $lvl==='Trung bình'?'#3b82f6':'#94a3b8' ?>;font-variation-settings:'FILL' 1;"><?= $ico ?></span>
                      <span style="font-size:10px;font-weight:700;color:<?= $lvl==='Trung bình'?'#2563eb':'#475569' ?>;"><?= $lvl ?></span>
                    </div>
                  </label>
                  <?php endforeach; ?>
                </div>
              </div>

              <!-- Task -->
              <div>
                <label style="display:block;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:6px;">
                  <span class="material-symbols-outlined" style="font-size:14px;vertical-align:middle;color:#3b82f6;font-variation-settings:'FILL' 1;">category</span>
                  Mục đích sử dụng
                </label>
                <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:8px;">
                  <?php foreach ([['Chơi game','sports_esports'],['Văn phòng','work'],['Đồ họa','brush'],['Khác','more_horiz']] as [$task,$ico]): ?>
                  <label style="cursor:pointer;" onclick="selectTask(this,'<?= $task ?>')" <?= $task==='Khác'?'id="ai-task-other-label"':'' ?>>
                    <input type="radio" name="ai-task" value="<?= $task ?>" <?= $task==='Chơi game'?'checked':'' ?> <?= $task==='Khác'?'id="ai-task-other-radio"':'' ?> style="display:none;">
                    <div class="task-card" style="display:flex;align-items:center;gap:8px;padding:10px 12px;border-radius:12px;border:2px solid <?= $task==='Chơi game'?'#3b82f6':'#e2e8f0' ?>;background:<?= $task==='Chơi game'?'#eff6ff':'#fff' ?>;">
                      <span class="material-symbols-outlined" style="font-size:18px;color:<?= $task==='Chơi game'?'#3b82f6':'#94a3b8' ?>;font-variation-settings:'FILL' 1;"><?= $ico ?></span>
                      <span style="font-size:12px;font-weight:700;color:<?= $task==='Chơi game'?'#2563eb':'#475569' ?>;"><?= $task ?></span>
                    </div>
                  </label>
                  <?php endforeach; ?>
                </div>
                <input type="hidden" id="ai-level" value="Trung bình">
                <input type="text" id="ai-task-custom" placeholder="Nhập nhu cầu khác..."
                  style="display:none;margin-top:8px;width:100%;box-sizing:border-box;padding:8px 12px;border:2px solid #e2e8f0;border-radius:12px;outline:none;font-size:13px;">
              </div>

              <!-- Submit -->
              <button id="btn-submit-ai-build"
                style="width:100%;display:flex;align-items:center;justify-content:center;gap:8px;background:linear-gradient(90deg,#1544b7,#2546a8);color:#fff;font-weight:700;font-size:14px;padding:12px;border-radius:12px;border:none;cursor:pointer;box-shadow:0 8px 20px rgba(21,68,183,0.3);margin-top:4px;">
                <span class="material-symbols-outlined" style="font-size:20px;font-variation-settings:'FILL' 1;">auto_awesome</span>
                Xác nhận & Build PC
              </button>
            </div>

            <!-- Loading (Multi-step animation) -->
            <div id="ai-build-loading" style="display:none;padding:24px 0 20px;flex-direction:column;align-items:center;gap:20px;">

              <!-- Brain pulse animation -->
              <div style="position:relative;width:72px;height:72px;">
                <div style="position:absolute;inset:-8px;border-radius:50%;border:2px solid rgba(59,130,246,0.2);animation:aiRing 2s ease-out infinite;"></div>
                <div style="position:absolute;inset:-4px;border-radius:50%;border:2px solid rgba(59,130,246,0.3);animation:aiRing 2s ease-out 0.5s infinite;"></div>
                <div style="position:absolute;inset:0;border-radius:50%;border:3px solid #dbeafe;"></div>
                <div style="position:absolute;inset:0;border-radius:50%;border:3px solid #3b82f6;border-top-color:transparent;animation:spin 0.9s linear infinite;"></div>
                <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;background:#eff6ff;border-radius:50%;">
                  <span class="material-symbols-outlined" style="color:#3b82f6;font-size:24px;font-variation-settings:'FILL' 1;animation:iconPulse 1.5s ease-in-out infinite;">smart_toy</span>
                </div>
              </div>

              <!-- Step list -->
              <div style="width:100%;display:flex;flex-direction:column;gap:8px;padding:0 4px;">
                <div id="ai-step-0" style="display:flex;align-items:center;gap:10px;padding:8px 12px;border-radius:10px;background:#f8fafc;border:1px solid #e2e8f0;opacity:0.35;transition:all 0.4s;">
                  <div id="ai-step-icon-0" style="width:28px;height:28px;border-radius:8px;background:#e2e8f0;display:flex;align-items:center;justify-content:center;flex-shrink:0;transition:all 0.4s;">
                    <span class="material-symbols-outlined" style="font-size:15px;color:#94a3b8;font-variation-settings:'FILL' 1;">brain</span>
                  </div>
                  <span id="ai-step-label-0" style="font-size:12px;font-weight:600;color:#94a3b8;flex:1;transition:all 0.4s;">Đang suy nghĩ...</span>
                  <span id="ai-step-check-0" class="material-symbols-outlined" style="font-size:14px;color:#22c55e;display:none;font-variation-settings:'FILL' 1;">check_circle</span>
                  <div id="ai-step-dot-0" style="width:6px;height:6px;border-radius:50%;background:#94a3b8;"></div>
                </div>
                <div id="ai-step-1" style="display:flex;align-items:center;gap:10px;padding:8px 12px;border-radius:10px;background:#f8fafc;border:1px solid #e2e8f0;opacity:0.35;transition:all 0.4s;">
                  <div id="ai-step-icon-1" style="width:28px;height:28px;border-radius:8px;background:#e2e8f0;display:flex;align-items:center;justify-content:center;flex-shrink:0;transition:all 0.4s;">
                    <span class="material-symbols-outlined" style="font-size:15px;color:#94a3b8;font-variation-settings:'FILL' 1;">search</span>
                  </div>
                  <span id="ai-step-label-1" style="font-size:12px;font-weight:600;color:#94a3b8;flex:1;transition:all 0.4s;">Đang phân tích yêu cầu</span>
                  <span id="ai-step-check-1" class="material-symbols-outlined" style="font-size:14px;color:#22c55e;display:none;font-variation-settings:'FILL' 1;">check_circle</span>
                  <div id="ai-step-dot-1" style="width:6px;height:6px;border-radius:50%;background:#94a3b8;"></div>
                </div>
                <div id="ai-step-2" style="display:flex;align-items:center;gap:10px;padding:8px 12px;border-radius:10px;background:#f8fafc;border:1px solid #e2e8f0;opacity:0.35;transition:all 0.4s;">
                  <div id="ai-step-icon-2" style="width:28px;height:28px;border-radius:8px;background:#e2e8f0;display:flex;align-items:center;justify-content:center;flex-shrink:0;transition:all 0.4s;">
                    <span class="material-symbols-outlined" style="font-size:15px;color:#94a3b8;font-variation-settings:'FILL' 1;">inventory_2</span>
                  </div>
                  <span id="ai-step-label-2" style="font-size:12px;font-weight:600;color:#94a3b8;flex:1;transition:all 0.4s;">Đang lựa chọn linh kiện</span>
                  <span id="ai-step-check-2" class="material-symbols-outlined" style="font-size:14px;color:#22c55e;display:none;font-variation-settings:'FILL' 1;">check_circle</span>
                  <div id="ai-step-dot-2" style="width:6px;height:6px;border-radius:50%;background:#94a3b8;"></div>
                </div>
                <div id="ai-step-3" style="display:flex;align-items:center;gap:10px;padding:8px 12px;border-radius:10px;background:#f8fafc;border:1px solid #e2e8f0;opacity:0.35;transition:all 0.4s;">
                  <div id="ai-step-icon-3" style="width:28px;height:28px;border-radius:8px;background:#e2e8f0;display:flex;align-items:center;justify-content:center;flex-shrink:0;transition:all 0.4s;">
                    <span class="material-symbols-outlined" style="font-size:15px;color:#94a3b8;font-variation-settings:'FILL' 1;">verified</span>
                  </div>
                  <span id="ai-step-label-3" style="font-size:12px;font-weight:600;color:#94a3b8;flex:1;transition:all 0.4s;">Đánh giá cấu hình</span>
                  <span id="ai-step-check-3" class="material-symbols-outlined" style="font-size:14px;color:#22c55e;display:none;font-variation-settings:'FILL' 1;">check_circle</span>
                  <div id="ai-step-dot-3" style="width:6px;height:6px;border-radius:50%;background:#94a3b8;"></div>
                </div>
              </div>

              <p id="ai-loading-tip" style="font-size:11px;color:#94a3b8;text-align:center;margin:0;font-style:italic;min-height:16px;"></p>
            </div>

            <style>
              @keyframes aiRing {
                0%   { transform:scale(1);   opacity:0.6; }
                100% { transform:scale(1.5); opacity:0; }
              }
              @keyframes iconPulse {
                0%, 100% { transform:scale(1); }
                50%       { transform:scale(1.15); }
              }
            </style>


            <!-- Result -->
            <div id="ai-build-result" style="display:none;flex-direction:column;gap:14px;">
              <div style="display:flex;align-items:center;justify-content:space-between;">
                <div>
                  <div style="font-size:13px;font-weight:900;color:#0f172a;">Cấu hình đề xuất</div>
                  <div style="font-size:10px;font-weight:700;color:#3b82f6;text-transform:uppercase;letter-spacing:0.05em;">✨ AI Powered</div>
                </div>
                <button id="btn-rebuild"
                  onclick="document.getElementById('ai-build-form').style.display='flex';document.getElementById('ai-build-result').style.display='none';"
                  style="font-size:11px;font-weight:600;color:#64748b;display:flex;align-items:center;gap:4px;background:none;border:none;cursor:pointer;padding:6px 8px;border-radius:8px;"
                  onmouseover="this.style.background='#eff6ff';this.style.color='#2563eb';" onmouseout="this.style.background='none';this.style.color='#64748b';">
                  <span class="material-symbols-outlined" style="font-size:14px;">refresh</span> Build lại
                </button>
              </div>
              <div id="ai-build-desc" style="background:linear-gradient(135deg,#eff6ff,#eef2ff);color:#334155;font-size:11px;padding:12px;border-radius:12px;line-height:1.6;border:1px solid #bfdbfe;"></div>
              <div style="position:relative;">
                <button id="ai-build-prev" style="display:none;position:absolute;left:-12px;top:50%;transform:translateY(-50%);z-index:10;width:32px;height:32px;border-radius:50%;background:#fff;border:1px solid #e2e8f0;box-shadow:0 2px 8px rgba(0,0,0,0.1);cursor:pointer;align-items:center;justify-content:center;">
                  <span class="material-symbols-outlined" style="font-size:14px;">chevron_left</span>
                </button>
                <div id="ai-build-products" style="display:flex;gap:10px;overflow-x:auto;padding-bottom:8px;scrollbar-width:none;"></div>
                <button id="ai-build-next" style="display:none;position:absolute;right:-12px;top:50%;transform:translateY(-50%);z-index:10;width:32px;height:32px;border-radius:50%;background:#fff;border:1px solid #e2e8f0;box-shadow:0 2px 8px rgba(0,0,0,0.1);cursor:pointer;align-items:center;justify-content:center;">
                  <span class="material-symbols-outlined" style="font-size:14px;">chevron_right</span>
                </button>
              </div>
              <div style="background:linear-gradient(135deg,#1e293b,#0f172a);padding:16px;border-radius:14px;">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
                  <div>
                    <div style="font-size:10px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.08em;">Tổng chi phí</div>
                    <div id="ai-build-total" style="font-size:20px;font-weight:900;color:#fff;">0đ</div>
                  </div>
                  <div style="width:40px;height:40px;background:rgba(255,255,255,0.1);border-radius:12px;display:flex;align-items:center;justify-content:center;">
                    <span class="material-symbols-outlined" style="color:#fff;font-variation-settings:'FILL' 1;">receipt_long</span>
                  </div>
                </div>
                <button id="btn-buy-ai-build"
                  style="width:100%;display:flex;align-items:center;justify-content:center;gap:8px;background:linear-gradient(90deg,#2563eb,#1d4ed8);color:#fff;font-weight:700;font-size:13px;padding:11px;border-radius:12px;border:none;cursor:pointer;box-shadow:0 6px 16px rgba(37,99,235,0.3);">
                  <span class="material-symbols-outlined" style="font-size:16px;font-variation-settings:'FILL' 1;">bolt</span>
                  Mua ngay cấu hình này
                </button>
              </div>
            </div>

          </div><!-- end body -->
        </div><!-- end ai-build-box -->

        <style>@keyframes spin { to { transform: rotate(360deg); } }</style>
        <script>
          function selectLevel(label, val) {
            document.querySelectorAll('.level-card').forEach(c => {
              c.style.borderColor='#e2e8f0'; c.style.background='#fff';
              c.querySelectorAll('span').forEach((s,i) => s.style.color = i===0?'#94a3b8':'#475569');
            });
            const card = label.querySelector('.level-card');
            card.style.borderColor='#3b82f6'; card.style.background='#eff6ff';
            card.querySelectorAll('span').forEach((s,i) => s.style.color = i===0?'#3b82f6':'#2563eb');
            document.getElementById('ai-level').value = val;
          }
          function selectTask(label, val) {
            document.querySelectorAll('.task-card').forEach(c => {
              c.style.borderColor='#e2e8f0'; c.style.background='#fff';
              c.querySelectorAll('span').forEach((s,i) => s.style.color = i===0?'#94a3b8':'#475569');
            });
            const card = label.querySelector('.task-card');
            card.style.borderColor='#3b82f6'; card.style.background='#eff6ff';
            card.querySelectorAll('span').forEach((s,i) => s.style.color = i===0?'#3b82f6':'#2563eb');
            document.getElementById('ai-task-custom').style.display = (val==='Khác') ? 'block' : 'none';
          }
        </script>
      </div>



    </div>
    <!-- BEGIN: Sản phẩm đã xem -->
    <section id="recently-viewed-section" class="max-w-7xl mx-auto px-4 py-12 hidden">
        <div class="flex items-center justify-between mb-8">
            <div class="flex items-center gap-4">
                <div class="w-1.5 h-8 bg-blue-500 rounded-full"></div>
                <h2 class="text-2xl font-bold text-slate-900">Sản phẩm bạn đã xem</h2>
            </div>
            <button onclick="clearHistory()" class="text-sm text-slate-400 hover:text-red-500 transition-colors flex items-center gap-1">
                <span class="material-symbols-outlined text-lg">delete</span> Xóa lịch sử
            </button>
        </div>
        <div class="relative">
            <div class="swiper recentlyViewedSwiper">
                <div id="recently-viewed-container" class="swiper-wrapper pb-10">
                    <!-- Dynamic content via JS -->
                </div>
                <div class="swiper-pagination"></div>
            </div>
        </div>
    </section>

    <script>
        function renderRecentlyViewed() {
            const section = document.getElementById('recently-viewed-section');
            const container = document.getElementById('recently-viewed-container');
            const history = JSON.parse(localStorage.getItem('recentlyViewed')) || [];

            if (history.length === 0) {
                section.classList.add('hidden');
                return;
            }

            section.classList.remove('hidden');
            container.innerHTML = history.map(p => {
                const price = p.price.toLocaleString('vi-VN');
                const origin = p.origin ? p.origin.toLocaleString('vi-VN') : null;
                const discount = p.origin > p.price ? Math.round((1 - p.price / p.origin) * 100) : 0;

                return `
                    <div class="swiper-slide h-full">
                        <a href="/detail/${p.sku}" class="group h-[408px] bg-white rounded-xl border border-slate-200 overflow-hidden hover:border-blue-500 hover:shadow-xl transition-all flex flex-col">
                            <div class="aspect-[4/3] relative overflow-hidden bg-slate-50 flex items-center justify-center p-4">
                                <img src="${p.img}" alt="${p.name}" class="w-full h-full object-contain group-hover:scale-105 transition-transform duration-500">
                                ${discount > 0 ? `
                                    <div class="absolute top-0 right-0 z-10">
                                        <div class="bg-red-500 text-white px-3 py-1.5 rounded-bl-xl shadow-sm flex flex-col items-center justify-center">
                                            <span class="text-[8px] font-bold uppercase tracking-widest opacity-90 leading-none mb-0.5">Giảm</span>
                                            <span class="text-sm font-black leading-none">${discount}%</span>
                                        </div>
                                    </div>` : ''}
                            </div>
                            <div class="p-4 flex-1 flex flex-col">
                                <h3 class="font-bold text-sm mb-1 text-slate-900 group-hover:text-blue-600 transition-colors line-clamp-2 min-h-[40px]">${p.name}</h3>
                                <div class="mt-auto flex items-center justify-between pt-3 border-t border-slate-100">
                                    <div class="flex flex-col">
                                        ${origin ? `<span class="text-[10px] text-gray-400 line-through">${origin}₫</span>` : ''}
                                        <span class="text-base font-extrabold text-slate-900 leading-tight">${price}₫</span>
                                    </div>
                                    <div class="size-9 flex items-center justify-center bg-blue-50 text-blue-600 rounded-lg">
                                        <span class="material-symbols-outlined text-xl">history</span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>`;
            }).join('');

            new Swiper('.recentlyViewedSwiper', {
                slidesPerView: 2,
                spaceBetween: 16,
                pagination: { el: '.swiper-pagination', clickable: true },
                breakpoints: {
                    1024: { slidesPerView: 4, spaceBetween: 24 }
                }
            });
        }

        function clearHistory() {
            if(confirm('Bạn có muốn xóa lịch sử xem sản phẩm?')) {
                localStorage.removeItem('recentlyViewed');
                renderRecentlyViewed();
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            renderRecentlyViewed();
        });
    </script>
    <!-- END: Sản phẩm đã xem -->
  </main>
  <div id="cart-summary"
    class="fixed bottom-0 left-0 right-0 z-[60] bg-white border-t border-slate-200 shadow-[0_-5px_10px_rgba(0,0,0,0.05)]">
    <!-- Voucher Selection Row (Replace old toggle) -->
    <div class="border-b border-slate-100 bg-white/50 relative">
      <div class="max-w-7xl mx-auto px-4 md:px-6">
        <div class="flex items-center justify-end py-2.5">
          <button type="button" class="group flex items-center gap-2 px-3 py-1.5 hover:bg-slate-50 transition-colors rounded-lg cursor-pointer max-w-[280px]" onclick="voucherUI.openModal()">
            <span class="material-symbols-outlined text-[20px] text-primary group-hover:scale-110 transition-transform">confirmation_number</span>
            <span class="text-sm text-slate-700 font-medium truncate shrink-0">Shop Voucher</span>
            <!-- Selected Voucher Indicator -->
            <div id="cartVoucherCodeDisplay" class="hidden shrink min-w-0">
              <span class="text-[13px] font-bold text-green-600 ml-2 truncate block" style="max-width: 200px;"></span>
            </div>
            <span class="text-[13px] text-blue-600 font-medium whitespace-nowrap hidden sm:inline ml-2" id="cartVoucherActionText">Chọn hoặc nhập mã</span>
            <i class="fa-solid fa-chevron-right text-[10px] text-slate-400 ml-1"></i>
          </button>
        </div>
      </div>
    </div>
    <!-- Main Summary Bar -->
    <div class="flex flex-col md:flex-row items-center justify-between px-4 py-4 gap-4">
      <div class="flex items-center gap-4 md:gap-8 flex-wrap">
        <label class="flex items-center gap-2 cursor-pointer">
          <input id="selectAllCheckbox" class="rounded bg-gray-300 text-primary focus:ring-primary border-slate-300"
            type="checkbox" />
          <span class="text-sm md:text-base">Chọn Tất Cả</span>
        </label>
        <button class="text-sm hover:text-primary transition-colors">
          Xóa
        </button>
        <button class="text-sm hover:text-primary transition-colors">
          Bỏ sản phẩm không hoạt động
        </button>
        <button class="text-sm text-primary font-medium">
          Lưu vào mục Đã thích
        </button>
      </div>
      <div class="flex items-center gap-4 w-full md:w-auto justify-between md:justify-end">
        <div class="text-right">
          <div class="flex items-center justify-end gap-1">
            <span class="text-sm md:text-base" id="totalCountText">Tổng cộng (0 sản phẩm):</span>
            <span class="text-xl md:text-2xl font-bold text-primary" id="totalPriceText">0đ</span>

          </div>
          <div class="flex items-center justify-end gap-1">
            <span class="text-xs text-slate-500">Tiết kiệm</span>
            <span class="text-xs text-primary totalDiscount">350,000đ</span>
          </div>
        </div>
        <button id="buy"
          class="bg-[#1463c2] text-white px-10 py-3 rounded-lg font-bold text-lg hover:bg-primary-dark transition-all min-w-[200px] shadow-lg shadow-primary/20">
          Mua Hàng
        </button>
      </div>
    </div>
  </div>
  </div>
  <?php include 'voucher_modal.php'; ?>
  <script src="js/checkout/cart.js"></script>
</body>