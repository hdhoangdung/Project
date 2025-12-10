<?php 
require_once __DIR__ . '/db.php'; 
session_start();

// Kiểm tra login (nếu cần)
if (!isset($_SESSION['username'])) {
    header("Location: formdn.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản Lý Khách Hàng - Bảo Hiểm Phương Tiện</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

  <div class="top-bar">
    <h1>Quản Lý Khách Hàng</h1>
    <div class="top-menu">
        <!-- Tên người dùng + dropdown -->
        <div class="user-menu">
            <div class="user-name" onclick="toggleMenu()">
                👤 Hi, <strong><?= htmlspecialchars($_SESSION['username']); ?></strong> ⌄
            </div>
            <div class="dropdown" id="dropdownMenu">
                <a href="dangxuat.php" class="logout">🚪 Đăng xuất</a>
            </div>
        </div>
        <!-- Thêm khách hàng -->
        <a href="add_customer.php" class="btn-add">+ Thêm Khách Hàng</a>
    </div>
</div>


    <!-- Thanh tìm kiếm -->
    <input type="text" id="search" placeholder="Tìm theo tên hoặc mã KH...">

    <!-- Bảng dữ liệu -->
    <table id="customerTable">
        <thead>
            <tr>
                <th>Họ Tên</th>
                <th>Mã KH</th>
                <th>SĐT</th>
                <th>Loại Bảo Hiểm</th>
                <th>Trạng Thái</th>
                <th>Hành Động</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (!isset($conn)) {
                die("<tr><td colspan='6'>Lỗi kết nối database: biến \$conn không tồn tại</td></tr>");
            }

            $sql = "SELECT c.*, GROUP_CONCAT(co.status) AS statuses 
                    FROM customers c 
                    LEFT JOIN contracts co ON c.id = co.customer_id 
                    GROUP BY c.id";

            $result = $conn->query($sql);

            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "
                    <tr>
                        <td>{$row['name']}</td>
                        <td>{$row['code']}</td>
                        <td>{$row['phone']}</td>
                        <td>{$row['insurance_type']}</td>
                        <td>{$row['statuses']}</td>
                        <td>
                            <a href='view_customer.php?id={$row['id']}'>Xem</a> |
                            <a href='edit_customer.php?id={$row['id']}'>Sửa</a> |
                            <a href='delete_customer.php?id={$row['id']}'>Xóa</a>
                        </td>
                    </tr>";
                }
            }
            ?>
        </tbody>
    </table>

    <!-- Search Script -->
    <script>
        document.getElementById("search").addEventListener("keyup", function () {
            let keyword = this.value.toLowerCase();
            let rows = document.querySelectorAll("#customerTable tbody tr");

            rows.forEach(row => {
                let name = row.cells[0].textContent.toLowerCase();
                let code = row.cells[1].textContent.toLowerCase();

                if (name.includes(keyword) || code.includes(keyword)) {
                    row.style.display = "";
                } else {
                    row.style.display = "none";
                
                }
                
            });
        });
    </script>
    <!-- Giới thiệu công ty -->
<section class="about-company">
    <div class="container">
        <h2>Về Bảo Hiểm Phương Tiện</h2>
        <p>
            Chúng tôi là công ty hàng đầu trong lĩnh vực bảo hiểm phương tiện tại Việt Nam. 
            Với hơn 10 năm kinh nghiệm, chúng tôi mang đến các giải pháp bảo hiểm toàn diện, 
            nhanh chóng và tin cậy cho khách hàng.
        </p>
        <p>
            Sứ mệnh của chúng tôi là bảo vệ tài sản của khách hàng và đem lại sự an tâm tuyệt đối. 
            Đội ngũ tư vấn chuyên nghiệp luôn sẵn sàng hỗ trợ bạn mọi lúc, mọi nơi.
        </p>
    </div>
</section>

<!-- Footer -->
<footer class="site-footer">
    <div class="container">
        <p>&copy; <?= date("Y"); ?> Bảo Hiểm Phương Tiện. All Rights Reserved.</p>
        <p>Địa chỉ: 123 Đường ABC, Quận 1, TP.HCM | Email: info@baohiempt.com | Hotline: 1900 1234</p>
    </div>
</footer>
<script>
    // Dropdown menu functionality
    function toggleMenu() {
        const dropdown = document.getElementById('dropdownMenu');
        dropdown.classList.toggle('show');
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        const dropdown = document.getElementById('dropdownMenu');
        const userMenu = document.querySelector('.user-menu');
        
        if (!userMenu.contains(e.target)) {
            dropdown.classList.remove('show');
        }
    });

    // Close dropdown when pressing Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const dropdown = document.getElementById('dropdownMenu');
            dropdown.classList.remove('show');
        }
    });
</script>

</body>
</html>
