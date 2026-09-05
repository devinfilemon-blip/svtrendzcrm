-- Client Management — software buyers + monthly billing / reports (run once if missing)

CREATE TABLE IF NOT EXISTS tblsoftware_client (
  iClientid INT AUTO_INCREMENT PRIMARY KEY,
  sClientname VARCHAR(255) NOT NULL,
  sCompanyname VARCHAR(255) NULL,
  sContactperson VARCHAR(255) NULL,
  sEmail VARCHAR(150) NULL,
  sPhone VARCHAR(50) NULL,
  sPlan VARCHAR(100) NULL,
  dMonthlyamount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  iBillingday INT NOT NULL DEFAULT 1,
  sStartdate DATE NULL,
  sStatus VARCHAR(20) NOT NULL DEFAULT 'Active',
  sAddress TEXT NULL,
  sNotes TEXT NULL,
  iUserid INT NULL,
  sCreatedTimeStamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  sModifiedTimestamp DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_status (sStatus),
  INDEX idx_name (sClientname)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS tblclient_monthly (
  iMonthlyid INT AUTO_INCREMENT PRIMARY KEY,
  iClientid INT NOT NULL,
  sClientname VARCHAR(255) NOT NULL,
  sMonth VARCHAR(7) NOT NULL,
  sDuedate DATE NOT NULL,
  dAmount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  dReceived DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  dPending DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  sStatus VARCHAR(20) NOT NULL DEFAULT 'Pending',
  sPaiddate DATE NULL,
  sMode VARCHAR(50) NULL,
  sInvoiceNo VARCHAR(100) NULL,
  sReport TEXT NULL,
  sNotes TEXT NULL,
  iUserid INT NULL,
  sCreatedTimeStamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  sModifiedTimestamp DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_client_month (iClientid, sMonth),
  INDEX idx_client (iClientid),
  INDEX idx_month (sMonth),
  INDEX idx_due (sDuedate),
  INDEX idx_status (sStatus)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
