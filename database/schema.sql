-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(20) UNIQUE NOT NULL,
    barcode VARCHAR(50) UNIQUE,
    role ENUM('admin', 'validator', 'participant') NOT NULL DEFAULT 'participant',
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Meal records table
CREATE TABLE IF NOT EXISTS meal_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    meal_type ENUM('morning', 'evening') NOT NULL,
    validator_id INT NOT NULL,
    served_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (validator_id) REFERENCES users(id),
    INDEX idx_user_meal_date (user_id, meal_type, served_at),
    INDEX idx_validator_date (validator_id, served_at)
) ENGINE=InnoDB;

-- Create indexes for performance
CREATE INDEX IF NOT EXISTS idx_user_barcode ON users(barcode);
CREATE INDEX IF NOT EXISTS idx_user_phone ON users(phone);
CREATE INDEX IF NOT EXISTS idx_meal_served_at ON meal_records(served_at);

-- Insert default admin user
INSERT INTO users (name, email, phone, role, password_hash) 
VALUES ('Admin', 'admin@summit.com', '1234567890', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi')
ON DUPLICATE KEY UPDATE id=id;

-- Insert sample validation team members
INSERT INTO users (name, email, phone, role, password_hash) 
VALUES 
    ('Validator 1', 'validator1@summit.com', '1111111111', 'validator', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
    ('Validator 2', 'validator2@summit.com', '2222222222', 'validator', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
    ('Validator 3', 'validator3@summit.com', '3333333333', 'validator', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi')
ON DUPLICATE KEY UPDATE id=id;
