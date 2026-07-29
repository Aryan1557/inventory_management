-- Run this once in phpMyAdmin to create the email tables
-- Use your existing database: inventory_management

-- Create emails table (referencing your users table with user_id)
CREATE TABLE IF NOT EXISTS emails (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    recipient_email VARCHAR(190) NOT NULL,
    recipient_id INT NULL,               -- filled in if recipient_email matches a registered user
    subject VARCHAR(255) NOT NULL DEFAULT '(no subject)',
    body MEDIUMTEXT,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (recipient_id) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Create attachments table
CREATE TABLE IF NOT EXISTS attachments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email_id INT NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    stored_name VARCHAR(255) NOT NULL,   -- unique filename on disk, inside /uploads
    filesize INT NOT NULL,
    FOREIGN KEY (email_id) REFERENCES emails(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Helpful indexes for the inbox/sent/received queries
CREATE INDEX idx_emails_recipient ON emails(recipient_id, created_at);
CREATE INDEX idx_emails_sender ON emails(sender_id, created_at);