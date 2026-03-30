-- shipping 
CREATE TABLE shipping (
    shipping_id INT IDENTITY(1,1) PRIMARY KEY,
    order_id INT UNIQUE FOREIGN KEY REFERENCES orders(order_id) ON DELETE CASCADE,
    courier_name VARCHAR(100) NOT NULL,
    tracking_number VARCHAR(100) UNIQUE NOT NULL,
    shipped_date DATETIME NULL,
    delivered_date DATETIME NULL,
    shipping_status VARCHAR(30) NOT NULL DEFAULT 'pending',
    created_at DATETIME NOT NULL DEFAULT SYSDATETIME(),
    updated_at DATETIME NULL
);
