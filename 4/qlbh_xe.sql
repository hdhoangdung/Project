-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th10 16, 2025 lúc 08:07 AM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `qlbh_xe`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `goibaohiem`
--

CREATE TABLE `goibaohiem` (
  `MaGoi` varchar(20) NOT NULL,
  `TenGoi` varchar(100) NOT NULL,
  `MoTa` varchar(500) DEFAULT NULL,
  `PhiBaoHiem` decimal(15,2) NOT NULL,
  `MucBoiThuong` decimal(15,2) NOT NULL,
  `ThoiHan` int(11) NOT NULL,
  `TrangThai` bit(1) DEFAULT b'1',
  `NgayTao` datetime DEFAULT current_timestamp()
) ;

--
-- Đang đổ dữ liệu cho bảng `goibaohiem`
--

INSERT INTO `goibaohiem` (`MaGoi`, `TenGoi`, `MoTa`, `PhiBaoHiem`, `MucBoiThuong`, `ThoiHan`, `TrangThai`, `NgayTao`) VALUES
('G001', 'Gói Cơ Bản', 'Bảo hiểm cơ bản', 1500000.00, 5000000.00, 12, b'1', '2025-11-15 00:44:29'),
('G002', 'Gói Tiêu Chuẩn', 'Bảo hiểm tiêu chuẩn', 3000000.00, 10000000.00, 12, b'1', '2025-11-15 00:44:29'),
('G003', 'Gói Cao Cấp', 'Bảo hiểm toàn diện', 6000000.00, 20000000.00, 12, b'1', '2025-11-15 00:44:29');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `hinhanhsuco`
--

