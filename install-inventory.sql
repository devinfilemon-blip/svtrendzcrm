-- Inventory module: products, inward (stock-in), outward (stock-out) — run once if tables are missing

CREATE TABLE IF NOT EXISTS tblinv_product (
  iProductid INT AUTO_INCREMENT PRIMARY KEY,
  sProductCode VARCHAR(50) NULL,
  sProductName VARCHAR(200) NOT NULL,
  sCategory VARCHAR(100) NULL,
  sHsnCode VARCHAR(20) NULL,
  sUnit VARCHAR(30) NOT NULL DEFAULT 'Nos',
  fPurchaseRate DECIMAL(12,2) NOT NULL DEFAULT 0,
  fSaleRate DECIMAL(12,2) NOT NULL DEFAULT 0,
  iOpeningStock INT NOT NULL DEFAULT 0,
  iReorderLevel INT NOT NULL DEFAULT 0,
  sStatus VARCHAR(20) NOT NULL DEFAULT 'Active',
  iCreatedBy INT NULL,
  sCreatedTimeStamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  sModifiedTimestamp DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_name (sProductName),
  INDEX idx_code (sProductCode),
  INDEX idx_category (sCategory),
  INDEX idx_status (sStatus)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS tblinv_inward (
  iInwardid INT AUTO_INCREMENT PRIMARY KEY,
  iProductid INT NOT NULL,
  dDate DATE NOT NULL,
  iQty INT NOT NULL DEFAULT 0,
  fRate DECIMAL(12,2) NOT NULL DEFAULT 0,
  fAmount DECIMAL(12,2) NOT NULL DEFAULT 0,
  sSupplier VARCHAR(150) NULL,
  sInvoiceNo VARCHAR(100) NULL,
  sRemarks VARCHAR(255) NULL,
  iCreatedBy INT NULL,
  sCreatedTimeStamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  sModifiedTimestamp DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_product (iProductid),
  INDEX idx_date (dDate)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS tblinv_outward (
  iOutwardid INT AUTO_INCREMENT PRIMARY KEY,
  iProductid INT NOT NULL,
  dDate DATE NOT NULL,
  iQty INT NOT NULL DEFAULT 0,
  sIssuedTo VARCHAR(150) NULL,
  sPurpose VARCHAR(50) NOT NULL DEFAULT 'Sale',
  sRemarks VARCHAR(255) NULL,
  iCreatedBy INT NULL,
  sCreatedTimeStamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  sModifiedTimestamp DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_product (iProductid),
  INDEX idx_date (dDate)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
