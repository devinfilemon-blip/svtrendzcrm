-- Location module — run once if table is missing
--
-- If you don't have a database selected yet for this project, create one first
-- (only run this once, and only if the database doesn't already exist):
-- CREATE DATABASE IF NOT EXISTS svtrendz_infilemon CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
-- USE svtrendz_infilemon;

CREATE TABLE IF NOT EXISTS tbllocation (
  iLocationid INT AUTO_INCREMENT PRIMARY KEY,
  iUserid INT NOT NULL,
  dLatitude DECIMAL(10,7) NOT NULL,
  dLongitude DECIMAL(10,7) NOT NULL,
  dEndLatitude DECIMAL(10,7) NULL,
  dEndLongitude DECIMAL(10,7) NULL,
  sDateTime DATETIME NOT NULL,
  sEndDateTime DATETIME NULL,
  sCreatedTimeStamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_user (iUserid),
  INDEX idx_datetime (sDateTime)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Migration for existing installs (safe to re-run; errors if columns already exist):
-- ALTER TABLE tbllocation
--   ADD COLUMN dEndLatitude DECIMAL(10,7) NULL AFTER dLongitude,
--   ADD COLUMN dEndLongitude DECIMAL(10,7) NULL AFTER dEndLatitude,
--   ADD COLUMN sEndDateTime DATETIME NULL AFTER sDateTime;
