USE qlbh;

-- Insert sample users for the four roles (passwords hashed with PHP's password_hash)
-- Please replace hashes with ones generated in your environment if needed.

INSERT INTO qlbh_taikhoan (TenTK, MatKhau, VaiTro, TrangThai) VALUES
('khach_hang', '$2y$10$u1m6sYh0YwQ0YtK1pQmM3eWq2p1aT6YxqXf9nGkz6dYw1G1Z8hE6', 'khach_hang', 'HoatDong'),
('phuong_tien', '$2y$10$u1m6sYh0YwQ0YtK1pQmM3eWq2p1aT6YxqXf9nGkz6dYw1G1Z8hE6', 'phuong_tien', 'HoatDong'),
('boi_thuong', '$2y$10$u1m6sYh0YwQ0YtK1pQmM3eWq2p1aT6YxqXf9nGkz6dYw1G1Z8hE6', 'boi_thuong', 'HoatDong'),
('ke_toan', '$2y$10$u1m6sYh0YwQ0YtK1pQmM3eWq2p1aT6YxqXf9nGkz6dYw1G1Z8hE6', 'ke_toan', 'HoatDong');

-- Example customer, vehicle, contract
INSERT INTO qlbh_khachhang (MaKH, TenKH, CCCD, DienThoai, DiaChi, TrangThai) VALUES
('KH001', 'Nguyen Van A', '012345678901', '0909000001', 'Hanoi', 1);

INSERT INTO qlbh_xe (MaXe, BienSoXe, SoKhung, SoMay, MaKH, HangXe, NamSX, LoaiXe, TrangThai) VALUES
('XE001', '29A-00001', 'SK001', 'SM001', 'KH001', 'Honda', 2020, 'Motorbike', 1);

INSERT INTO qlbh_hopdong (MaHD, MaKH, MaXe, NgayBD, NgayKT, SoTien, TrangThai) VALUES
('HD001', 'KH001', 'XE001', '2025-01-01', '2026-01-01', 1000.00, 1);
