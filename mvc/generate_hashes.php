<?php
// Tạo các tài khoản với password hash

$accounts = [
    ['username' => 'khach_hang', 'password' => '123456', 'role' => 'khach_hang'],
    ['username' => 'boi_thuong', 'password' => '123456', 'role' => 'boi_thuong'],
    ['username' => 'phuong_tien', 'password' => '123456', 'role' => 'phuong_tien'],
    ['username' => 'ke_toan', 'password' => '123456', 'role' => 'ke_toan'],
];

echo "SELECT statements để update tài khoản:\n";
echo "=========================================\n\n";

foreach ($accounts as $account) {
    $hashedPassword = password_hash($account['password'], PASSWORD_BCRYPT, ['cost' => 10]);
    echo "UPDATE qlbh_taikhoan \n";
    echo "SET MatKhau = '" . $hashedPassword . "' \n";
    echo "WHERE TenTK = '" . $account['username'] . "';\n\n";
}

echo "\n=========================================\n";
echo "Tài khoản SQL:\n";
echo "=========================================\n\n";

foreach ($accounts as $account) {
    $hashedPassword = password_hash($account['password'], PASSWORD_BCRYPT, ['cost' => 10]);
    echo "Username: {$account['username']}\n";
    echo "Password (plain): {$account['password']}\n";
    echo "Password (hash): {$hashedPassword}\n";
    echo "Role: {$account['role']}\n";
    echo "---\n\n";
}

// Tạo INSERT statements cho tài khoản mới
echo "=========================================\n";
echo "Full SQL INSERT statements:\n";
echo "=========================================\n\n";

foreach ($accounts as $account) {
    $hashedPassword = password_hash($account['password'], PASSWORD_BCRYPT, ['cost' => 10]);
    $id = 'TK' . str_pad(array_search($account, $accounts) + 1, 3, '0', STR_PAD_LEFT);
    echo "INSERT INTO qlbh_taikhoan (MaTK, TenTK, MatKhau, VaiTro, TrangThai, CreatedAt, UpdatedAt) VALUES\n";
    echo "('{$id}', '{$account['username']}', '{$hashedPassword}', '{$account['role']}', 'HoatDong', NOW(), NOW());\n\n";
}
?>
