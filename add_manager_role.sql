-- Add manager role support to the database
-- Update existing users table to support manager role
-- Note: The role column should already exist, we just need to ensure manager role is supported

-- Update any existing users with manager role to have proper permissions
UPDATE users SET can_view_all_time = 1, can_download_reports = 1 WHERE role = 'manager';

-- Optional: If you want to convert some existing employees to managers, uncomment and modify:
-- UPDATE users SET role = 'manager', can_view_all_time = 1, can_download_reports = 1 WHERE id = [USER_ID];
