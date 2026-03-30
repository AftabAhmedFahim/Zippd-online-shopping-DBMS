-- returns
CREATE TABLE returns (
    return_id INT IDENTITY(1,1) PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    user_id INT NOT NULL FOREIGN KEY REFERENCES users(user_id),
    return_reason VARCHAR(MAX) NOT NULL,
    return_date DATETIME NOT NULL DEFAULT GETDATE(),
    status VARCHAR(30) NOT NULL DEFAULT 'pending',
    created_at DATETIME NOT NULL DEFAULT SYSDATETIME(),
    updated_at DATETIME NULL,
    UNIQUE (order_id, product_id),
    FOREIGN KEY (order_id, product_id) REFERENCES order_items(order_id, product_id)
);
