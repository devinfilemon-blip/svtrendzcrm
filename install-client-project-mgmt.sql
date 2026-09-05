-- Client Project Management: run once against dbinficrm_infilemon.
-- (sClientCompany also self-heals at runtime via crmEnsureClientProjectColumns()
--  in layouts/crm-access.php, but running this explicitly is faster.)

ALTER TABLE tbluser ADD COLUMN IF NOT EXISTS sClientCompany VARCHAR(255) NULL;

CREATE TABLE IF NOT EXISTS tblclient_project (
  iId INT AUTO_INCREMENT PRIMARY KEY,
  sProjectName VARCHAR(255) NOT NULL,
  sDescription TEXT NULL,
  iClientUserid INT NOT NULL,
  iCreatedBy INT NOT NULL,
  sStatus VARCHAR(30) NOT NULL DEFAULT 'Active',
  dStartDate DATE NULL,
  dDueDate DATE NULL,
  dCreatedAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  dUpdatedAt DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_client (iClientUserid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS tblclient_project_tasks (
  id INT AUTO_INCREMENT PRIMARY KEY,
  project_id INT NOT NULL,
  sTitle VARCHAR(255) NOT NULL,
  sDescription TEXT NULL,
  sAssigned_to INT NOT NULL,
  sCreated_by INT NOT NULL,
  sStatus VARCHAR(50) NOT NULL DEFAULT 'Pending',
  sDue_date DATE NULL,
  sCreated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  sUpdated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_project (project_id),
  INDEX idx_assigned (sAssigned_to)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS tblclient_project_activity (
  id INT AUTO_INCREMENT PRIMARY KEY,
  project_id INT NOT NULL,
  task_id INT NULL,
  sAction VARCHAR(50) NOT NULL,
  sDetail TEXT NOT NULL,
  iUserid INT NOT NULL,
  dCreatedAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_project (project_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
