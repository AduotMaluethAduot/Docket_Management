-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS webtech_fall2024_aduot_jok;
USE webtech_fall2024_aduot_jok;

-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default admin user (password: admin123)

-- Lawyers table
CREATE TABLE lawyers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20) NOT NULL,
    specialization ENUM('civil', 'criminal', 'corporate', 'family', 'tax', 'other') NOT NULL,
    bar_number VARCHAR(50) UNIQUE NOT NULL,
    years_experience INT NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Clients table
CREATE TABLE clients (
    id INT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20) NOT NULL,
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Cases table
CREATE TABLE IF NOT EXISTS cases (
    id INT PRIMARY KEY AUTO_INCREMENT,
    case_number VARCHAR(20) UNIQUE NOT NULL,
    case_title VARCHAR(255) NOT NULL,
    client_id INT NOT NULL,
    lawyer_id INT NOT NULL,
    case_type ENUM('civil', 'criminal', 'corporate', 'family', 'other') NOT NULL,
    case_status ENUM('pending', 'active', 'closed') DEFAULT 'pending',
    description TEXT,
    filing_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id),
    FOREIGN KEY (lawyer_id) REFERENCES lawyers(id)
);

-- Documents table
CREATE TABLE documents (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    case_id INT,
    document_type ENUM('contract', 'evidence', 'court_order', 'correspondence', 'other') NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_size INT NOT NULL,
    file_type VARCHAR(50) NOT NULL,
    notes TEXT,
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    uploaded_by INT NOT NULL,
    FOREIGN KEY (case_id) REFERENCES cases(id),
    FOREIGN KEY (uploaded_by) REFERENCES users(id)
);

-- Case History table for tracking case updates
CREATE TABLE IF NOT EXISTS case_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    case_id INT NOT NULL,
    action VARCHAR(255) NOT NULL,
    description TEXT,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (case_id) REFERENCES cases(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Case Hearings table
CREATE TABLE IF NOT EXISTS case_hearings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    hearing_date DATETIME NOT NULL,
    hearing_type VARCHAR(100) NOT NULL,
    location VARCHAR(255),
    notes TEXT,
    status ENUM('scheduled', 'completed', 'postponed', 'cancelled') DEFAULT 'scheduled',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert sample lawyers
INSERT INTO lawyers (name, email, phone, specialization, bar_number, years_experience, status, notes) VALUES
('John Smith', 'john.smith@example.com', '(555) 123-4567', 'civil', 'BAR123NY', 15, 'active', 'Specializes in civil litigation'),
('Sarah Johnson', 'sarah.j@example.com', '(555) 234-5678', 'criminal', 'BAR456NY', 8, 'active', 'Criminal defense expert'),
('Michael Brown', 'mbrown@example.com', '(555) 345-6789', 'corporate', 'BAR789NY', 12, 'active', 'Corporate law specialist');

-- Insert sample clients
INSERT INTO clients (first_name, last_name, email, phone, address) VALUES
('James', 'Wilson', 'jwilson@example.com', '(555) 111-2222', '123 Main St, New York, NY'),
('Emily', 'Davis', 'emily.d@example.com', '(555) 222-3333', '456 Park Ave, New York, NY'),
('Robert', 'Miller', 'rmiller@example.com', '(555) 333-4444', '789 Broadway, New York, NY');

-- Insert sample cases
INSERT INTO cases (case_number, case_title, client_id, lawyer_id, case_type, case_status, description, filing_date) VALUES
('2024001', 'Wilson vs ABC Corp', 1, 1, 'civil', 'active', 'Employment discrimination case', '2024-01-15'),
('2024002', 'State vs Davis', 2, 2, 'criminal', 'pending', 'Criminal defense case', '2024-01-20'),
('2024003', 'Miller Contract Dispute', 3, 3, 'corporate', 'active', 'Business contract dispute', '2024-01-25');

-- Insert sample case history
INSERT INTO case_history (case_id, action, description, created_by) VALUES
(1, 'Case Filed', 'Initial case filing completed', 1),
(2, 'Document Added', 'Added arrest report to case file', 1),
(3, 'Status Update', 'Case moved to active status', 1);

-- Verify tables exist
SELECT 
    TABLE_NAME 
FROM 
    information_schema.TABLES 
WHERE 
    TABLE_SCHEMA = 'webtech_fall2024_aduot_jok' 
    AND TABLE_NAME IN ('cases', 'case_history', 'lawyers', 'clients');

-- Check if user has proper permissions
GRANT ALL PRIVILEGES ON webtech_fall2024_aduot_jok.* TO 'aduot.jok'@'localhost';
FLUSH PRIVILEGES;

-- Verify foreign key constraints
ALTER TABLE cases
    ADD CONSTRAINT fk_case_client
    FOREIGN KEY (client_id) REFERENCES clients(id),
    ADD CONSTRAINT fk_case_lawyer
    FOREIGN KEY (lawyer_id) REFERENCES lawyers(id);

ALTER TABLE case_history
    ADD CONSTRAINT fk_history_case
    FOREIGN KEY (case_id) REFERENCES cases(id),
    ADD CONSTRAINT fk_history_user
    FOREIGN KEY (created_by) REFERENCES users(id);