-- Profit & Loss account entries (run once if missing)
CREATE TABLE IF NOT EXISTS tblpnl_entry (
  iPnLid INT AUTO_INCREMENT PRIMARY KEY,
  sType VARCHAR(20) NOT NULL DEFAULT 'Income',
  sCategory VARCHAR(100) NOT NULL DEFAULT '',
  sParticulars VARCHAR(255) NOT NULL DEFAULT '',
  sEntrydate DATE NOT NULL,
  dAmount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  sReference VARCHAR(100) NULL,
  sNotes TEXT NULL,
  iUserid INT NULL,
  sCreatedTimeStamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  sModifiedTimestamp DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_type (sType),
  INDEX idx_category (sCategory),
  INDEX idx_date (sEntrydate)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
