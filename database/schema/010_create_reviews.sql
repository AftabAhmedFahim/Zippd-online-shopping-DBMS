-- reviews 
CREATE TABLE reviews (
    review_id INT IDENTITY(1,1) PRIMARY KEY,
    user_id INT NOT NULL FOREIGN KEY REFERENCES users(user_id),
    product_id INT NOT NULL FOREIGN KEY REFERENCES products(product_id),
    rating TINYINT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    review_text VARCHAR(MAX) NULL,
    review_date DATETIME NOT NULL DEFAULT GETDATE(),
    created_at DATETIME NOT NULL DEFAULT SYSDATETIME(),
    updated_at DATETIME NULL,
    UNIQUE (user_id, product_id)
);
