/* ADMINS */
SET IDENTITY_INSERT admins ON;

INSERT INTO admins (admin_id, full_name, email, phone, password_hash, status)
VALUES
(1,'Admin One','admin1@pcstore.com','01700000001','hash_admin_1','active'),
(2,'Admin Two','admin2@pcstore.com','01700000002','hash_admin_2','active');

SET IDENTITY_INSERT admins OFF;


/* USERS */
SET IDENTITY_INSERT users ON;

INSERT INTO users (user_id, full_name, email, password_hash, phone, gender, address)
VALUES
(1,'Saiman Ullah','saiman@gmail.com','hash_user_1','01712345671','Male','Kaliganj, Bangladesh'),
(2,'Aftab Ahmed Fahim','aftab@gmail.com','hash_user_2','01712345672','Male','Dhaka, Bangladesh'),
(3,'Rubaiat Ar Rabib','rabib@gmail.com','hash_user_3','01712345673','Male','Chittagong, Bangladesh'),
(4,'Sheikh Riaz Uddin','riaz@gmail.com','hash_user_4','01712345674','Male','Khulna, Bangladesh'),
(5,'Tanvir Ahmed','tanvir@gmail.com','hash_user_5','01810000005','Male','Khulna, Bangladesh');

SET IDENTITY_INSERT users OFF;


/* CATEGORIES */
SET IDENTITY_INSERT categories ON;

INSERT INTO categories (category_id, category_name, description)
VALUES
(1,'Laptop','Laptops for work and gaming'),
(2,'CPU','Desktop processors'),
(3,'GPU','Graphics cards'),
(4,'RAM','Memory modules'),
(5,'SSD','Solid State Drives'),
(6,'Motherboard','Motherboards for PC builds'),
(7,'PSU','Power supply units');

SET IDENTITY_INSERT categories OFF;


/* PRODUCTS */
SET IDENTITY_INSERT products ON;

INSERT INTO products (product_id, product_name, description, stock_qty, price)
VALUES
(1,'ASUS TUF Gaming F15','Gaming laptop i5 RTX3050 16GB 512GB',8,109999),
(2,'Lenovo IdeaPad 3','Ryzen 5 laptop 8GB RAM 512GB SSD',12,64999),
(3,'Intel Core i5-12400F','6 core 12 thread processor',20,18999),
(4,'AMD Ryzen 5 5600','6 core gaming CPU',18,16999),
(5,'NVIDIA RTX 4060','8GB gaming graphics card',10,46999),
(6,'NVIDIA RTX 3060','12GB graphics card',9,38999),
(7,'Corsair Vengeance 16GB DDR4','16GB 3200MHz RAM kit',30,5499),
(8,'G.Skill Ripjaws 32GB DDR4','32GB RAM kit',16,9999),
(9,'Samsung 970 EVO Plus 1TB','NVMe SSD',14,10999),
(10,'WD Blue 500GB NVMe','Budget SSD',25,5499),
(11,'MSI B550M PRO VDH WIFI','AMD motherboard',11,13999),
(12,'Gigabyte B660M DS3H','Intel motherboard',9,14999),
(13,'Corsair CV650','650W 80+ Bronze PSU',22,6499);

SET IDENTITY_INSERT products OFF;


/* PRODUCT CATEGORIES */
INSERT INTO product_categories (product_id, category_id)
VALUES
(1,1),(2,1),(3,2),(4,2),(5,3),(6,3),
(7,4),(8,4),(9,5),(10,5),(11,6),(12,6),(13,7);


/* ORDERS */
SET IDENTITY_INSERT orders ON;

INSERT INTO orders (order_id, user_id, order_status, shipping_address, total_amount, is_paid)
VALUES
(1,1,'shipped','Dhaka Mirpur Road 10',29997,1),
(2,2,'shipped','Chattogram GEC Circle',46999,1),
(3,4,'pending','Sylhet Zindabazar',64999,0);

SET IDENTITY_INSERT orders OFF;


/* ORDER ITEMS */
INSERT INTO order_items (order_id, product_id, quantity, unit_price, line_total)
VALUES
(1,3,1,18999,18999),
(1,7,1,5499,5499),
(1,10,1,5499,5499),
(2,5,1,46999,46999),
(3,2,1,64999,64999);


/* PAYMENTS */
SET IDENTITY_INSERT payments ON;

INSERT INTO payments (payment_id, order_id, amount, payment_date, payment_method, payment_status)
VALUES
(1,1,29997,GETDATE(),'card','paid'),
(2,2,46999,GETDATE(),'bkash','paid'),
(3,3,64999,NULL,'cash_on_delivery','pending');

SET IDENTITY_INSERT payments OFF;


/* SHIPPING */
SET IDENTITY_INSERT shipping ON;

INSERT INTO shipping (shipping_id, order_id, courier_name, tracking_number, shipped_date, shipping_status)
VALUES
(1,1,'Pathao Courier','TRK1001BD',GETDATE(),'delivered'),
(2,2,'SA Paribahan','TRK1002BD',GETDATE(),'in_transit'),
(3,3,'Steadfast','TRK1003BD',NULL,'pending');

SET IDENTITY_INSERT shipping OFF;


/* REVIEWS */
SET IDENTITY_INSERT reviews ON;

INSERT INTO reviews (review_id, user_id, product_id, rating, review_text)
VALUES
(1,1,3,5,'Excellent CPU performance'),
(2,2,5,5,'Great GPU for gaming'),
(3,4,2,4,'Good laptop for study');

SET IDENTITY_INSERT reviews OFF;


/* RETURNS */
SET IDENTITY_INSERT returns ON;

INSERT INTO returns (return_id, order_id, product_id, user_id, return_reason, status)
VALUES
(1,1,10,1,'Wanted larger SSD capacity','approved');

SET IDENTITY_INSERT returns OFF;