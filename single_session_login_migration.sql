-- ======================================
-- Single Session Login Feature
-- Database Migration SQL
-- ======================================

-- Add current_session_token column to tblstaff table
ALTER TABLE `tblstaff` 
ADD `current_session_token` VARCHAR(128) NULL DEFAULT NULL 
AFTER `last_login`;

-- Add current_session_token column to tblcontacts table
ALTER TABLE `tblcontacts` 
ADD `current_session_token` VARCHAR(128) NULL DEFAULT NULL 
AFTER `last_login`;

-- ======================================
-- Migration Complete!
-- ======================================