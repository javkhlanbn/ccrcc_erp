-- Add permission columns to users table
ALTER TABLE users
ADD COLUMN can_view_all_time TINYINT(1) NOT NULL DEFAULT 0,
ADD COLUMN can_download_reports TINYINT(1) NOT NULL DEFAULT 0;

-- Update existing admin users to have all permissions by default
UPDATE users SET can_view_all_time = 1, can_download_reports = 1 WHERE role = 'admin';
