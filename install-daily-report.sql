-- Daily Report module — run once if table is missing
CREATE TABLE IF NOT EXISTS tbldaily_report (
  iReportid INT AUTO_INCREMENT PRIMARY KEY,
  iUserid INT NOT NULL,
  sDate DATE NOT NULL,
  sTimeIn VARCHAR(20) NULL,
  sTimeOut VARCHAR(20) NULL,
  iTimeInChecked TINYINT(1) NOT NULL DEFAULT 0,
  iTimeOutChecked TINYINT(1) NOT NULL DEFAULT 0,
  sTasksPlanned TEXT NULL,
  sTasksCompleted TEXT NULL,
  sWorkDetails TEXT NULL,
  sPending TEXT NULL,
  sBlockers TEXT NULL,
  sTomorrowPlan TEXT NULL,
  sRemarks TEXT NULL,
  sCreatedTimeStamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  sModifiedTimestamp DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_user (iUserid),
  INDEX idx_date (sDate)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- For existing installs:
-- ALTER TABLE tbldaily_report ADD COLUMN iTimeInChecked TINYINT(1) NOT NULL DEFAULT 0 AFTER sTimeOut;
-- ALTER TABLE tbldaily_report ADD COLUMN iTimeOutChecked TINYINT(1) NOT NULL DEFAULT 0 AFTER iTimeInChecked;
