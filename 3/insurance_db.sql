-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th10 26, 2025 lúc 10:06 PM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `insurance_db`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `contracts`
--

CREATE TABLE `contracts` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `sign_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `type` varchar(100) DEFAULT NULL,
  `value` decimal(10,2) DEFAULT NULL,
  `fee` decimal(10,2) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `vehicle_id` int(11) DEFAULT NULL,
  `status` enum('active','suspended','expired') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `contracts`
--

INSERT INTO `contracts` (`id`, `code`, `sign_date`, `expiry_date`, `type`, `value`, `fee`, `customer_id`, `vehicle_id`, `status`) VALUES
(1, 'HD001', '2023-01-01', '2024-01-01', 'Bảo hiểm thân vỏ', 50000000.00, 1000000.00, 1, 1, 'active'),
(2, 'KH003', '2025-11-06', '2026-10-16', '111', 1000000.00, 13345678.00, 3, 1, 'active');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `name` varchar(255) NOT NULL,
  `dob` date DEFAULT NULL,
  `id_card` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `occupation` varchar(100) DEFAULT NULL,
  `insurance_type` varchar(100) DEFAULT NULL,
  `history` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `customers`
--

INSERT INTO `customers` (`id`, `code`, `name`, `dob`, `id_card`, `address`, `phone`, `email`, `occupation`, `insurance_type`, `history`) VALUES
(1, 'KH001', 'Nguyễn Văn A', '1990-01-01', '123456789', 'Hà Nội', '0123456789', 'a@example.com', 'Kỹ sư', 'Xe máy', '[\"Giao dịch 1\", \"Giao dịch 2\"]'),
(2, 'KH002', 'Khánh Vũ', '2005-12-29', '01010101011012', 'Yên Bái', '01010110101', 'abcd@gmail.com', 'Sinh Viên', 'Xe tải', '\"\"Active\"\"'),
(3, 'KH003', 'Nguyễn Khánh Linh', '2005-07-07', '0101032030302', 'Hà Nội', '003304442222', 'abc@gmail.com', 'Sinh Viên', 'Xe máy', '\"\"Active\"\"');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `hoten` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `sodu` decimal(10,2) DEFAULT 100000.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `hoten`, `username`, `password`, `email`, `sodu`) VALUES
(0, 'khanh', '1', '$2y$10$pkFVQlGO2LQIXyxIQdlDP.EPi9/A4swEz7jbRdZpsvjSnJd3ozKzS', '1@gmail.com', 100000.00);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `vehicles`
--

CREATE TABLE `vehicles` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `vehicles`
--

INSERT INTO `vehicles` (`id`, `code`, `description`) VALUES
(1, 'PT001', 'Honda Wave 125cc');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `contracts`
--
ALTER TABLE `contracts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `vehicle_id` (`vehicle_id`);

--
-- Chỉ mục cho bảng `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Chỉ mục cho bảng `vehicles`
--
ALTER TABLE `vehicles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `contracts`
--
ALTER TABLE `contracts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `vehicles`
--
ALTER TABLE `vehicles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `contracts`
--
ALTER TABLE `contracts`
  ADD CONSTRAINT `contracts_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `contracts_ibfk_2` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
