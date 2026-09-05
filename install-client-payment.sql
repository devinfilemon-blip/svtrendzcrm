-- Sales Management — client payment tracking (run once if missing)
CREATE TABLE IF NOT EXISTS tblclient_payment (
  iPaymentid INT AUTO_INCREMENT PRIMARY KEY,
  iCustomerid INT NULL,
  leadId INT NOT NULL DEFAULT 0,
  iQuotationid INT NULL,
  sClientname VARCHAR(255) NOT NULL,
  sInvoiceNo VARCHAR(100) NULL,
  sPaymentdate DATE NOT NULL,
  dAmount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  dReceived DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  dPending DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  sStatus VARCHAR(20) NOT NULL DEFAULT 'Pending',
  sMode VARCHAR(50) NULL,
  sReference VARCHAR(100) NULL,
  sNotes TEXT NULL,
  iUserid INT NULL,
  sCreatedTimeStamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  sModifiedTimestamp DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_customer (iCustomerid),
  INDEX idx_lead (leadId),
  INDEX idx_status (sStatus),
  INDEX idx_date (sPaymentdate)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
