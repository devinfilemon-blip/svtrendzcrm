-- Re-engage module
CREATE TABLE IF NOT EXISTS tblreengage (
  iReengageid INT AUTO_INCREMENT PRIMARY KEY,
  iUserid INT NOT NULL,
  iLeadid INT NOT NULL DEFAULT 0,
  sCompanyname VARCHAR(255) NOT NULL DEFAULT '',
  sDescription TEXT NULL,
  sDate DATE NOT NULL,
  iCreatedBy INT NULL,
  sCreatedTimeStamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  sModifiedTimestamp DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_user (iUserid),
  INDEX idx_lead (iLeadid),
  INDEX idx_date (sDate)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
