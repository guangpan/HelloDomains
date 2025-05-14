CREATE TABLE domains (
    id INT AUTO_INCREMENT PRIMARY KEY,
    domain_name VARCHAR(255) NOT NULL,
    price DECIMAL(10,2),
    show_price BOOLEAN DEFAULT false,
    notes TEXT,
    show_notes BOOLEAN DEFAULT false,
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    contact_info TEXT,
    wechat VARCHAR(255),
    wechat_qr VARCHAR(255),
    qq VARCHAR(255),
    qq_qr VARCHAR(255),
    telegram VARCHAR(255),
    email VARCHAR(255),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 初始化设置表
INSERT INTO settings (contact_info) VALUES (''); 