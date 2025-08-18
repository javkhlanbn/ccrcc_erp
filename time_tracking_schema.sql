-- Time Tracking System Database Schema

-- Time entries table - ажилчдын цагийн бүртгэл
CREATE TABLE time_entries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    date DATE NOT NULL,
    start_time TIME NULL,
    end_time TIME NULL,
    total_hours DECIMAL(5,2) DEFAULT 0.00,
    status ENUM('in_progress', 'completed', 'break') DEFAULT 'completed',
    break_time DECIMAL(5,2) DEFAULT 0.00,
    overtime_hours DECIMAL(5,2) DEFAULT 0.00,
    notes TEXT NULL,
    is_manual BOOLEAN DEFAULT FALSE,
    edited_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (edited_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_user_date (user_id, date)
);

-- Break entries table - завсарлага бүртгэх
CREATE TABLE break_entries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    time_entry_id INT NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NULL,
    duration DECIMAL(5,2) DEFAULT 0.00,
    break_type ENUM('lunch', 'short', 'meeting', 'other') DEFAULT 'short',
    notes VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (time_entry_id) REFERENCES time_entries(id) ON DELETE CASCADE
);

-- Work hours settings table - ажлын цагийн тохиргоо
CREATE TABLE work_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    daily_hours DECIMAL(4,2) DEFAULT 8.00,
    weekly_hours DECIMAL(4,2) DEFAULT 40.00,
    overtime_rate DECIMAL(4,2) DEFAULT 1.5,
    break_duration DECIMAL(4,2) DEFAULT 1.00,
    late_threshold INT DEFAULT 15, -- минутаар
    early_leave_threshold INT DEFAULT 15, -- минутаар
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default work settings
INSERT INTO work_settings (daily_hours, weekly_hours, overtime_rate, break_duration) 
VALUES (8.00, 40.00, 1.5, 1.00);

-- Add indexes for better performance
CREATE INDEX idx_time_entries_user_date ON time_entries(user_id, date);
CREATE INDEX idx_time_entries_date ON time_entries(date);
CREATE INDEX idx_break_entries_time_entry ON break_entries(time_entry_id);
