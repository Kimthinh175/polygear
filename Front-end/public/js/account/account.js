// chức năng cơ bản
function unlockInput(inputId) {
  const input = document.getElementById(inputId);
  input.removeAttribute("readonly");
  input.classList.remove("input-locked");
  input.classList.add(
    "input-unlocked",
    "focus:ring-2",
    "focus:ring-blue-500/20",
    "focus:border-blue-300",
  );
  input.focus();
}

function previewAndEncodeImage(input) {
  const file = input.files[0];
  if (file) {
    if (file.size > 1024 * 1024) {
      alert("Ảnh quá lớn! Vui lòng chọn ảnh dưới 1MB.");
      input.value = "";
      return;
    }
    const reader = new FileReader();
    reader.onload = function (e) {
      document.getElementById("main-avatar-preview").src = e.target.result;
      document.getElementById("hidden-avatar-base64").value = e.target.result;
    };
    reader.readAsDataURL(file);
  }
}

// api cập nhật thông tin chung
function submitAllData() {
  const btn = event.currentTarget;
  const originalText = btn.innerText;
  btn.innerText = "Đang lưu...";
  btn.disabled = true;

  const formData = new FormData();
  formData.append("user_id", document.getElementById("hidden-user-id").value);
  formData.append(
    "user_name",
    document.getElementById("input-user-name").value,
  );
  formData.append("gmail", document.getElementById("input-user-email").value);
  formData.append(
    "phone_number",
    document.getElementById("input-user-phone").value,
  );
  formData.append(
    "user_address",
    document.getElementById("final-address-input").value,
  );

  const fileInput = document.getElementById("avatarInput");
  if (fileInput.files.length > 0) {
    formData.append("avatar_file", fileInput.files[0]);
  }

  fetch("/Back-end/api/account", { credentials: 'include', 
    method: "POST",
    body: formData,
  })
    .then((res) => res.json())
    .then((result) => {
      btn.innerText = originalText;
      btn.disabled = false;
      if (result.status === "success") {
        alert("Cập nhật thành công!");
      } else {
        alert("Lỗi cập nhật: " + (result.message || "Không xác định"));
      }
    })
    .catch((error) => {
      btn.innerText = originalText;
      btn.disabled = false;
      console.error("Lỗi API:", error);
    });
}

// đóng mở modal
function toggleModal(modalId, show) {
  const modal = document.getElementById(modalId);
  if (!modal) return;
  if (show) {
    modal.classList.add("active");
    document.body.style.overflow = "hidden";
  } else {
    modal.classList.remove("active");
    if (!document.querySelector(".modal-backdrop.active")) {
      document.body.style.overflow = "auto";
    }
  }
}

window.onclick = function (event) {
  if (event.target.classList.contains("modal-backdrop")) {
    toggleModal(event.target.id, false);
  }
};

function openAddressListModal() {
  toggleModal("addressListModal", true);
}
function backToAddressList() {
  toggleModal("mapModal", false);
  setTimeout(() => toggleModal("addressListModal", true), 200);
}

// lưu địa chỉ đang chọn ra màn hình chính
function saveSelectedAddress() {
  const selectedRadio = document.querySelector(
    'input[name="selected_address"]:checked',
  );
  if (selectedRadio) {
    const addressValue = selectedRadio.value;

    document.getElementById("main-ui-address").innerText = addressValue;
    document.getElementById("final-address-input").value = addressValue;

    const oldDefaultBadge = document.querySelector(
      "#address-list-container .bg-red-50.text-red-600",
    );
    if (oldDefaultBadge) {
      oldDefaultBadge.remove();
    }

    const checkedLabel = selectedRadio.closest("label");
    const actionContainer = checkedLabel.querySelector(
      ".flex.items-center.gap-2.mt-2",
    );

    if (actionContainer) {
      const defaultBadgeHTML = `<span class="inline-block px-2 py-0.5 bg-red-50 text-red-600 text-[10px] uppercase font-bold rounded border border-red-200">Mặc định</span>`;
      actionContainer.insertAdjacentHTML("afterbegin", defaultBadgeHTML);
    }
  }
  toggleModal("addressListModal", false);
}

