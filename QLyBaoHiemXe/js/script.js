/**
 * JavaScript cho hệ thống Quản Lý Bảo Hiểm Xe
 */

document.addEventListener('DOMContentLoaded', function() {
    // Khởi tạo tooltips
    initTooltips();
    
    // Khởi tạo form validation
    initFormValidation();
    
    // Khởi tạo search functionality
    initSearch();
    
    // Khởi tạo date pickers
    initDatePickers();
});

/**
 * Khởi tạo tooltips
 */
function initTooltips() {
    const tooltips = document.querySelectorAll('[title]');
    tooltips.forEach(tooltip => {
        tooltip.addEventListener('mouseenter', showTooltip);
        tooltip.addEventListener('mouseleave', hideTooltip);
    });
}

function showTooltip(e) {
    const tooltipText = this.getAttribute('title');
    const tooltipEl = document.createElement('div');
    tooltipEl.className = 'custom-tooltip';
    tooltipEl.textContent = tooltipText;
    document.body.appendChild(tooltipEl);
    
    const rect = this.getBoundingClientRect();
    tooltipEl.style.left = rect.left + 'px';
    tooltipEl.style.top = (rect.top - tooltipEl.offsetHeight - 5) + 'px';
    
    this.removeAttribute('title');
    this.setAttribute('data-original-title', tooltipText);
}

function hideTooltip() {
    const tooltipEl = document.querySelector('.custom-tooltip');
    if (tooltipEl) {
        tooltipEl.remove();
    }
    
    const originalTitle = this.getAttribute('data-original-title');
    if (originalTitle) {
        this.setAttribute('title', originalTitle);
        this.removeAttribute('data-original-title');
    }
}

/**
 * Khởi tạo form validation
 */
function initFormValidation() {
    const forms = document.querySelectorAll('form[novalidate]');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
            }
        });
    });
}

function validateForm(form) {
    let isValid = true;
    const requiredFields = form.querySelectorAll('[required]');
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            markFieldInvalid(field, 'Trường này là bắt buộc');
            isValid = false;
        } else {
            markFieldValid(field);
        }
    });
    
    // Validate email
    const emailFields = form.querySelectorAll('input[type="email"]');
    emailFields.forEach(field => {
        if (field.value && !isValidEmail(field.value)) {
            markFieldInvalid(field, 'Email không hợp lệ');
            isValid = false;
        }
    });
    
    // Validate phone
    const phoneFields = form.querySelectorAll('input[type="tel"]');
    phoneFields.forEach(field => {
        if (field.value && !isValidPhone(field.value)) {
            markFieldInvalid(field, 'Số điện thoại không hợp lệ');
            isValid = false;
        }
    });
    
    return isValid;
}

function markFieldInvalid(field, message) {
    field.classList.add('invalid');
    field.classList.remove('valid');
    
    // Remove existing error message
    const existingError = field.parentNode.querySelector('.field-error');
    if (existingError) {
        existingError.remove();
    }
    
    // Add new error message
    const errorEl = document.createElement('div');
    errorEl.className = 'field-error';
    errorEl.textContent = message;
    field.parentNode.appendChild(errorEl);
}

function markFieldValid(field) {
    field.classList.add('valid');
    field.classList.remove('invalid');
    
    // Remove error message
    const existingError = field.parentNode.querySelector('.field-error');
    if (existingError) {
        existingError.remove();
    }
}

function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function isValidPhone(phone) {
    const phoneRegex = /^(0[3|5|7|8|9])+([0-9]{8})$/;
    return phoneRegex.test(phone);
}

/**
 * Khởi tạo search functionality
 */
function initSearch() {
    const searchInputs = document.querySelectorAll('input[type="search"], input[name="search"]');
    searchInputs.forEach(input => {
        input.addEventListener('input', debounce(function() {
            const form = this.closest('form');
            if (form) {
                form.submit();
            }
        }, 500));
    });
}

/**
 * Debounce function để giảm số lần gọi
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Khởi tạo date pickers
 */
function initDatePickers() {
    const dateInputs = document.querySelectorAll('input[type="date"]');
    dateInputs.forEach(input => {
        // Set min date to today for future dates
        if (input.name === 'sign_date' || input.name === 'expiry_date') {
            input.min = new Date().toISOString().split('T')[0];
        }
        
        // Set max date to today for birth dates
        if (input.name === 'date_of_birth') {
            input.max = new Date().toISOString().split('T')[0];
        }
    });
}

/**
 * Confirm delete với SweetAlert2
 */
function confirmDelete(message = 'Bạn có chắc chắn muốn xóa?') {
    return Swal.fire({
        title: 'Xác nhận xóa',
        text: message,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Xóa',
        cancelButtonText: 'Hủy'
    });
}

/**
 * Format số tiền khi nhập
 */
function formatCurrencyInput(input) {
    input.addEventListener('input', function() {
        let value = this.value.replace(/\./g, '');
        if (!isNaN(value)) {
            this.value = new Intl.NumberFormat('vi-VN').format(value);
        }
    });
}

/**
 * Auto-calculate expiry date based on sign date
 */
function initAutoExpiryDate() {
    const signDateInput = document.getElementById('sign_date');
    const expiryDateInput = document.getElementById('expiry_date');
    
    if (signDateInput && expiryDateInput) {
        signDateInput.addEventListener('change', function() {
            if (this.value) {
                const signDate = new Date(this.value);
                const expiryDate = new Date(signDate);
                expiryDate.setFullYear(expiryDate.getFullYear() + 1);
                expiryDateInput.value = expiryDate.toISOString().split('T')[0];
            }
        });
    }
}

/**
 * Toggle password visibility
 */
function initPasswordToggle() {
    const toggleButtons = document.querySelectorAll('.toggle-password');
    toggleButtons.forEach(button => {
        button.addEventListener('click', function() {
            const passwordInput = this.previousElementSibling;
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    });
}

// Khởi tạo khi DOM ready
document.addEventListener('DOMContentLoaded', function() {
    initAutoExpiryDate();
    initPasswordToggle();
    
    // Áp dụng format currency cho các input số tiền
    const currencyInputs = document.querySelectorAll('input[type="number"]');
    currencyInputs.forEach(formatCurrencyInput);
});