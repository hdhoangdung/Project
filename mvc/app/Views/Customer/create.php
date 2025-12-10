<?php
/**
 * Create Customer View (Module 1C)
 * Form for adding a new customer
 */

// Security check
if (!isset($auth) || !$auth->isLoggedIn()) {
    $_SESSION['error'] = 'Please log in to access this page';
    header('Location: ' . BASE_URL . '?c=Auth&m=login');
    exit;
}
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <h2><i class="fas fa-user-plus"></i> Add New Customer</h2>
            <hr>

            <?php if (isset($formError)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($formError); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form id="customerForm" method="POST" action="<?php echo BASE_URL; ?>?c=Customer&m=create" novalidate>
                <div class="mb-3">
                    <label for="hoTen" class="form-label">Full Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="hoTen" name="HoTen" required
                           placeholder="Enter customer's full name">
                    <div class="invalid-feedback">Please provide a name.</div>
                </div>

                <div class="mb-3">
                    <label for="ngaySinh" class="form-label">Date of Birth</label>
                    <input type="date" class="form-control" id="ngaySinh" name="NgaySinh">
                    <small class="form-text text-muted">Optional. Format: YYYY-MM-DD</small>
                </div>

                <div class="mb-3">
                    <label for="cccd" class="form-label">CCCD/ID Number <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="cccd" name="CCCD" required
                           placeholder="Enter 12-digit ID" maxlength="12" pattern="\d{12}">
                    <div class="invalid-feedback">CCCD must be exactly 12 digits.</div>
                    <small class="form-text text-muted">Must be exactly 12 digits (no spaces or dashes).</small>
                </div>

                <div class="mb-3">
                    <label for="diaChi" class="form-label">Address</label>
                    <textarea class="form-control" id="diaChi" name="DiaChi" rows="3"
                              placeholder="Enter customer's address"></textarea>
                </div>

                <div class="mb-3">
                    <label for="sdt" class="form-label">Phone Number</label>
                    <input type="tel" class="form-control" id="sdt" name="SDT"
                           placeholder="Enter phone number (9-14 digits)" pattern="[+]?\d{9,14}">
                    <div class="invalid-feedback">Phone must be 9-14 digits, optionally starting with +.</div>
                    <small class="form-text text-muted">Format: 9-14 digits, optional + prefix.</small>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" class="form-control" id="email" name="Email"
                           placeholder="Enter email address">
                    <div class="invalid-feedback">Please provide a valid email address.</div>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Create Customer
                    </button>
                    <a href="<?php echo BASE_URL; ?>?c=Customer&m=list" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('customerForm').addEventListener('submit', function(e) {
    // Client-side validation
    let isValid = true;
    
    // Validate CCCD
    const cccd = document.getElementById('cccd').value.trim();
    if (!/^\d{12}$/.test(cccd)) {
        document.getElementById('cccd').classList.add('is-invalid');
        isValid = false;
    } else {
        document.getElementById('cccd').classList.remove('is-invalid');
    }
    
    // Validate phone if provided
    const phone = document.getElementById('sdt').value.trim();
    if (phone && !/^[+]?\d{9,14}$/.test(phone)) {
        document.getElementById('sdt').classList.add('is-invalid');
        isValid = false;
    } else {
        document.getElementById('sdt').classList.remove('is-invalid');
    }
    
    // Validate email if provided
    const email = document.getElementById('email').value.trim();
    if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        document.getElementById('email').classList.add('is-invalid');
        isValid = false;
    } else {
        document.getElementById('email').classList.remove('is-invalid');
    }
    
    // Validate full name
    const hoTen = document.getElementById('hoTen').value.trim();
    if (!hoTen) {
        document.getElementById('hoTen').classList.add('is-invalid');
        isValid = false;
    } else {
        document.getElementById('hoTen').classList.remove('is-invalid');
    }
    
    if (!isValid) {
        e.preventDefault();
    }
});
</script>
