<?php
/**
 * Customer List View (Module 1C)
 * Display paginated customer list with search and actions
 */

// Security check
if (!isset($auth) || !$auth->isLoggedIn()) {
    $_SESSION['error'] = 'Please log in to access this page';
    header('Location: ' . BASE_URL . '?c=Auth&m=login');
    exit;
}
?>

<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2><i class="fas fa-users"></i> Customers</h2>
        </div>
        <div class="col-md-4 text-end">
            <a href="<?php echo BASE_URL; ?>?c=Customer&m=create" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Customer
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

    <!-- Search Bar -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-2">
                <input type="hidden" name="c" value="Customer">
                <input type="hidden" name="m" value="search">
                
                <div class="col-md-5">
                    <input type="text" name="cccd" class="form-control" placeholder="Search by CCCD..." 
                           value="<?php echo htmlspecialchars($_GET['cccd'] ?? ''); ?>">
                </div>
                <div class="col-md-5">
                    <input type="text" name="phone" class="form-control" placeholder="Search by Phone..." 
                           value="<?php echo htmlspecialchars($_GET['phone'] ?? ''); ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-info w-100">
                        <i class="fas fa-search"></i> Search
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Customers Table -->
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
                    <?php if (!empty($customers)): ?>
                        <?php foreach ($customers as $customer): ?>
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
                                    <button type="button" class="btn btn-sm btn-danger" 
                                            onclick="confirmDelete(<?php echo $customer['MaKH']; ?>)" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                No customers found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <nav class="mt-4">
            <ul class="pagination justify-content-center">
                <?php if ($currentPage > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="<?php echo BASE_URL; ?>?c=Customer&m=list&page=1">First</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="<?php echo BASE_URL; ?>?c=Customer&m=list&page=<?php echo $currentPage - 1; ?>">Previous</a>
                    </li>
                <?php endif; ?>

                <?php for ($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++): ?>
                    <li class="page-item <?php echo $i === $currentPage ? 'active' : ''; ?>">
                        <a class="page-link" href="<?php echo BASE_URL; ?>?c=Customer&m=list&page=<?php echo $i; ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                <?php endfor; ?>

                <?php if ($currentPage < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link" href="<?php echo BASE_URL; ?>?c=Customer&m=list&page=<?php echo $currentPage + 1; ?>">Next</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="<?php echo BASE_URL; ?>?c=Customer&m=list&page=<?php echo $totalPages; ?>">Last</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>

        <div class="text-center text-muted small mt-3">
            Page <?php echo $currentPage; ?> of <?php echo $totalPages; ?> 
            (<?php echo $total; ?> total customers)
        </div>
    <?php endif; ?>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this customer? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a id="deleteLink" href="#" class="btn btn-danger">Delete</a>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(maKH) {
    const deleteLink = document.getElementById('deleteLink');
    deleteLink.href = '<?php echo BASE_URL; ?>?c=Customer&m=delete&maKH=' + maKH;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
