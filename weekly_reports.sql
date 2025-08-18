-- Weekly Reports table үүсгэх
CREATE TABLE IF NOT EXISTS weekly_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    week_start_date DATE NOT NULL,
    week_end_date DATE NOT NULL,
    monday_work TEXT,
    tuesday_work TEXT,
    wednesday_work TEXT,
    thursday_work TEXT,
    friday_work TEXT,
    saturday_work TEXT,
    sunday_work TEXT,
    summary TEXT,
    status ENUM('draft', 'submitted', 'reviewed') DEFAULT 'draft',
    submitted_at TIMESTAMP NULL,
    reviewed_by INT NULL,
    reviewed_at TIMESTAMP NULL,
    admin_feedback TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (reviewed_by) REFERENCES users(id)
);
