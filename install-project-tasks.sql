-- Run once on live DB if Project Management shows errors
CREATE TABLE IF NOT EXISTS tblproject_tasks (
  id INT AUTO_INCREMENT PRIMARY KEY,
  lead_id INT NOT NULL,
  sTitle VARCHAR(255) NOT NULL,
  sDescription TEXT NULL,
  sAssigned_to INT NOT NULL,
  sCreated_by INT NOT NULL,
  sStatus VARCHAR(50) NOT NULL DEFAULT 'Pending',
  sDue_date DATE NULL,
  sCreated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  sUpdated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_lead (lead_id),
  INDEX idx_assigned (sAssigned_to),
  INDEX idx_status (sStatus)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
