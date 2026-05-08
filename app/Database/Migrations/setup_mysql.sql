-- SQL Migration for M-Pesa Analyzer
-- 1. Create indices for performance
ALTER TABLE tbl_Sms ADD INDEX idx_sms_time (sms_time);
ALTER TABLE tbl_Sms ADD INDEX idx_sms_category (sms_category);
ALTER TABLE tbl_Sms ADD INDEX idx_sms_number (sms_number);
ALTER TABLE tbl_Sms ADD INDEX idx_sms_owner (sms_owner);

-- 2. Create table for Analyzed outputs
CREATE TABLE IF NOT EXISTS tbl_Analyzed_Transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    orig_sms_id VARCHAR(50),
    trans_id VARCHAR(20),
    amount DECIMAL(10,2),
    counterparty VARCHAR(255),
    description TEXT,
    trans_date DATETIME,
    created_at DATETIME,
    INDEX idx_orig_sms (orig_sms_id),
    INDEX idx_trans_date (trans_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
