<?php
/**
 * Customer Detail View (Module 1C)
 * Display detailed customer information with related data
 */

// Security check
if (!isset($auth) || !$auth->isLoggedIn()) {
    $_SESSION['error'] = 'Please log in to access this page';
    header('Location: ' . BASE_URL . '?c=Auth&m=login');
    exit;
}

if (!isset($customer)) {
    $_SESSION['error'] = 'Customer not found';
    header('Location: ' . BASE_URL . '?c=Customer&m=list');
    exit;
}
?>

<div class="container mt-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h2><i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($customer['HoTen']); ?></h2>
            <p class="text-muted">ID: <?php echo htmlspecialchars($customer['MaKH']); ?></p>
        </div>
        <div class="col-md-4 text-end">
            <a href="<?php echo BASE_URL; ?>?c=Customer&m=edit&maKH=<?php echo $customer['MaKH']; ?>" 
               class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="<?php echo BASE_URL; ?>?c=Customer&m=list" class="btn btn-secondary">
                <i class="fas fa-list"></i> Back to List
            </a>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Customer Information -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Personal Information</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr>
                            <td><strong>Full Name:</strong></td>
                            <td><?php echo htmlspecialchars($customer['HoTen']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Date of Birth:</strong></td>
                            <td><?php echo htmlspecialchars($customer['NgaySinh'] ?? 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <td><strong>CCCD/ID:</strong></td>
                            <td><?php echo htmlspecialchars($customer['CCCD']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Status:</strong></td>
                            <td>
                                <?php if ($customer['TrangThai'] === 'HoatDong'): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php elseif ($customer['TrangThai'] === 'NgungHieuLuc'): ?>
                                    <span class="badge bg-warning">Inactive</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Deleted</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Contact Information</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr>
                            <td><strong>Phone:</strong></td>
                            <td><?php echo htmlspecialchars($customer['SDT'] ?? 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Email:</strong></td>
                            <td><?php echo htmlspecialchars($customer['Email'] ?? 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Address:</strong></td>
                            <td><?php echo htmlspecialchars($customer['DiaChi'] ?? 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Created:</strong></td>
                            <td><?php echo htmlspecialchars($customer['CreatedAt']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Updated:</strong></td>
                            <td><?php echo htmlspecialchars($customer['UpdatedAt']); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <?php if (isset($statistics)): ?>
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">Vehicles</h5>
                    <h2 class="text-primary"><?php echo $statistics['vehicles']; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">Contracts</h5>
                    <h2 class="text-success"><?php echo $statistics['contracts']; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">Claims</h5>
                    <h2 class="text-warning"><?php echo $statistics['claims']; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">Total Claims</h5>
                    <h2 class="text-danger"><?php echo number_format($statistics['totalClaimAmount'], 2); ?></h2>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Vehicles -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-car"></i> Vehicles</h5>
                    <a href="#" class="btn btn-sm btn-light" onclick="return false;">
                        <i class="fas fa-plus"></i> Add Vehicle
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>License Plate</th>
                                <th>Brand</th>
                                <th>Year</th>
                                <th>Type</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($vehicles)): ?>
                                <?php foreach ($vehicles as $vehicle): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($vehicle['BienSoXe']); ?></td>
                                    <td><?php echo htmlspecialchars($vehicle['HangXe'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($vehicle['NamSX'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($vehicle['LoaiXe'] ?? 'N/A'); ?></td>
                                    <td>
                                        <?php if ($vehicle['TrangThai'] === 'HoatDong'): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Deleted</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-3">No vehicles registered.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Contracts -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-file-contract"></i> Contracts</h5>
                    <a href="#" class="btn btn-sm btn-light" onclick="return false;">
                        <i class="fas fa-plus"></i> Add Contract
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Contract ID</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Premium</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($contracts)): ?>
                                <?php foreach ($contracts as $contract): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($contract['MaHD']); ?></td>
                                    <td><?php echo htmlspecialchars($contract['NgayBD']); ?></td>
                                    <td><?php echo htmlspecialchars($contract['NgayKT']); ?></td>
                                    <td><?php echo number_format($contract['SoTien'], 2); ?> VND</td>
                                    <td>
                                        <?php if ($contract['TrangThai'] === 'HoatDong'): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-3">No contracts found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Claims -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-exclamation-circle"></i> Claims</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Claim ID</th>
                                <th>Contract ID</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($claims)): ?>
                                <?php foreach ($claims as $claim): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($claim['MaYC']); ?></td>
                                    <td><?php echo htmlspecialchars($claim['MaHD']); ?></td>
                                    <td><?php echo htmlspecialchars(substr($claim['NoiDung'] ?? '', 0, 50)); ?>...</td>
                                    <td>
                                        <?php if ($claim['TrangThai'] === 'HoatDong'): ?>
                                            <span class="badge bg-info">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Cancelled</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($claim['CreatedAt']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-3">No claims found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
