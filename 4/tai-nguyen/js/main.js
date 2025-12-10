/*
 * File: `assets/js/main.js`
 * Mục đích:
 * - Chứa các hàm JS dùng chung cho toàn project: định dạng tiền/ ngày, modal, validation, alerts, tìm kiếm.
 * - Không lưu trữ logic bảo mật (kiểm tra quyền) ở client-side.
 */

// Hàm định dạng tiền tệ
function formatMoney(amount) {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(amount);
}

// Hàm định dạng ngày
function formatDate(dateString) {
    const date = new Date(dateString);
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();
    return `${day}/${month}/${year}`;
}

// Xác nhận xóa
function confirmDelete(message = "Bạn có chắc chắn muốn xóa?") {
    return confirm(message);
}

// Các hàm Modal
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('active');
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
    }
}

// Đóng modal khi click bên ngoài
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.classList.remove('active');
    }
}

// Tìm kiếm trong bảng
function searchTable(inputId, tableId) {
	const input = document.getElementById(inputId);
	if (!input) return;
	const filter = input.value.toUpperCase();
	const table = document.getElementById(tableId);
	if (!table) return;
	const tr = table.getElementsByTagName('tr');

	for (let i = 1; i < tr.length; i++) {
		const txt = tr[i].innerText || tr[i].textContent;
		tr[i].style.display = txt.toUpperCase().indexOf(filter) > -1 ? '' : 'none';
	}
}

// Chuyển đổi tab
function switchTab(tabName) {
    // Đặt lại nút tab
    document.querySelectorAll(".tab").forEach(btn => {
        btn.classList.remove("active");
        btn.setAttribute("aria-selected", "false");
    });

    // Đặt lại bảng điều khiển
    document.querySelectorAll(".tab-panel").forEach(panel => {
        panel.classList.remove("active");
    });

    // Kích hoạt tab được chọn
    document.getElementById("tab-" + tabName).classList.add("active");
    document.getElementById("tab-" + tabName).setAttribute("aria-selected", "true");

    // Hiển thị bảng điều khiển được chọn
    document.getElementById("panel-" + tabName).classList.add("active");
}

// Gom DOMContentLoaded listener
document.addEventListener('DOMContentLoaded', function() {
	// Auto hide alerts
	const alerts = document.querySelectorAll('.alert');
	alerts.forEach(alert => setTimeout(() => { alert.style.opacity = '0'; setTimeout(() => alert.remove(), 300); }, 5000));
	// Validate tiền
	const moneyInputs = document.querySelectorAll('input[type="number"]');
	moneyInputs.forEach(input => input.addEventListener('input', function() { this.value = this.value.replace(/[^0-9]/g, ''); }));
	// Focus password when username present
	const usernameInput = document.getElementById('username');
	const passwordInput = document.getElementById('password');
	if (usernameInput && passwordInput && usernameInput.value.trim() !== '') passwordInput.focus();
});
