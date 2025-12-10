<?php
/**
 * Customer Search Results View (Module 1C)
 * Display search results for customers
 */

// Security check
if (!isset($auth) || !$auth->isLoggedIn()) {
    $_SESSION['error'] = 'Please log in to access this page';
    header('Location: ' . BASE_URL . '?c=Auth&m=login');
    exit;
}
?>

<div class="container mt-4">
    <h2><i class="fas fa-search"></i> Search Results</h2>
    <hr>

    <!-- Search Form -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-2">
                <input type="hidden" name="c" value="Customer">
                <input type="hidden" name="m" value="search">
                
                <div class="col-md-5">
                    <input type="text" name="cccd" class="form-control" placeholder="Search by CCCD..." 
                           value="<?php echo htmlspecialchars($cccd ?? ''); ?>">
                </div>
                <div class="col-md-5">
                    <input type="text" name="phone" class="form-control" placeholder="Search by Phone..." 
                           value="<?php echo htmlspecialchars($phone ?? ''); ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-info w-100">
                        <i class="fas fa-search"></i> Search
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Search Results -->
    <?php if ($searchPerformed): ?>
        <?php if (!empty($results)): ?>
            <div class="card">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>CCCD</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($results as $customer): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($customer['MaKH']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($customer['HoTen']); ?></td>
                                    <td><?php echo htmlspecialchars($customer['CCCD']); ?></td>
                                    <td><?php echo htmlspecialchars($customer['SDT'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($customer['Email'] ?? 'N/A'); ?></td>
                                    <td>
                                        <?php if ($customer['TrangThai'] === 'HoatDong'): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php elseif ($customer['TrangThai'] === 'NgungHieuLuc'): ?>
                                            <span class="badge bg-warning">Inactive</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Deleted</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="<?php echo BASE_URL; ?>?c=Customer&m=detail&maKH=<?php echo $customer['MaKH']; ?>" 
                                           class="btn btn-sm btn-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?php echo BASE_URL; ?>?c=Customer&m=edit&maKH=<?php echo $customer['MaKH']; ?>" 
                                           class="btn btn-sm btn-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <p class="mt-3 text-muted">Found <?php echo count($results); ?> customer(s)</p>
        <?php else: ?>
            <div class="alert alert-info" role="alert">
                <i class="fas fa-info-circle"></i> No customers found matching your search criteria.
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="alert alert-secondary" role="alert">
            <i class="fas fa-search"></i> Enter search criteria and click Search to find customers.
        </div>
    <?php endif; ?>

    <div class="mt-4">
        <a href="<?php echo BASE_URL; ?>?c=Customer&m=list" class="btn btn-secondary">
            <i class="fas fa-list"></i> Back to Customer List
        </a>
    </div>
</div>
