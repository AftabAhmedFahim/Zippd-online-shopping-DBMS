-- orders 
CREATE TABLE orders (
    order_id INT IDENTITY(1,1) PRIMARY KEY,
    user_id INT NOT NULL FOREIGN KEY REFERENCES users(user_id),
    order_date DATETIME NOT NULL DEFAULT GETDATE(),
    order_status VARCHAR(30) NOT NULL DEFAULT 'pending',
    shipping_address VARCHAR(MAX) NOT NULL,
    total_amount DECIMAL(12,2) NOT NULL CHECK (total_amount >= 0),
    is_paid BIT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT SYSDATETIME(),
    updated_at DATETIME NULL
);