CREATE TABLE `hinhanhsuco` (
  `MaHinhAnh` varchar(20) NOT NULL,
  `MaYC` varchar(20) DEFAULT NULL,
  `TenFile` varchar(255) NOT NULL,
  `DuongDan` varchar(500) NOT NULL,
  `NgayTaiLen` datetime DEFAULT current_timestamp(),
  `MoTa` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `hopdong`
--

CREATE TABLE `hopdong` (
  `MaHD` varchar(20) NOT NULL,
  `MaKH` varchar(20) DEFAULT NULL,
  `MaXe` varchar(20) DEFAULT NULL,
  `MaGoi` varchar(20) DEFAULT NULL,
  `NgayLap` date NOT NULL,
  `NgayHetHan` date NOT NULL,
  `PhiBaoHiem` decimal(15,2) NOT NULL,
  `TrangThai` varchar(20) DEFAULT 'Chưa thanh toán',
  `MaNV` varchar(20) DEFAULT NULL,
  `NgayTao` datetime DEFAULT current_timestamp()
) ;

--
-- Đang đổ dữ liệu cho bảng `hopdong`
--

INSERT INTO `hopdong` (`MaHD`, `MaKH`, `MaXe`, `MaGoi`, `NgayLap`, `NgayHetHan`, `PhiBaoHiem`, `TrangThai`, `MaNV`, `NgayTao`) VALUES
('HD20251101', 'KH001', 'XE001', 'G001', '2025-11-01', '2026-11-01', 1500000.00, 'Đã thanh toán', 'NV001', '2025-11-15 00:44:29'),
('HD20251102', 'KH002', 'XE002', 'G002', '2025-10-25', '2026-10-25', 3000000.00, 'Đã thanh toán', 'NV001', '2025-11-15 00:44:29'),
('HD20251103', 'KH003', 'XE003', 'G003', '2025-09-10', '2026-09-10', 6000000.00, 'Đã thanh toán', 'NV001', '2025-11-15 00:44:29'),
('HD20251104', 'KH004', 'XE004', 'G002', '2025-08-15', '2026-08-15', 3000000.00, 'Chưa thanh toán', 'NV001', '2025-11-15 00:44:29'),
('HD20251105', 'KH005', 'XE005', 'G001', '2025-07-20', '2026-07-20', 1500000.00, 'Đã thanh toán', 'NV001', '2025-11-15 00:44:29');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `khachhang`
--

CREATE TABLE `khachhang` (
  `MaKH` varchar(20) NOT NULL,
  `HoTen` varchar(100) NOT NULL,
  `NgaySinh` date NOT NULL,
  `GioiTinh` bit(1) NOT NULL,
  `DiaChi` varchar(200) NOT NULL,
  `SoDienThoai` varchar(10) NOT NULL,
  `Email` varchar(100) DEFAULT NULL,
  `CCCD` varchar(12) DEFAULT NULL,
  `NgayTao` datetime DEFAULT current_timestamp()
) ;

--
-- Đang đổ dữ liệu cho bảng `khachhang`
--

INSERT INTO `khachhang` (`MaKH`, `HoTen`, `NgaySinh`, `GioiTinh`, `DiaChi`, `SoDienThoai`, `Email`, `CCCD`, `NgayTao`) VALUES
('KH001', 'Nguyễn Văn A', '1980-02-15', b'1', 'Hà Nội', '0911000001', 'nv.a@example.com', '012345678901', '2025-11-15 00:44:29'),
('KH002', 'Trần Thị B', '1985-06-20', b'0', 'Hà Nội', '0911000002', 'tt.b@example.com', '012345678902', '2025-11-15 00:44:29'),
('KH003', 'Lê Văn C', '1990-09-10', b'1', 'Hà Nội', '0911000003', 'lv.c@example.com', '012345678903', '2025-11-15 00:44:29'),
('KH004', 'Phạm Thị D', '1992-11-05', b'0', 'Hà Nội', '0911000004', 'pt.d@example.com', '012345678904', '2025-11-15 00:44:29'),
('KH005', 'Hoàng Văn E', '1978-01-30', b'1', 'Hà Nội', '0911000005', 'hv.e@example.com', '012345678905', '2025-11-15 00:44:29');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `lichsuthaydoi`
--

CREATE TABLE `lichsuthaydoi` (
  `MaLog` int(11) NOT NULL,
  `BangDuLieu` varchar(50) NOT NULL,
  `MaBanGhi` varchar(20) NOT NULL,
  `HanhDong` varchar(20) NOT NULL,
  `DuLieuCu` text DEFAULT NULL,
  `DuLieuMoi` text DEFAULT NULL,
  `MaNV` varchar(20) DEFAULT NULL,
  `ThoiGian` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `lichsuthaydoi`
--

INSERT INTO `lichsuthaydoi` (`MaLog`, `BangDuLieu`, `MaBanGhi`, `HanhDong`, `DuLieuCu`, `DuLieuMoi`, `MaNV`, `ThoiGian`) VALUES
(1, 'TaiKhoan', 'TK001', 'LOGIN', NULL, '{\"ip\":\"::1\"}', 'NV001', '2025-11-14 23:40:17'),
(2, 'TaiKhoan', 'TK001', 'LOGIN', NULL, '{\"ip\":\"::1\"}', 'NV001', '2025-11-14 23:48:05'),
(3, 'TaiKhoan', 'TK001', 'LOGIN', NULL, '{\"ip\":\"::1\"}', 'NV001', '2025-11-14 23:50:00'),
(4, 'TaiKhoan', 'TK001', 'LOGIN', NULL, '{\"ip\":\"::1\"}', 'NV001', '2025-11-14 23:56:02'),
(5, 'TaiKhoan', 'TK001', 'LOGIN', NULL, '{\"ip\":\"::1\"}', 'NV001', '2025-11-14 23:57:55'),
(6, 'TaiKhoan', 'TK001', 'LOGIN', NULL, '{\"ip\":\"::1\"}', 'NV001', '2025-11-15 00:02:48'),
(7, 'TaiKhoan', 'TK001', 'LOGIN', NULL, '{\"ip\":\"::1\"}', 'NV001', '2025-11-15 00:04:47'),
(8, 'TaiKhoan', 'TK001', 'LOGIN', NULL, '{\"ip\":\"::1\"}', 'NV001', '2025-11-15 00:06:02'),
(9, 'TaiKhoan', 'TK001', 'LOGIN', NULL, '{\"ip\":\"::1\"}', 'NV001', '2025-11-15 00:08:26'),
(10, 'TaiKhoan', 'TK001', 'LOGIN', NULL, '{\"ip\":\"::1\"}', 'NV001', '2025-11-15 00:16:30'),
(11, 'TaiKhoan', 'TK001', 'LOGIN', NULL, '{\"ip\":\"::1\"}', 'NV001', '2025-11-15 00:18:11'),
(12, 'TaiKhoan', 'TK001', 'LOGIN', NULL, '{\"ip\":\"::1\"}', 'NV001', '2025-11-15 00:19:43'),
(13, 'TaiKhoan', 'TK001', 'LOGIN', NULL, '{\"ip\":\"::1\"}', 'NV001', '2025-11-15 00:25:17'),
(14, 'PhieuThu', 'PT15112025001', 'INSERT', NULL, '{\"MaHD\":\"HD20251101\",\"SoTien\":\"1500000.00\",\"NgayThu\":\"2025-11-15\"}', 'NV001', '2025-11-15 00:44:55'),
(15, 'PhieuThu', 'PT15112025001', 'INSERT', NULL, '{\"MaHD\":\"HD20251105\",\"SoTien\":\"15000000.00\",\"NgayThu\":\"2025-11-15\"}', 'NV001', '2025-11-15 01:03:41');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `nhanvien`
--

CREATE TABLE `nhanvien` (
  `MaNV` varchar(20) NOT NULL,
  `HoTen` varchar(100) NOT NULL,
  `NgaySinh` date NOT NULL,
  `GioiTinh` bit(1) NOT NULL COMMENT '0: Nữ, 1: Nam',
  `DiaChi` varchar(200) DEFAULT NULL,
  `SoDienThoai` varchar(10) NOT NULL,
  `Email` varchar(100) DEFAULT NULL,
  `PhongBan` varchar(50) NOT NULL,
  `TrangThai` bit(1) DEFAULT b'1' COMMENT '0: Nghỉ việc, 1: Đang làm',
  `NgayTao` datetime DEFAULT current_timestamp()
) ;

--
-- Đang đổ dữ liệu cho bảng `nhanvien`
--

INSERT INTO `nhanvien` (`MaNV`, `HoTen`, `NgaySinh`, `GioiTinh`, `DiaChi`, `SoDienThoai`, `Email`, `PhongBan`, `TrangThai`, `NgayTao`) VALUES
('NV001', 'Nguyễn Văn Kế Toán', '1990-05-15', b'1', 'Hà Nội', '0912345678', 'ketoan@company.com', 'Kế toán', b'1', '2025-11-14 18:25:03'),
('NV002', 'Trần Thị Quản Lý', '1985-03-20', b'0', 'Hà Nội', '0923456789', 'quanly@company.com', 'Quản lý', b'1', '2025-11-14 18:25:03'),
('NV003', 'Lê Văn Giám Định', '1992-07-10', b'1', 'Hà Nội', '0934567890', 'giamdinh@company.com', 'Giám định', b'1', '2025-11-14 18:25:03');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `phieuchi`
--

CREATE TABLE `phieuchi` (
  `MaPC` varchar(20) NOT NULL,
  `MaYC` varchar(20) DEFAULT NULL,
  `NgayChi` date NOT NULL,
  `SoTien` decimal(15,2) NOT NULL,
  `GhiChu` varchar(500) DEFAULT NULL,
  `MaNV` varchar(20) DEFAULT NULL,
  `TrangThai` varchar(20) DEFAULT 'Chờ chi trả',
  `NgayTao` datetime DEFAULT current_timestamp()
) ;

--
-- Đang đổ dữ liệu cho bảng `phieuchi`
--

INSERT INTO `phieuchi` (`MaPC`, `MaYC`, `NgayChi`, `SoTien`, `GhiChu`, `MaNV`, `TrangThai`, `NgayTao`) VALUES
('PC02112025001', 'YC001', '2025-11-02', 4500000.00, 'Chi trả bồi thường', 'NV001', 'Đã chi trả', '2025-11-15 00:44:29');

--
-- Bẫy `phieuchi`
--
DELIMITER $$
CREATE TRIGGER `trg_SinhMaPhieuChi` BEFORE INSERT ON `phieuchi` FOR EACH ROW BEGIN
    DECLARE ngay_str VARCHAR(8);
    DECLARE so_thu_tu INT;
    SET ngay_str = DATE_FORMAT(NEW.NgayChi, '%d%m%Y');
    SELECT COUNT(*)+1 INTO so_thu_tu FROM PhieuChi WHERE NgayChi = NEW.NgayChi;
    SET NEW.MaPC = CONCAT('PC', ngay_str, LPAD(so_thu_tu, 3, '0'));
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `phieuthu`
--

CREATE TABLE `phieuthu` (
  `MaPT` varchar(20) NOT NULL,
  `MaHD` varchar(20) DEFAULT NULL,
  `NgayThu` date NOT NULL,
  `SoTien` decimal(15,2) NOT NULL,
  `GhiChu` varchar(500) DEFAULT NULL,
  `MaNV` varchar(20) DEFAULT NULL,
  `TrangThai` varchar(20) DEFAULT 'Hoạt động',
  `NgayTao` datetime DEFAULT current_timestamp()
) ;

--
-- Đang đổ dữ liệu cho bảng `phieuthu`
--

INSERT INTO `phieuthu` (`MaPT`, `MaHD`, `NgayThu`, `SoTien`, `GhiChu`, `MaNV`, `TrangThai`, `NgayTao`) VALUES
('PT11092025001', 'HD20251103', '2025-09-11', 6000000.00, 'Thanh toán theo hợp đồng', 'NV001', 'Hoạt động', '2025-11-15 00:44:29'),
('PT15112025001', 'HD20251105', '2025-11-15', 15000000.00, '', 'NV001', 'Hoạt động', '2025-11-15 01:03:41');

--
-- Bẫy `phieuthu`
--
DELIMITER $$
CREATE TRIGGER `trg_SinhMaPhieuThu` BEFORE INSERT ON `phieuthu` FOR EACH ROW BEGIN
    DECLARE ngay_str VARCHAR(8);
    DECLARE so_thu_tu INT;
    SET ngay_str = DATE_FORMAT(NEW.NgayThu, '%d%m%Y');
    SELECT COUNT(*)+1 INTO so_thu_tu FROM PhieuThu WHERE NgayThu = NEW.NgayThu;
    SET NEW.MaPT = CONCAT('PT', ngay_str, LPAD(so_thu_tu, 3, '0'));
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_UpdateTrangThaiHD` AFTER INSERT ON `phieuthu` FOR EACH ROW BEGIN
    UPDATE HopDong SET TrangThai = 'Đã thanh toán'
    WHERE MaHD = NEW.MaHD;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `taikhoan`
--

CREATE TABLE `taikhoan` (
  `MaTK` varchar(20) NOT NULL,
  `MaNV` varchar(20) DEFAULT NULL,
  `TenDangNhap` varchar(50) NOT NULL,
  `MatKhau` varchar(255) NOT NULL,
  `VaiTro` varchar(20) NOT NULL,
  `TrangThai` bit(1) DEFAULT b'1',
  `SoLanDangNhapSai` int(11) DEFAULT 0,
  `ThoiGianKhoa` datetime DEFAULT NULL,
  `NgayTao` datetime DEFAULT current_timestamp()
) ;

--
-- Đang đổ dữ liệu cho bảng `taikhoan`
--

INSERT INTO `taikhoan` (`MaTK`, `MaNV`, `TenDangNhap`, `MatKhau`, `VaiTro`, `TrangThai`, `SoLanDangNhapSai`, `ThoiGianKhoa`, `NgayTao`) VALUES
('TK001', 'NV001', 'ketoan', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'KeToan', b'1', 0, NULL, '2025-11-14 18:25:03'),
('TK002', 'NV002', 'quanly', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'QuanLy', b'1', 0, NULL, '2025-11-14 18:25:03'),
('TK003', 'NV003', 'giamdinh', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'GiamDinh', b'1', 0, NULL, '2025-11-14 18:25:03');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `xeoto`
--

CREATE TABLE `xeoto` (
  `MaXe` varchar(20) NOT NULL,
  `MaKH` varchar(20) DEFAULT NULL,
  `BienSo` varchar(15) NOT NULL,
  `HangXe` varchar(50) NOT NULL,
  `DongXe` varchar(50) NOT NULL,
  `NamSanXuat` int(11) NOT NULL,
  `MauSac` varchar(30) DEFAULT NULL,
  `SoKhung` varchar(17) NOT NULL,
  `SoMay` varchar(20) NOT NULL,
  `NgayTao` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `xeoto`
--

INSERT INTO `xeoto` (`MaXe`, `MaKH`, `BienSo`, `HangXe`, `DongXe`, `NamSanXuat`, `MauSac`, `SoKhung`, `SoMay`, `NgayTao`) VALUES
('XE001', 'KH001', '30A-10001', 'Toyota', 'Vios', 2018, 'Trắng', 'KS00000000000001', 'SM0000000001', '2025-11-15 00:44:29'),
('XE002', 'KH002', '30A-10002', 'Honda', 'City', 2019, 'Đỏ', 'KS00000000000002', 'SM0000000002', '2025-11-15 00:44:29'),
('XE003', 'KH003', '30A-10003', 'Ford', 'Ranger', 2017, 'Đen', 'KS00000000000003', 'SM0000000003', '2025-11-15 00:44:29'),
('XE004', 'KH004', '30A-10004', 'Hyundai', 'Accent', 2020, 'Bạc', 'KS00000000000004', 'SM0000000004', '2025-11-15 00:44:29'),
('XE005', 'KH005', '30A-10005', 'Kia', 'Morning', 2016, 'Xanh', 'KS00000000000005', 'SM0000000005', '2025-11-15 00:44:29');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `yeucauboithuong`
--

CREATE TABLE `yeucauboithuong` (
  `MaYC` varchar(20) NOT NULL,
  `MaHD` varchar(20) DEFAULT NULL,
  `NgayYeuCau` date NOT NULL,
  `NgaySuCo` date NOT NULL,
  `DiaDiemSuCo` varchar(200) NOT NULL,
  `MoTaSuCo` varchar(1000) NOT NULL,
  `SoTienDeXuat` decimal(15,2) NOT NULL,
  `SoTienDuyet` decimal(15,2) DEFAULT NULL,
  `TrangThai` varchar(20) DEFAULT 'Chờ duyệt',
  `LyDoTuChoi` varchar(500) DEFAULT NULL,
  `MaNVGiamDinh` varchar(20) DEFAULT NULL,
  `NgayDuyet` date DEFAULT NULL,
  `NgayTao` datetime DEFAULT current_timestamp()
) ;

--
-- Đang đổ dữ liệu cho bảng `yeucauboithuong`
--

INSERT INTO `yeucauboithuong` (`MaYC`, `MaHD`, `NgayYeuCau`, `NgaySuCo`, `DiaDiemSuCo`, `MoTaSuCo`, `SoTienDeXuat`, `SoTienDuyet`, `TrangThai`, `LyDoTuChoi`, `MaNVGiamDinh`, `NgayDuyet`, `NgayTao`) VALUES
('YC001', 'HD20251102', '2025-10-30', '2025-10-28', 'Hà Nội', 'Va chạm nhẹ', 5000000.00, 4500000.00, 'Đã duyệt', NULL, 'NV003', NULL, '2025-11-15 00:44:29'),
('YC002', 'HD20251101', '2025-11-05', '2025-11-04', 'Hà Nội', 'Bị mất gương', 200000.00, NULL, 'Chờ duyệt', NULL, NULL, NULL, '2025-11-15 00:44:29');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `goibaohiem`
--
ALTER TABLE `goibaohiem`
  ADD PRIMARY KEY (`MaGoi`);

--
-- Chỉ mục cho bảng `hinhanhsuco`
--
ALTER TABLE `hinhanhsuco`
  ADD PRIMARY KEY (`MaHinhAnh`),
  ADD KEY `MaYC` (`MaYC`);

--
-- Chỉ mục cho bảng `hopdong`
--
ALTER TABLE `hopdong`
  ADD PRIMARY KEY (`MaHD`),
  ADD KEY `MaKH` (`MaKH`),
  ADD KEY `MaXe` (`MaXe`),
  ADD KEY `MaGoi` (`MaGoi`),
  ADD KEY `MaNV` (`MaNV`);

--
-- Chỉ mục cho bảng `khachhang`
--
ALTER TABLE `khachhang`
  ADD PRIMARY KEY (`MaKH`),
  ADD UNIQUE KEY `CCCD` (`CCCD`);

--
-- Chỉ mục cho bảng `lichsuthaydoi`
--
ALTER TABLE `lichsuthaydoi`
  ADD PRIMARY KEY (`MaLog`);

--
-- Chỉ mục cho bảng `nhanvien`
--
ALTER TABLE `nhanvien`
  ADD PRIMARY KEY (`MaNV`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- Chỉ mục cho bảng `phieuchi`
--
ALTER TABLE `phieuchi`
  ADD PRIMARY KEY (`MaPC`),
  ADD KEY `MaYC` (`MaYC`),
  ADD KEY `MaNV` (`MaNV`);

--
-- Chỉ mục cho bảng `phieuthu`
--
ALTER TABLE `phieuthu`
  ADD PRIMARY KEY (`MaPT`),
  ADD KEY `MaHD` (`MaHD`),
  ADD KEY `MaNV` (`MaNV`);

--
-- Chỉ mục cho bảng `taikhoan`
--
ALTER TABLE `taikhoan`
  ADD PRIMARY KEY (`MaTK`),
  ADD UNIQUE KEY `TenDangNhap` (`TenDangNhap`),
  ADD UNIQUE KEY `MaNV` (`MaNV`);

--
-- Chỉ mục cho bảng `xeoto`
--
ALTER TABLE `xeoto`
  ADD PRIMARY KEY (`MaXe`),
  ADD UNIQUE KEY `BienSo` (`BienSo`),
  ADD UNIQUE KEY `SoKhung` (`SoKhung`),
  ADD UNIQUE KEY `SoMay` (`SoMay`),
  ADD KEY `MaKH` (`MaKH`);

--
-- Chỉ mục cho bảng `yeucauboithuong`
--
ALTER TABLE `yeucauboithuong`
  ADD PRIMARY KEY (`MaYC`),
  ADD KEY `MaHD` (`MaHD`),
  ADD KEY `MaNVGiamDinh` (`MaNVGiamDinh`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `lichsuthaydoi`
--
ALTER TABLE `lichsuthaydoi`
  MODIFY `MaLog` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `hinhanhsuco`
--
ALTER TABLE `hinhanhsuco`
  ADD CONSTRAINT `hinhanhsuco_ibfk_1` FOREIGN KEY (`MaYC`) REFERENCES `yeucauboithuong` (`MaYC`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `hopdong`
--
ALTER TABLE `hopdong`
  ADD CONSTRAINT `hopdong_ibfk_1` FOREIGN KEY (`MaKH`) REFERENCES `khachhang` (`MaKH`) ON UPDATE CASCADE,
  ADD CONSTRAINT `hopdong_ibfk_2` FOREIGN KEY (`MaXe`) REFERENCES `xeoto` (`MaXe`) ON UPDATE CASCADE,
  ADD CONSTRAINT `hopdong_ibfk_3` FOREIGN KEY (`MaGoi`) REFERENCES `goibaohiem` (`MaGoi`) ON UPDATE CASCADE,
  ADD CONSTRAINT `hopdong_ibfk_4` FOREIGN KEY (`MaNV`) REFERENCES `nhanvien` (`MaNV`) ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `phieuchi`
--
ALTER TABLE `phieuchi`
  ADD CONSTRAINT `phieuchi_ibfk_1` FOREIGN KEY (`MaYC`) REFERENCES `yeucauboithuong` (`MaYC`) ON UPDATE CASCADE,
  ADD CONSTRAINT `phieuchi_ibfk_2` FOREIGN KEY (`MaNV`) REFERENCES `nhanvien` (`MaNV`) ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `phieuthu`
--
ALTER TABLE `phieuthu`
  ADD CONSTRAINT `phieuthu_ibfk_1` FOREIGN KEY (`MaHD`) REFERENCES `hopdong` (`MaHD`) ON UPDATE CASCADE,
  ADD CONSTRAINT `phieuthu_ibfk_2` FOREIGN KEY (`MaNV`) REFERENCES `nhanvien` (`MaNV`) ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `taikhoan`
--
ALTER TABLE `taikhoan`
  ADD CONSTRAINT `taikhoan_ibfk_1` FOREIGN KEY (`MaNV`) REFERENCES `nhanvien` (`MaNV`) ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `xeoto`
--
ALTER TABLE `xeoto`
  ADD CONSTRAINT `xeoto_ibfk_1` FOREIGN KEY (`MaKH`) REFERENCES `khachhang` (`MaKH`) ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `yeucauboithuong`
--
ALTER TABLE `yeucauboithuong`
  ADD CONSTRAINT `yeucauboithuong_ibfk_1` FOREIGN KEY (`MaHD`) REFERENCES `hopdong` (`MaHD`) ON UPDATE CASCADE,
  ADD CONSTRAINT `yeucauboithuong_ibfk_2` FOREIGN KEY (`MaNVGiamDinh`) REFERENCES `nhanvien` (`MaNV`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
