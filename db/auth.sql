-- Update users table to include role
ALTER TABLE users
ADD COLUMN role ENUM('admin', 'user') DEFAULT 'user' AFTER email,
ADD COLUMN reset_token VARCHAR(255) NULL,
ADD COLUMN reset_token_expires DATETIME NULL;

-- Create remember_me tokens table
CREATE TABLE user_tokens (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Add indexes
CREATE INDEX idx_user_tokens ON user_tokens(token);
CREATE INDEX idx_user_email ON users(email); 