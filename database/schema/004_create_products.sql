-- products
CREATE TABLE products (
    product_id INT IDENTITY(1,1) PRIMARY KEY,
    admin_id INT NOT NULL FOREIGN KEY REFERENCES admins(admin_id),
    product_name VARCHAR(255) NOT NULL,
    description VARCHAR(MAX) NULL,
    stock_qty INT NOT NULL DEFAULT 0,
    price DECIMAL(12,2) NOT NULL CHECK (price >= 0),
    created_at DATETIME NOT NULL DEFAULT SYSDATETIME(),
    updated_at DATETIME NULL
);
