# Database Schema (ERD) — Tables & Relationships

This document describes the database tables for the e-commerce system and how they relate to each other.

---

## Tables Overview

### 1) `users`
Stores customer accounts.

**Main columns**
- `user_id` (PK)
- `full_name`
- `email` (UNIQUE)
- `password_hash`
- `phone`
- `gender`
- `address`

**Used by**
- `orders.user_id` (who placed an order)
- `reviews.user_id` (who wrote a review)
- `returns.user_id` (who requested a return)

---

### 2) `admins`
Stores admin accounts.

**Main columns**
- `admin_id` (PK)
- `full_name`
- `email` (UNIQUE)
- `phone`
- `password_hash`
- `status`

---

### 3) `products`
Stores products available for purchase.

**Main columns**
- `product_id` (PK)
- `product_name`
- `description`
- `stock_qty`
- `price`

**Used by**
- `order_items.product_id` (product in an order)
- `reviews.product_id` (reviews for this product)
- `product_categories.product_id` (link to categories)

---

### 4) `categories`
Stores product categories (e.g., Electronics, Books).

**Main columns**
- `category_id` (PK)
- `category_name` (UNIQUE)
- `description`

**Used by**
- `product_categories.category_id` (link to products)

---

### 5) `product_categories`
Join table for the many-to-many relationship between products and categories.

**Main columns**
- `product_id` (PK, FK → `products.product_id`)
- `category_id` (PK, FK → `categories.category_id`)

**Why it exists**
- One product can belong to many categories
- One category can contain many products

---

### 6) `orders`
Stores an order placed by a user.

**Main columns**
- `order_id` (PK)
- `user_id` (FK → `users.user_id`)
- `order_date`
- `order_status`
- `shipping_address`
- `total_amount`
- `is_paid`

**Used by**
- `order_items.order_id` (items inside the order)
- `payments.order_id` (payment for the order)
- `shipping.order_id` (shipping info for the order)

---

### 7) `order_items`
Stores individual products inside an order.

**Main columns**
- `order_id` (PK, FK → `orders.order_id`)
- `product_id` (PK, FK → `products.product_id`)
- `quantity`
- `unit_price`
- `line_total` (usually derived: `quantity * unit_price`)

**Why composite PK**
- `(order_id, product_id)` uniquely identifies each product line in an order.

**Used by**
- `returns (order_id, product_id)` (return is tied to a specific order line)

---

### 8) `payments`
Stores payment info for an order.

**Main columns**
- `payment_id` (PK)
- `order_id` (FK → `orders.order_id`, UNIQUE)
- `amount`
- `payment_date`
- `payment_method`
- `payment_status`

**Important rule**
- **One order has exactly one payment record** (1–1), enforced by `UNIQUE(order_id)`.

---

### 9) `shipping`
Stores shipping/tracking info for an order.

**Main columns**
- `shipping_id` (PK)
- `order_id` (FK → `orders.order_id`, UNIQUE)
- `courier_name`
- `tracking_number` (UNIQUE)
- `shipped_date`
- `delivered_date`
- `shipping_status`

**Important rule**
- **One order has exactly one shipping record** (1–1), enforced by `UNIQUE(order_id)`.

---

### 10) `reviews`
Stores product reviews written by users.

**Main columns**
- `review_id` (PK)
- `user_id` (FK → `users.user_id`)
- `product_id` (FK → `products.product_id`)
- `rating` (1–5)
- `review_text`
- `review_date`

**Common rule (optional)**
- You may enforce “one review per user per product” using `UNIQUE(user_id, product_id)` if required.

---

### 11) `returns`
Stores return requests made by users for items in an order.

**Main columns**
- `return_id` (PK)
- `order_id`
- `product_id`
- `user_id`
- `return_reason`
- `return_date`
- `status`

**Keys**
- `(order_id, product_id)` is UNIQUE so the same order item cannot be returned twice.
- `(order_id, product_id)` is an FK → `order_items(order_id, product_id)` ensuring the returned product actually exists in that order.

---

## Relationships (Cardinality)

### Users → Orders
- **1 user** can place **many orders**
- `orders.user_id` → `users.user_id`

### Orders → OrderItems
- **1 order** contains **many order items**
- `order_items.order_id` → `orders.order_id`

### Products → OrderItems
- **1 product** can appear in **many order items**
- `order_items.product_id` → `products.product_id`

### Orders → Payments
- **1 order** has **1 payment** (1–1)
- `payments.order_id` → `orders.order_id` and `UNIQUE(payments.order_id)`

### Orders → Shipping
- **1 order** has **1 shipping record** (1–1)
- `shipping.order_id` → `orders.order_id` and `UNIQUE(shipping.order_id)`

### Users → Reviews
- **1 user** can write **many reviews**
- `reviews.user_id` → `users.user_id`

### Products → Reviews
- **1 product** can have **many reviews**
- `reviews.product_id` → `products.product_id`

### Products ↔ Categories (Many-to-Many)
- Implemented via `product_categories`
- `product_categories.product_id` → `products.product_id`
- `product_categories.category_id` → `categories.category_id`

### OrderItems → Returns
- **1 order item** can have **0 or 1 return request**
- `returns(order_id, product_id)` → `order_items(order_id, product_id)`
- `UNIQUE(returns.order_id, returns.product_id)` prevents duplicate returns for same order line

---

## Quick ERD Summary

- Users place Orders
- Orders contain OrderItems
- Each Order has one Payment and one Shipping record
- Products belong to Categories (M–M)
- Users write Reviews on Products
- Returns are tied to a specific OrderItem

---
