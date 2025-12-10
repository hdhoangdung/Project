        </div>
    </main>
    
    <?php if (isset($_SESSION['user_id'])): ?>
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h4><i class="fas fa-car-crash"></i> Quản Lý Bảo Hiểm Xe</h4>
                    <p>Hệ thống quản lý bảo hiểm xe chuyên nghiệp, giúp quản lý khách hàng và hợp đồng hiệu quả.</p>
                </div>
                
                <div class="footer-section">
                    <h4><i class="fas fa-link"></i> Liên Kết Nhanh</h4>
                    <ul>
                        <li><a href="dashboard.php"><i class="fas fa-home"></i> Trang Chủ</a></li>
                        <li><a href="customers.php"><i class="fas fa-users"></i> Khách Hàng</a></li>
                        <li><a href="contracts.php"><i class="fas fa-file-contract"></i> Hợp Đồng</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2024 Quản Lý Bảo Hiểm Xe. All rights reserved.</p>
            </div>
        </div>
    </footer>
    <?php endif; ?>

    <script src="js/script.js"></script>
</body>
</html>