// api xóa địa chỉ
function deleteAddress(event, id, btnElement) {
  event.stopPropagation();
  event.preventDefault();

  if (confirm("Bạn có chắc chắn muốn xóa địa chỉ này?")) {
    const originalHTML = btnElement.innerHTML;
    btnElement.innerHTML = "Đang xóa...";
    btnElement.disabled = true;

    fetch("/Back-end/api/address", { credentials: 'include', 
      method: "DELETE",
      body: JSON.stringify({ id: id }),
    })
      .then((res) => res.json())
      .then((result) => {
        if (result.status === "success") {
          const labelWrapper = btnElement.closest("label");
          if (labelWrapper) labelWrapper.remove();
        } else {
          btnElement.innerHTML = originalHTML;
          btnElement.disabled = false;
          alert("Lỗi xóa: " + (result.message || "Không xác định"));
        }
      })
      .catch((error) => {
        btnElement.innerHTML = originalHTML;
        btnElement.disabled = false;
        console.error("Lỗi API:", error);
        alert("Lỗi mạng khi xóa!");
      });
  }
}

document
  .getElementById("address-list-container")
  .addEventListener("change", function (e) {
    if (e.target.name === "selected_address") {
      const labels = this.querySelectorAll("label");
      labels.forEach((lbl) => {
        lbl.classList.remove("border-blue-500", "bg-blue-50/20");
        lbl.classList.add("border-gray-200");
      });
      const checkedLabel = e.target.closest("label");
      checkedLabel.classList.remove("border-gray-200");
      checkedLabel.classList.add("border-blue-500", "bg-blue-50/20");
    }
  });

// bản đồ và định vị
let map, marker;
function openMapModal() {
  toggleModal("addressListModal", false);
  setTimeout(() => toggleModal("mapModal", true), 200);

  if (!map) {
    const fallbackLngLat = [106.6297, 10.8231];
    map = new maplibregl.Map({
      container: "map-canvas",
      style:
        "https:// maps.track-asia.com/styles/v1/streets.json?key=public_key",
      center: fallbackLngLat,
      zoom: 16,
      attributionControl: false,
    });
    map.addControl(new maplibregl.NavigationControl(), "bottom-right");
    const geolocate = new maplibregl.GeolocateControl({
      positionOptions: { enableHighAccuracy: true },
      trackUserLocation: false,
      showAccuracyCircle: false,
    });
    map.addControl(geolocate, "bottom-right");
    marker = new maplibregl.Marker({ draggable: true, color: "#ef4444" })
      .setLngLat(fallbackLngLat)
      .addTo(map);

    geolocate.on("geolocate", function (e) {
      const lon = e.coords.longitude;
      const lat = e.coords.latitude;
      document.getElementById("current-selected-address").value =
        "Đang tìm địa chỉ...";
      marker.setLngLat([lon, lat]);
      getAddressFromCoords(lat, lon);
    });
    getAddressFromCoords(fallbackLngLat[1], fallbackLngLat[0]);
    marker.on("dragend", function () {
      document.getElementById("current-selected-address").value =
        "Đang tìm địa chỉ...";
      const lngLat = marker.getLngLat();
      getAddressFromCoords(lngLat.lat, lngLat.lng);
    });
    map.on("click", function (e) {
      document.getElementById("current-selected-address").value =
        "Đang tìm địa chỉ...";
      marker.setLngLat(e.lngLat);
      getAddressFromCoords(e.lngLat.lat, e.lngLat.lng);
    });
    map.on("load", function () {
      geolocate.trigger();
    });
  }
  setTimeout(() => {
    map.resize();
  }, 300);
}

document.getElementById("search-location-btn").onclick = () => {
  const query = document.getElementById("map-search-input").value;
  if (!query) return;
  const btn = document.getElementById("search-location-btn");
  btn.innerText = "...";
  fetch(
    `https:// nominatim.openstreetmap.org/search?format=jsonv2&q=${encodeuricomponent(query)}&countrycodes=vn`
  )
    .then((res) => res.json())
    .then((data) => {
      btn.innerText = "Tìm";
      if (data && data.length > 0) {
        const lat = parseFloat(data[0].lat);
        const lon = parseFloat(data[0].lon);
        map.flyTo({ center: [lon, lat], zoom: 17, essential: true });
        marker.setLngLat([lon, lat]);
        getAddressFromCoords(lat, lon);
      } else {
        alert("Không tìm thấy địa chỉ này!");
      }
    })
    .catch((err) => {
      btn.innerText = "Tìm";
    });
};

