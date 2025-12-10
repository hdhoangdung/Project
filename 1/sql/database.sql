-- Tạo database nếu chưa tồn tại
CREATE DATABASE IF NOT EXISTS insurance_management;
USE insurance_management;

-- Xóa các bảng cũ nếu tồn tại
DROP TABLE IF EXISTS transactions;
DROP TABLE IF EXISTS contracts;
DROP TABLE IF EXISTS customers;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS activity_logs;

-- Tạo bảng customers
CREATE TABLE customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_code VARCHAR(20) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    date_of_birth DATE NOT NULL,
    id_card VARCHAR(20) UNIQUE NOT NULL,
    address TEXT NOT NULL,
    phone VARCHAR(15) NOT NULL,
    email VARCHAR(100),
    occupation VARCHAR(100),
    insurance_type VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tạo bảng contracts
CREATE TABLE contracts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    contract_code VARCHAR(20) UNIQUE NOT NULL,
    customer_id INT NOT NULL,
    vehicle_code VARCHAR(50) NOT NULL,
    sign_date DATE NOT NULL,
    expiry_date DATE NOT NULL,
    insurance_type VARCHAR(100) NOT NULL,
    insurance_value DECIMAL(15,2) NOT NULL,
    premium DECIMAL(15,2) NOT NULL,
    status ENUM('active', 'suspended', 'expired') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
);

-- Tạo bảng transactions
CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    contract_id INT NOT NULL,
    transaction_type VARCHAR(50) NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    transaction_date DATE NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (contract_id) REFERENCES contracts(id) ON DELETE CASCADE
);

-- Tạo bảng users
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    role ENUM('admin', 'staff') DEFAULT 'staff',
    is_active BOOLEAN DEFAULT TRUE,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tạo bảng activity_logs
CREATE TABLE activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    action VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Thêm dữ liệu mẫu cho users (MẬT KHẨU: password)
INSERT INTO users (username, password, full_name, email, role) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Quản Trị Viên', 'admin@baohiemxe.com', 'admin'),
('nhanvien', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nhân Viên Kinh Doanh', 'nhanvien@baohiemxe.com', 'staff');

-- Thêm dữ liệu mẫu cho customers
INSERT INTO customers (customer_code, full_name, date_of_birth, id_card, address, phone, email, occupation, insurance_type) VALUES 
('KH000001', 'Nguyễn Văn An', '1985-03-15', '025123456789', '123 Đường Lê Lợi, Quận 1, TP.HCM', '0903123456', 'nguyenvanan@gmail.com', 'Kỹ sư', 'Bảo hiểm ô tô'),
('KH000002', 'Trần Thị Bình', '1990-07-22', '025123456788', '456 Đường Nguyễn Huệ, Quận 1, TP.HCM', '0903123457', 'tranthibinh@gmail.com', 'Giáo viên', 'Bảo hiểm xe máy'),
('KH000003', 'Lê Văn Cường', '1988-12-10', '025123456787', '789 Đường Pasteur, Quận 3, TP.HCM', '0903123458', 'levancuong@gmail.com', 'Doanh nhân', 'Bảo hiểm TNDS');

-- Thêm dữ liệu mẫu cho contracts
INSERT INTO contracts (contract_code, customer_id, vehicle_code, sign_date, expiry_date, insurance_type, insurance_value, premium, status) VALUES 
('HD000001', 1, '51A-12345', '2024-01-15', '2025-01-15', 'Bảo hiểm ô tô', 500000000, 7500000, 'active'),
('HD000002', 2, '59-B12345', '2024-02-01', '2025-02-01', 'Bảo hiểm xe máy', 40000000, 800000, 'active'),
('HD000003', 3, '51A-67890', '2024-01-20', '2025-01-20', 'Bảo hiểm TNDS', 300000000, 4500000, 'active');

-- Thêm dữ liệu mẫu cho transactions
INSERT INTO transactions (contract_id, transaction_type, amount, transaction_date, description) VALUES 
(1, 'Thanh toán phí', 7500000, '2024-01-15', 'Thanh toán phí bảo hiểm ban đầu'),
(2, 'Thanh toán phí', 800000, '2024-02-01', 'Thanh toán phí bảo hiểm xe máy'),
(3, 'Thanh toán phí', 4500000, '2024-01-20', 'Thanh toán phí bảo hiểm TNDS');
-- Đảm bảo mật khẩu được hash đúng (password = 'password')
UPDATE users SET password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' 
WHERE username IN ('admin', 'nhanvien');