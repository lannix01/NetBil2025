CREATE TABLE transactions (
    checkout_id VARCHAR(50) PRIMARY KEY,
    phone VARCHAR(12),
    amount DECIMAL(10,2),
    mac VARCHAR(17),
    package VARCHAR(50),
    status ENUM('pending', 'paid', 'failed'),
    username VARCHAR(50),
    password VARCHAR(50),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    checkout_id VARCHAR(50) UNIQUE,
    phone VARCHAR(15),
    amount DECIMAL(10,2),
    mac VARCHAR(17),
    package VARCHAR(50),
    status ENUM('pending', 'paid', 'failed'),
    username VARCHAR(50),
    password VARCHAR(50),
    mpesa_receipt VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);