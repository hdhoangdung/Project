-- ============================================================================
-- Vehicle Insurance Management System - Complete Database Schema
-- Module 0B - Database Design
-- ============================================================================
-- This schema supports a complete insurance management system with:
-- - Customer management with vehicle ownership
-- - Insurance contracts linking customers to vehicles
-- - Claims workflow: Request → Assessment → Approval → Payout
-- - Accounting: Receipt tracking and payout management
-- - Complete audit logging via TrangThai (soft delete) and Lichsu table
-- ============================================================================

-- Drop existing database and recreate
DROP DATABASE IF EXISTS qlbh;
CREATE DATABASE IF NOT EXISTS qlbh 
  CHARACTER SET utf8mb4 
  COLLATE utf8mb4_unicode_ci;

USE qlbh;

-- Disable foreign key checks during table creation
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================================
-- TABLE 1: qlbh_khachhang (Customers)
-- ============================================================================
-- Stores customer (policyholder) information
-- CCCD = Citizen Identification Card (unique identifier in Vietnam)
-- TrangThai: 1=Active, 0=Soft Deleted
CREATE TABLE qlbh_khachhang (
  MaKH VARCHAR(32) NOT NULL PRIMARY KEY COMMENT 'Customer ID',
  TenKH VARCHAR(255) NOT NULL COMMENT 'Customer name',
  CCCD VARCHAR(12) NOT NULL UNIQUE COMMENT 'Citizen ID (9-12 digits)',
  DienThoai VARCHAR(20) COMMENT 'Phone number',
  DiaChi VARCHAR(255) COMMENT 'Address',
  TrangThai TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=Active, 0=Deleted',
  CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Record creation time',
  UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last update time',
  
  INDEX idx_cccd (CCCD) COMMENT 'Search by citizen ID',
  INDEX idx_status (TrangThai) COMMENT 'Filter by status'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Customer/Policyholder records';

-- ============================================================================
-- TABLE 2: qlbh_xe (Vehicles)
-- ============================================================================
-- Stores vehicle information owned by customers
-- BienSoXe = License plate (unique vehicle identifier)
-- SoKhung = Chassis number (unique structural identifier)
-- SoMay = Engine number (unique engine identifier)
CREATE TABLE qlbh_xe (
  MaXe VARCHAR(32) NOT NULL PRIMARY KEY COMMENT 'Vehicle ID',
  BienSoXe VARCHAR(32) NOT NULL UNIQUE COMMENT 'License plate (unique)',
  SoKhung VARCHAR(64) NOT NULL UNIQUE COMMENT 'Chassis number (unique)',
  SoMay VARCHAR(64) NOT NULL UNIQUE COMMENT 'Engine number (unique)',
  MaKH VARCHAR(32) NOT NULL COMMENT 'Owner customer ID (FK)',
  HangXe VARCHAR(128) COMMENT 'Vehicle brand',
  NamSX INT COMMENT 'Manufacturing year',
  LoaiXe VARCHAR(128) COMMENT 'Vehicle type/category',
  TrangThai TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=Active, 0=Deleted',
  CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Record creation time',
  UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last update time',
  
  CONSTRAINT fk_xe_khachhang FOREIGN KEY (MaKH) 
    REFERENCES qlbh_khachhang(MaKH) 
    ON DELETE RESTRICT 
    ON UPDATE CASCADE 
    COMMENT 'Link to owner customer',
  
  INDEX idx_bien_so (BienSoXe) COMMENT 'Search by license plate',
  INDEX idx_makh (MaKH) COMMENT 'Find vehicles by customer',
  INDEX idx_status (TrangThai) COMMENT 'Filter by status'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Vehicle records linked to customers';

-- ============================================================================
-- TABLE 3: qlbh_hopdong (Contracts)
-- ============================================================================
-- Insurance contracts linking customers to vehicles
-- Links a customer's vehicle to an insurance policy
-- Duration: NgayBD (start) to NgayKT (end)
-- SoTien = Total premium amount
CREATE TABLE qlbh_hopdong (
  MaHD VARCHAR(32) NOT NULL PRIMARY KEY COMMENT 'Contract ID',
  MaKH VARCHAR(32) NOT NULL COMMENT 'Customer ID (FK)',
  MaXe VARCHAR(32) NOT NULL COMMENT 'Vehicle ID (FK)',
  NgayBD DATE NOT NULL COMMENT 'Contract start date',
  NgayKT DATE NOT NULL COMMENT 'Contract end date',
  SoTien DECIMAL(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Total premium (VND)',
  TrangThai TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=Active, 0=Expired/Deleted',
  CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Record creation time',
  UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last update time',
  
  CONSTRAINT fk_hd_khachhang FOREIGN KEY (MaKH) 
    REFERENCES qlbh_khachhang(MaKH) 
    ON DELETE RESTRICT 
    ON UPDATE CASCADE 
    COMMENT 'Link to customer',
    
  CONSTRAINT fk_hd_xe FOREIGN KEY (MaXe) 
    REFERENCES qlbh_xe(MaXe) 
    ON DELETE RESTRICT 
    ON UPDATE CASCADE 
    COMMENT 'Link to insured vehicle',
  
  INDEX idx_makh (MaKH) COMMENT 'Find contracts by customer',
  INDEX idx_maxe (MaXe) COMMENT 'Find contracts by vehicle',
  INDEX idx_dates (NgayBD, NgayKT) COMMENT 'Search by date range',
  INDEX idx_status (TrangThai) COMMENT 'Filter by status'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Insurance contracts (policy documents)';

-- ============================================================================
-- TABLE 4: qlbh_yeucau (Claims - Request Phase)
-- ============================================================================
-- Customer claim requests
-- Initiated when customer files a claim for an incident
-- First step in claims workflow
CREATE TABLE qlbh_yeucau (
  MaYC VARCHAR(32) NOT NULL PRIMARY KEY COMMENT 'Claim request ID',
  MaHD VARCHAR(32) NOT NULL COMMENT 'Related contract ID (FK)',
  MaKH VARCHAR(32) NOT NULL COMMENT 'Claimant customer ID (FK)',
  MaXe VARCHAR(32) NOT NULL COMMENT 'Vehicle involved ID (FK)',
  NoiDung LONGTEXT COMMENT 'Claim description/incident details',
  TrangThai TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=Submitted, 0=Cancelled',
  CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Claim submission time',
  UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last update time',
  
  CONSTRAINT fk_yc_hopdong FOREIGN KEY (MaHD) 
    REFERENCES qlbh_hopdong(MaHD) 
    ON DELETE RESTRICT 
    ON UPDATE CASCADE 
    COMMENT 'Link to contract',
    
  CONSTRAINT fk_yc_khachhang FOREIGN KEY (MaKH) 
    REFERENCES qlbh_khachhang(MaKH) 
    ON DELETE RESTRICT 
    ON UPDATE CASCADE 
    COMMENT 'Link to claimant',
    
  CONSTRAINT fk_yc_xe FOREIGN KEY (MaXe) 
    REFERENCES qlbh_xe(MaXe) 
    ON DELETE RESTRICT 
    ON UPDATE CASCADE 
    COMMENT 'Link to vehicle involved',
  
  INDEX idx_mahd (MaHD) COMMENT 'Find claims by contract',
  INDEX idx_makh (MaKH) COMMENT 'Find claims by customer',
  INDEX idx_maxe (MaXe) COMMENT 'Find claims by vehicle',
  INDEX idx_status (TrangThai) COMMENT 'Filter by status'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Claim request records (workflow step 1)';

-- ============================================================================
-- TABLE 5: qlbh_thamdinh (Claims - Assessment Phase)
-- ============================================================================
-- Insurance assessor evaluates the claim
-- Second step in claims workflow
-- One assessment per claim request
CREATE TABLE qlbh_thamdinh (
  MaTD VARCHAR(32) NOT NULL PRIMARY KEY COMMENT 'Assessment ID',
  MaYC VARCHAR(32) NOT NULL UNIQUE COMMENT 'Related claim request (FK, one-to-one)',
  KetQua LONGTEXT COMMENT 'Assessment findings/results',
  TrangThai TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0=Pending, 1=Completed',
  CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Assessment creation time',
  UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last update time',
  
  CONSTRAINT fk_td_yc FOREIGN KEY (MaYC) 
    REFERENCES qlbh_yeucau(MaYC) 
    ON DELETE RESTRICT 
    ON UPDATE CASCADE 
    COMMENT 'Link to claim request (one claim has one assessment)',
  
  INDEX idx_mayc (MaYC) COMMENT 'Find assessment for claim',
  INDEX idx_status (TrangThai) COMMENT 'Filter by completion status'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Claim assessment records (workflow step 2)';

-- ============================================================================
-- TABLE 6: qlbh_pheduyet (Claims - Approval Phase)
-- ============================================================================
-- Claims manager approves or rejects assessment
-- Third step in claims workflow
-- One approval per assessment
CREATE TABLE qlbh_pheduyet (
  MaPD VARCHAR(32) NOT NULL PRIMARY KEY COMMENT 'Approval ID',
  MaTD VARCHAR(32) NOT NULL UNIQUE COMMENT 'Related assessment (FK, one-to-one)',
  QuyetDinh LONGTEXT COMMENT 'Approval decision/reason',
  TrangThai TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0=Pending, 1=Approved',
  CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Approval creation time',
  UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last update time',
  
  CONSTRAINT fk_pd_td FOREIGN KEY (MaTD) 
    REFERENCES qlbh_thamdinh(MaTD) 
    ON DELETE RESTRICT 
    ON UPDATE CASCADE 
    COMMENT 'Link to assessment (one assessment has one approval)',
  
  INDEX idx_matd (MaTD) COMMENT 'Find approval for assessment',
  INDEX idx_status (TrangThai) COMMENT 'Filter by approval status'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Claim approval/decision records (workflow step 3)';

-- ============================================================================
-- TABLE 7: qlbh_phieuthu (Accounting - Receipt/Payment Receipt)
-- ============================================================================
-- Records customer premium payments received
-- Tracks money received from customers for contracts
CREATE TABLE qlbh_phieuthu (
  MaPT VARCHAR(32) NOT NULL PRIMARY KEY COMMENT 'Receipt ID',
  MaHD VARCHAR(32) NOT NULL COMMENT 'Contract paid for (FK)',
  SoTien DECIMAL(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Amount received (VND)',
  NgayThu DATE NOT NULL COMMENT 'Payment receipt date',
  TrangThai TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=Valid, 0=Cancelled',
  CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Receipt creation time',
  UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last update time',
  
  CONSTRAINT fk_pt_hopdong FOREIGN KEY (MaHD) 
    REFERENCES qlbh_hopdong(MaHD) 
    ON DELETE RESTRICT 
    ON UPDATE CASCADE 
    COMMENT 'Link to contract',
  
  INDEX idx_mahd (MaHD) COMMENT 'Find receipts for contract',
  INDEX idx_ngay (NgayThu) COMMENT 'Search by payment date',
  INDEX idx_status (TrangThai) COMMENT 'Filter by status'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Customer payment receipts (incoming money)';

-- ============================================================================
-- TABLE 8: qlbh_phieuchi (Accounting - Payout/Claims Payment)
-- ============================================================================
-- Records insurance payouts to customers for approved claims
-- Linked to approved claims (via MaPD) and tracks money paid out
CREATE TABLE qlbh_phieuchi (
  MaPC VARCHAR(32) NOT NULL PRIMARY KEY COMMENT 'Payout ID',
  MaPD VARCHAR(32) NOT NULL COMMENT 'Related approval (FK)',
  SoTien DECIMAL(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Amount paid out (VND)',
  NgayChi DATE NOT NULL COMMENT 'Payout date',
  TrangThai TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=Valid, 0=Cancelled',
  CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Payout creation time',
  UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last update time',
  
  CONSTRAINT fk_pc_pheduyet FOREIGN KEY (MaPD) 
    REFERENCES qlbh_pheduyet(MaPD) 
    ON DELETE RESTRICT 
    ON UPDATE CASCADE 
    COMMENT 'Link to claim approval (one approval can have one payout)',
  
  INDEX idx_mapd (MaPD) COMMENT 'Find payout for approval',
  INDEX idx_ngay (NgayChi) COMMENT 'Search by payout date',
  INDEX idx_status (TrangThai) COMMENT 'Filter by status'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Insurance claim payouts (outgoing money)';

-- ============================================================================
-- TABLE 9: qlbh_taikhoan (Users/Accounts)
-- ============================================================================
-- System user accounts for staff members
-- Four roles: Customer Staff, Vehicle Staff, Claims Staff, Accounting Staff
-- Passwords must be hashed (bcrypt recommended)
CREATE TABLE qlbh_taikhoan (
  UserID INT NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'User ID',
  username VARCHAR(100) NOT NULL UNIQUE COMMENT 'Login username',
  password VARCHAR(255) NOT NULL COMMENT 'Hashed password (bcrypt)',
  role VARCHAR(64) NOT NULL COMMENT 'User role: Customer Staff|Vehicle Staff|Claims Staff|Accounting Staff',
  TrangThai TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=Active, 0=Disabled',
  CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Account creation time',
  UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last login/update time',
  
  INDEX idx_username (username) COMMENT 'Fast user lookup by username',
  INDEX idx_role (role) COMMENT 'Find users by role'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='System user accounts with role-based access';

-- ============================================================================
-- TABLE 10: qlbh_lichsu (Audit Log)
-- ============================================================================
-- System audit trail: all create/update/delete operations
-- Supports compliance, debugging, and accountability
-- OldData/NewData store JSON snapshots for change tracking
CREATE TABLE qlbh_lichsu (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'Log entry ID',
  User VARCHAR(100) COMMENT 'Username who performed action',
  Action VARCHAR(255) COMMENT 'Action type: create_*, update_*, delete_*',
  Timestamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'When action occurred',
  OldData LONGTEXT COMMENT 'Previous values (JSON)',
  NewData LONGTEXT COMMENT 'New values (JSON)',
  IP VARCHAR(45) COMMENT 'Client IP address (IPv4/IPv6)',
  
  INDEX idx_user (User) COMMENT 'Find actions by user',
  INDEX idx_action (Action) COMMENT 'Find actions by type',
  INDEX idx_timestamp (Timestamp) COMMENT 'Find actions by date/time',
  INDEX idx_combined (User, Action, Timestamp) COMMENT 'Combined search query'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Complete audit log of all system operations';

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================================
-- SUMMARY
-- ============================================================================
-- Total Tables Created: 10
-- Total Foreign Keys: 14
-- Total Indexes: 40+
-- Storage Engine: InnoDB (transactions, FK support)
-- Character Set: utf8mb4 (supports emoji, full Unicode)
-- Collation: utf8mb4_unicode_ci (case-insensitive, Unicode-aware)
--
-- Data Flow:
-- 1. Create Customer (qlbh_khachhang)
-- 2. Register Vehicle (qlbh_xe) → link to Customer
-- 3. Create Contract (qlbh_hopdong) → link Customer+Vehicle
-- 4. Submit Claim (qlbh_yeucau) → link to Contract
-- 5. Assess Claim (qlbh_thamdinh) → evaluate
-- 6. Approve Claim (qlbh_pheduyet) → decision
-- 7. Process Payout (qlbh_phieuchi) → pay customer
-- 8. Track Receipts (qlbh_phieuthu) → record premiums
-- 9. Manage Users (qlbh_taikhoan) → role-based access
-- 10. Audit Trail (qlbh_lichsu) → compliance log
--
-- -- 0B complete

