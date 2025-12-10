    </main>

    <!-- Footer -->
    <footer class="bg-dark text-white mt-5 py-4">
        <div class="container-fluid">
            <div class="row">
                <!-- About -->
                <div class="col-md-4 mb-3">
                    <h6 class="fw-bold"><i class="fas fa-shield-alt"></i> Vehicle Insurance</h6>
                    <p class="small text-muted">
                        Complete insurance management system for vehicle policies, claims processing, and accounting.
                    </p>
                </div>

                <!-- Quick Links -->
                <div class="col-md-4 mb-3">
                    <h6 class="fw-bold">Quick Links</h6>
                    <ul class="list-unstyled small">
                        <li><a href="<?php echo $baseURL ?? '/'; ?>" class="text-decoration-none text-muted">Home</a></li>
                        <li><a href="<?php echo $baseURL ?? '/'; ?>?c=Customer&m=list" class="text-decoration-none text-muted">Customers</a></li>
                        <li><a href="<?php echo $baseURL ?? '/'; ?>?c=Vehicle&m=list" class="text-decoration-none text-muted">Vehicles</a></li>
                        <li><a href="<?php echo $baseURL ?? '/'; ?>?c=Claims&m=list" class="text-decoration-none text-muted">Claims</a></li>
                        <li><a href="<?php echo $baseURL ?? '/'; ?>?c=Accounting&m=list" class="text-decoration-none text-muted">Accounting</a></li>
                    </ul>
                </div>

                <!-- Contact -->
                <div class="col-md-4 mb-3">
                    <h6 class="fw-bold">Support</h6>
                    <ul class="list-unstyled small text-muted">
                        <li><i class="fas fa-envelope"></i> support@insurance.local</li>
                        <li><i class="fas fa-phone"></i> +84 (0) 1234-5678</li>
                        <li><i class="fas fa-map-marker-alt"></i> Ho Chi Minh City, Vietnam</li>
                    </ul>
                </div>
            </div>

            <hr class="bg-secondary">

            <!-- Copyright -->
            <div class="row">
                <div class="col-md-6">
                    <p class="small text-muted mb-0">
                        &copy; <?php echo date('Y'); ?> Vehicle Insurance Management System. All rights reserved.
                    </p>
                </div>
                <div class="col-md-6 text-end">
                    <p class="small text-muted mb-0">
                        Version 1.0 | <a href="#" class="text-decoration-none text-muted">Terms</a> | 
                        <a href="#" class="text-decoration-none text-muted">Privacy</a>
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JS -->
    <script src="<?php echo $baseURL ?? '/'; ?>/assets/js/script.js"></script>

    <?php if (isset($customJS)): ?>
        <?php foreach ((array)$customJS as $js): ?>
            <script src="<?php echo $baseURL ?? '/'; ?><?php echo $js; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Debug Info (only in DEBUG mode) -->
    <?php if (DEBUG && isset($auth) && $auth->isLoggedIn()): ?>
        <script>
            console.log('User:', {
                id: <?php echo json_encode($auth->getUserID()); ?>,
                name: <?php echo json_encode($auth->getUsername()); ?>,
                role: <?php echo json_encode($auth->getRole()); ?>
            });
        </script>
    <?php endif; ?>
</body>
</html>
