CREATE DATABASE IF NOT EXISTS al_habib_pharmacy
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE al_habib_pharmacy;

DROP TABLE IF EXISTS contact_messages;
DROP TABLE IF EXISTS prescriptions;
DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS cart_items;
DROP TABLE IF EXISTS carts;
DROP TABLE IF EXISTS addresses;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS companies;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS admins;
DROP TABLE IF EXISTS customers;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(50) NOT NULL
);

CREATE TABLE customers (
    customer_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    phone VARCHAR(20),
    loyalty_points INT DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

CREATE TABLE admins (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    role VARCHAR(50) DEFAULT 'admin',
    FOREIGN KEY (user_id) REFERENCES users(user_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

CREATE TABLE categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
);

CREATE TABLE companies (
    company_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
);

CREATE TABLE products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    price DOUBLE NOT NULL,
    stock INT NOT NULL,
    category_id INT,
    company_id INT,
    generic_name VARCHAR(150) NULL,
    brand_name VARCHAR(150) NULL,
    manufacturer_name VARCHAR(150) NULL,
    product_ndc VARCHAR(50) NULL,
    dosage_form VARCHAR(100) NULL,
    route VARCHAR(100) NULL,
    image_url VARCHAR(255) NULL,
    source VARCHAR(50) DEFAULT 'local',
    is_active TINYINT(1) DEFAULT 1,
    FOREIGN KEY (category_id) REFERENCES categories(category_id)
        ON DELETE SET NULL
        ON UPDATE CASCADE,
    FOREIGN KEY (company_id) REFERENCES companies(company_id)
        ON DELETE SET NULL
        ON UPDATE CASCADE
);

CREATE TABLE addresses (
    address_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    city VARCHAR(100) NOT NULL,
    street VARCHAR(150) NOT NULL,
    building_no VARCHAR(50),
    details VARCHAR(255),
    FOREIGN KEY (user_id) REFERENCES users(user_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

CREATE TABLE carts (
    cart_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    total DOUBLE DEFAULT 0,
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

CREATE TABLE cart_items (
    cart_item_id INT AUTO_INCREMENT PRIMARY KEY,
    cart_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DOUBLE NOT NULL,
    FOREIGN KEY (cart_id) REFERENCES carts(cart_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

CREATE TABLE orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    address_id INT,
    order_date DATE NOT NULL,
    status VARCHAR(50) NOT NULL,
    total DOUBLE NOT NULL,
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    FOREIGN KEY (address_id) REFERENCES addresses(address_id)
        ON DELETE SET NULL
        ON UPDATE CASCADE
);

CREATE TABLE order_items (
    order_item_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DOUBLE NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(order_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

CREATE TABLE payments (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    method VARCHAR(50) NOT NULL,
    amount DOUBLE NOT NULL,
    status VARCHAR(50) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(order_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

CREATE TABLE prescriptions (
    prescription_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    order_id INT,
    file_path VARCHAR(255) NOT NULL,
    issue_date DATE NOT NULL,
    status VARCHAR(50) NOT NULL,
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(order_id)
        ON DELETE SET NULL
        ON UPDATE CASCADE
);

CREATE TABLE contact_messages (
    message_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT,
    subject VARCHAR(150) NOT NULL,
    message TEXT NOT NULL,
    status VARCHAR(50) DEFAULT 'Unread',
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id)
        ON DELETE SET NULL
        ON UPDATE CASCADE
);

INSERT INTO users (name, email, password, role)
VALUES 
('Admin User', 'admin@alhabib.com', '$2y$12$2yES7hEJ59HIkeRmaaeXjevio7yn4ILCnaxSEuQ4wwf1xtgFv9TZm', 'admin'),
('Ahmed Hassan', 'ahmed@example.com', '$2y$12$mcQ0V8f/7yYiHMLD.N5m2.gjIAXddL5IjBD93XtHR6oGq0HkK9H/q', 'customer');

INSERT INTO admins (user_id, role)
VALUES (1, 'admin');

INSERT INTO customers (user_id, phone, loyalty_points)
VALUES (2, '01012345678', 0);

INSERT INTO categories (name)
VALUES 
('Pain Relief'),
('Vitamins'),
('Antibiotics'),
('Allergy'),
('Gastric Care'),
('Respiratory Care'),
('Diabetes Care'),
('Heart Care'),
('Eye Care');

INSERT INTO companies (name)
VALUES 
('Panadol Care'),
('Pfizer Health'),
('Eva Pharma'),
('Sanofi Care'),
('Bayer'),
('GSK'),
('Equate Health'),
('Adel Germany');

INSERT INTO products
    (name, price, stock, category_id, company_id, generic_name, brand_name, manufacturer_name, product_ndc, dosage_form, route, image_url, source)
VALUES
('Panadol Cold & Flu', 75.00, 25, 1, 1, 'Acetaminophen', 'Panadol', 'GSK', NULL, 'Tablet', 'Oral', 'assets/images/products/panadol.jpg', 'local'),
('Ibuprofen 400mg', 40.00, 32, 1, 5, 'Ibuprofen', 'Brufen', 'Bayer', NULL, 'Tablet', 'Oral', 'assets/images/products/ibuprofen-400mg.jpg', 'local'),
('Aspirin 81mg', 35.00, 40, 1, 5, 'Aspirin', 'Aspirin', 'Bayer', NULL, 'Tablet', 'Oral', 'assets/images/products/aspirin.jpg', 'local'),
('Celecoxib 200mg', 95.00, 18, 1, 2, 'Celecoxib', 'Celebrex', 'Pfizer', NULL, 'Capsule', 'Oral', 'assets/images/products/celecoxib.avif', 'local'),
('Vitamin C 500mg', 60.00, 33, 2, 3, 'Ascorbic Acid', 'Vitamin C', 'Eva Pharma', NULL, 'Tablet', 'Oral', 'assets/images/products/vitamin-c-500mg.jpg', 'local'),
('Amoxicillin 500mg', 45.00, 22, 3, 2, 'Amoxicillin', 'Amoxil', 'Pfizer', NULL, 'Capsule', 'Oral', 'assets/images/products/amoxicillin-500mg.jpg', 'local'),
('Azithromycin 500mg', 88.00, 20, 3, 2, 'Azithromycin', 'Zithromax', 'Pfizer', NULL, 'Tablet', 'Oral', 'assets/images/products/azithromycin.jpg', 'local'),
('Doxycycline 100mg', 52.00, 24, 3, 3, 'Doxycycline', 'Doxycycline', 'Eva Pharma', NULL, 'Capsule', 'Oral', 'assets/images/products/doxycycline-100mg.jpg', 'local'),
('Cetirizine 10mg', 30.00, 45, 4, 4, 'Cetirizine', 'Zyrtec', 'Sanofi', NULL, 'Tablet', 'Oral', 'assets/images/products/cetirizine.jpg', 'local'),
('Omeprazole 20mg', 55.00, 28, 5, 4, 'Omeprazole', 'Prilosec', 'Sanofi', NULL, 'Capsule', 'Oral', 'assets/images/products/omeprazole.jpg', 'local'),
('Ventolin Inhaler', 120.00, 16, 6, 6, 'Salbutamol', 'Ventolin', 'GSK', NULL, 'Inhaler', 'Inhalation', 'assets/images/products/ventolin-inhaler.jpg', 'local'),
('Equate DayTime Cold & Flu', 110.00, 14, 6, 7, 'Acetaminophen Dextromethorphan Phenylephrine', 'Equate DayTime', 'Equate Health', '49035-659-22', 'Softgel', 'Oral', 'assets/images/products/equate-daytime-cold-flu.webp', 'local'),
('Metformin 500mg', 42.00, 36, 7, 3, 'Metformin', 'Metformin', 'Eva Pharma', NULL, 'Tablet', 'Oral', 'assets/images/products/metformin-500mg.jpg', 'local'),
('Lisinopril 20mg', 58.00, 30, 8, 2, 'Lisinopril', 'Lisinopril', 'Pfizer', NULL, 'Tablet', 'Oral', 'assets/images/products/lisinopril-20mg.jpg', 'local'),
('Cineraria Maritima Eye Drops', 70.00, 12, 9, 8, 'Cineraria Maritima', 'Cineraria Maritima', 'Adel Germany', NULL, 'Eye Drops', 'Ophthalmic', 'assets/images/products/cineraria-eye-drops.jpg', 'local');
