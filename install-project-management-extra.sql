-- Run once on live DB if Project Management (team/notifications/expenses/visits) shows errors.
-- (Tables also self-heal at runtime via project-api.php, but running this explicitly is faster.)

CREATE TABLE IF NOT EXISTS tblproject_notifications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  iUserid INT NOT NULL,
  lead_id INT NOT NULL,
  sType VARCHAR(50) NOT NULL,
  sMessage TEXT NOT NULL,
  iIsRead TINYINT(1) NOT NULL DEFAULT 0,
  dCreatedAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  iCreatedBy INT NULL,
  INDEX idx_user (iUserid),
  INDEX idx_lead (lead_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS tblproject_expenses (
  id INT AUTO_INCREMENT PRIMARY KEY,
  lead_id INT NOT NULL,
  iUserid INT NOT NULL,
  sCategory VARCHAR(30) NOT NULL,
  dAmount DECIMAL(10,2) NOT NULL,
  sExpenseDate DATE NOT NULL,
  sDescription TEXT NULL,
  sReceipt VARCHAR(255) NULL,
  sReceiptName VARCHAR(255) NULL,
  sStatus VARCHAR(20) NOT NULL DEFAULT 'Pending',
  iReviewedBy INT NULL,
  sReviewNote TEXT NULL,
  dCreatedAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  dUpdatedAt DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_lead (lead_id),
  INDEX idx_user (iUserid),
  INDEX idx_status (sStatus)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS tblproject_visits (
  id INT AUTO_INCREMENT PRIMARY KEY,
  lead_id INT NOT NULL,
  iUserid INT NOT NULL,
  sVisitType VARCHAR(20) NOT NULL,
  sVisitDate DATE NOT NULL,
  sLocation VARCHAR(255) NULL,
  sNotes TEXT NULL,
  dCreatedAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_lead (lead_id),
  INDEX idx_user (iUserid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS tblproject_visit_photos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  visit_id INT NOT NULL,
  sPhoto VARCHAR(255) NOT NULL,
  sPhotoName VARCHAR(255) NULL,
  dCreatedAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_visit (visit_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