document
  .getElementById("map-search-input")
  .addEventListener("keypress", function (event) {
    if (event.key === "Enter") {
      event.preventDefault();
      document.getElementById("search-location-btn").click();
    }
  });

function getAddressFromCoords(lat, lng) {
  fetch(
    `https:// nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`,
    {
      headers: { "Accept-Language": "vi-VN,vi;q=0.9,en;q=0.8" },
    }
  )
    .then((res) => res.json())
    .then((data) => {
      if (data && data.display_name) {
        document.getElementById("current-selected-address").value =
          data.display_name;
      } else {
        document.getElementById("current-selected-address").value =
          "Không tìm thấy địa chỉ cụ thể.";
      }
    });
}

// fix quan trọng: lấy tên, sđt và gọi đúng đường dẫn api
document.getElementById("confirm-address-btn").onclick = (event) => {
  const newAddress = document.getElementById(
    "current-selected-address",
  ).value.trim();
  const newName = document.getElementById("new-receiver-name").value.trim();
  const newPhone = document.getElementById("new-receiver-phone").value.trim();

  if (
    newAddress === "Đang tìm địa chỉ..." ||
    newAddress === "Đang tải tọa độ..."
  ) {
    alert("Vui lòng đợi tải xong hoặc chọn một địa chỉ trên bản đồ!");
    return;
  }

  if (newName === "" || newPhone === "") {
    alert("Vui lòng nhập đầy đủ Tên người nhận và Số điện thoại!");
    return;
  }

  const btn = event.currentTarget;
  const originalHTML = btn.innerHTML;
  btn.innerHTML = "Đang lưu...";
  btn.disabled = true;

  const formData = new FormData();
  formData.append("user_id", document.getElementById("hidden-user-id").value);
  formData.append("receiver_name", newName);
  formData.append("receiver_phone", newPhone);
  formData.append("address", newAddress);

  // sửa đúng api endpoint: /api/address
  fetch("/Back-end/api/address", { credentials: 'include', 
    method: "POST",
    body: formData,
  })
    .then((res) => res.json())
    .then((result) => {
      btn.innerHTML = originalHTML;
      btn.disabled = false;

      if (result.status === "success") {
        const oldDefaultBadge = document.querySelector(
          "#address-list-container .bg-red-50.text-red-600",
        );
        if (oldDefaultBadge) {
          oldDefaultBadge.remove();
        }

        const newAddressHTML = `
                    <label class="flex items-start gap-3 p-4 border rounded-xl cursor-pointer transition-colors border-blue-500 bg-blue-50/20">
                      <input type="radio" name="selected_address" value="${newAddress}" checked class="mt-1 w-4 h-4 text-blue-600">
                      <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1.5">
                            <span class="font-bold text-gray-800">${newName}</span>
                            <span class="text-gray-300 text-sm">|</span>
                            <span class="text-gray-600 text-sm font-semibold">${newPhone}</span>
                        </div>
                        <p class="text-sm text-gray-600">${newAddress}</p>
                        <div class="flex items-center gap-2 mt-2">
                            <span class="inline-block px-2 py-0.5 bg-red-50 text-red-600 text-[10px] uppercase font-bold rounded border border-red-200">Mặc định</span>
                        </div>
                      </div>
                    </label>
                  `;

        const labels = document.querySelectorAll(
          "#address-list-container label",
        );
        labels.forEach((lbl) => {
          lbl.classList.remove("border-blue-500", "bg-blue-50/20");
          lbl.classList.add("border-gray-200");
          lbl.querySelector("input").checked = false;
        });

        const container = document.getElementById("address-list-container");
        container.insertAdjacentHTML("afterbegin", newAddressHTML);

        document.getElementById("main-ui-address").innerText = newAddress;
        document.getElementById("final-address-input").value = newAddress;

        // dọn sạch form
        document.getElementById("new-receiver-name").value = "";
        document.getElementById("new-receiver-phone").value = "";

        backToAddressList();
      } else {
        alert("Lỗi thêm địa chỉ: " + (result.message || "Không xác định"));
      }
    })
    .catch((error) => {
      btn.innerHTML = originalHTML;
      btn.disabled = false;
      console.error("Lỗi API:", error);
      alert("Lỗi mạng khi thêm địa chỉ!");
    });
};
