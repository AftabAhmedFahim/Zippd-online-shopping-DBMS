-- Reference SQL script; 

CREATE TABLE users (
    user_id INT IDENTITY(1,1) PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NULL,
    gender VARCHAR(20) NULL,
    address VARCHAR(MAX) NULL,
    created_at DATETIME NOT NULL DEFAULT SYSDATETIME(),
    updated_at DATETIME NULL
);

CREATE TABLE admins (
    admin_id INT IDENTITY(1,1) PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(20) NULL,
    password_hash VARCHAR(255) NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'active',
    created_at DATETIME NOT NULL DEFAULT SYSDATETIME(),
    updated_at DATETIME NULL
);

CREATE TABLE categories (
    category_id INT IDENTITY(1,1) PRIMARY KEY,
    category_name VARCHAR(255) UNIQUE NOT NULL,
    description VARCHAR(MAX) NULL,
    created_at DATETIME NOT NULL DEFAULT SYSDATETIME(),
    updated_at DATETIME NULL
);

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

CREATE TABLE order_items (
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL CHECK (quantity > 0),
    unit_price DECIMAL(12,2) NOT NULL CHECK (unit_price >= 0),
    line_total DECIMAL(12,2) NOT NULL CHECK (line_total >= 0),
    PRIMARY KEY (order_id, product_id),
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id)
);

CREATE TABLE product_categories (
    product_id INT NOT NULL,
    category_id INT NOT NULL,
    PRIMARY KEY (product_id, category_id),
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE CASCADE
);

CREATE TABLE payments (
    payment_id INT IDENTITY(1,1) PRIMARY KEY,
    order_id INT UNIQUE FOREIGN KEY REFERENCES orders(order_id) ON DELETE CASCADE,
    amount DECIMAL(12,2) NOT NULL CHECK (amount >= 0),
    payment_date DATETIME NULL,
    payment_method VARCHAR(30) NOT NULL,
    payment_status VARCHAR(30) NOT NULL DEFAULT 'pending',
    created_at DATETIME NOT NULL DEFAULT SYSDATETIME(),
    updated_at DATETIME NULL
);

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
