-- Online Computer Store Database Schema

CREATE DATABASE IF NOT EXISTS computer_store;
USE computer_store;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    is_admin TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products table
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    image_url VARCHAR(255),
    category VARCHAR(50) NOT NULL,
    stock INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Cart table
CREATE TABLE IF NOT EXISTS cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_cart_item (user_id, product_id)
);

-- Orders table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_price DECIMAL(10, 2) NOT NULL,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status VARCHAR(20) DEFAULT 'pending',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Order items table 
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

INSERT INTO users (name, email, password, is_admin) VALUES
('Admin User', 'admin@store.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1);

INSERT INTO products (name, description, price, image_url, category, stock) VALUES
-- LAPTOPS
('Gaming Laptop Pro', 'High-performance gaming laptop with RTX 4070, 16GB RAM, 1TB SSD, Intel i7-13700H, 15.6" FHD 144Hz display', 1299.99, 'https://via.placeholder.com/300x300?text=Gaming+Laptop+Pro', 'laptops', 15),
('Ultrabook 14"', 'Lightweight ultrabook with Intel i5-1340P, 8GB RAM, 256GB SSD, 14" FHD IPS display, 10-hour battery', 699.99, 'https://via.placeholder.com/300x300?text=Ultrabook+14', 'laptops', 12),
('Gaming Laptop Elite', 'Premium gaming laptop with RTX 4080, 32GB RAM, 2TB SSD, AMD Ryzen 9 7940HS, 17.3" QHD 165Hz', 2499.99, 'https://via.placeholder.com/300x300?text=Gaming+Laptop+Elite', 'laptops', 8),
('Business Laptop 15"', 'Professional business laptop with Intel i7-1355U, 16GB RAM, 512GB SSD, 15.6" FHD touchscreen', 999.99, 'https://via.placeholder.com/300x300?text=Business+Laptop', 'laptops', 20),
('Budget Laptop', 'Affordable laptop with Intel i3-1215U, 8GB RAM, 256GB SSD, 15.6" HD display, perfect for students', 449.99, 'https://via.placeholder.com/300x300?text=Budget+Laptop', 'laptops', 35),
('Workstation Laptop', 'Mobile workstation with RTX 3000 Ada, 64GB RAM, 2TB SSD, Intel Xeon processor, 16" 4K display', 3499.99, 'https://via.placeholder.com/300x300?text=Workstation+Laptop', 'laptops', 5),
('2-in-1 Convertible', 'Versatile 2-in-1 laptop with Intel i5-1335U, 16GB RAM, 512GB SSD, 13.3" FHD touchscreen, 360° hinge', 849.99, 'https://via.placeholder.com/300x300?text=2-in-1+Laptop', 'laptops', 18),
('Gaming Laptop Budget', 'Entry-level gaming laptop with RTX 4050, 16GB RAM, 512GB SSD, AMD Ryzen 5 7535HS, 15.6" FHD 120Hz', 899.99, 'https://via.placeholder.com/300x300?text=Budget+Gaming+Laptop', 'laptops', 22),
('MacBook Pro Alternative', 'High-end laptop with AMD Ryzen 9 7940HS, 32GB RAM, 1TB SSD, 16" QHD display, premium build', 1899.99, 'https://via.placeholder.com/300x300?text=Premium+Laptop', 'laptops', 10),
('Chromebook 14"', 'Lightweight Chromebook with MediaTek Kompanio 828, 8GB RAM, 128GB eMMC, 14" FHD display', 299.99, 'https://via.placeholder.com/300x300?text=Chromebook', 'laptops', 40),

-- DESKTOPS
('Business Desktop', 'Reliable desktop computer for office work, Intel i7-13700, 16GB RAM, 512GB SSD, Windows 11 Pro', 899.99, 'https://via.placeholder.com/300x300?text=Business+Desktop', 'desktops', 20),
('Workstation Desktop', 'Professional workstation with AMD Ryzen 9 7950X, 32GB RAM, 2TB SSD, NVIDIA RTX A4000, Windows 11 Pro', 1899.99, 'https://via.placeholder.com/300x300?text=Workstation+Desktop', 'desktops', 10),
('Gaming Desktop Pro', 'High-performance gaming PC with RTX 4070, Intel i7-13700K, 32GB RAM, 1TB SSD, 850W PSU, RGB lighting', 1999.99, 'https://via.placeholder.com/300x300?text=Gaming+Desktop+Pro', 'desktops', 12),
('Budget Desktop', 'Affordable desktop PC with Intel i5-12400, 8GB RAM, 256GB SSD, integrated graphics, perfect for home use', 499.99, 'https://via.placeholder.com/300x300?text=Budget+Desktop', 'desktops', 30),
('Gaming Desktop Elite', 'Ultimate gaming rig with RTX 4090, AMD Ryzen 9 7950X3D, 64GB RAM, 2TB NVMe SSD, liquid cooling, RGB', 3999.99, 'https://via.placeholder.com/300x300?text=Gaming+Desktop+Elite', 'desktops', 6),
('Mini PC', 'Compact mini desktop with Intel N100, 16GB RAM, 512GB SSD, perfect for HTPC or office use', 399.99, 'https://via.placeholder.com/300x300?text=Mini+PC', 'desktops', 25),
('All-in-One PC 24"', 'Sleek all-in-one desktop with Intel i5-13400, 16GB RAM, 512GB SSD, 24" FHD touchscreen, wireless keyboard/mouse', 1099.99, 'https://via.placeholder.com/300x300?text=All-in-One+PC', 'desktops', 15),
('Home Office Desktop', 'Complete home office solution with Intel i5-13500, 16GB RAM, 512GB SSD, WiFi 6, Windows 11', 749.99, 'https://via.placeholder.com/300x300?text=Home+Office+Desktop', 'desktops', 18),
('Gaming Desktop Mid-Range', 'Solid gaming PC with RTX 4060 Ti, AMD Ryzen 7 7700X, 16GB RAM, 1TB SSD, 750W PSU', 1299.99, 'https://via.placeholder.com/300x300?text=Mid-Range+Gaming+PC', 'desktops', 14),
('Server Desktop', 'Powerful server-grade desktop with dual Xeon processors, 64GB ECC RAM, 4TB storage, redundant PSU', 2999.99, 'https://via.placeholder.com/300x300?text=Server+Desktop', 'desktops', 4),

-- GRAPHIC CARDS
('RTX 4090 Graphics Card', 'NVIDIA GeForce RTX 4090 24GB GDDR6X, Ada Lovelace architecture, DLSS 3, ray tracing, 4K gaming ready', 1599.99, 'https://via.placeholder.com/300x300?text=RTX+4090', 'graphic_cards', 8),
('RTX 4060 Graphics Card', 'NVIDIA GeForce RTX 4060 8GB GDDR6, perfect for 1080p gaming, DLSS 3 support, efficient power consumption', 299.99, 'https://via.placeholder.com/300x300?text=RTX+4060', 'graphic_cards', 15),
('RTX 4080 Graphics Card', 'NVIDIA GeForce RTX 4080 16GB GDDR6X, high-end gaming and content creation, excellent 1440p/4K performance', 1199.99, 'https://via.placeholder.com/300x300?text=RTX+4080', 'graphic_cards', 10),
('RTX 4070 Graphics Card', 'NVIDIA GeForce RTX 4070 12GB GDDR6X, great 1440p gaming performance, DLSS 3, ray tracing', 599.99, 'https://via.placeholder.com/300x300?text=RTX+4070', 'graphic_cards', 12),
('RTX 4070 Ti Graphics Card', 'NVIDIA GeForce RTX 4070 Ti 12GB GDDR6X, premium 1440p and entry 4K gaming, excellent value', 799.99, 'https://via.placeholder.com/300x300?text=RTX+4070+Ti', 'graphic_cards', 11),
('RTX 3060 Graphics Card', 'NVIDIA GeForce RTX 3060 12GB GDDR6, budget-friendly ray tracing, great for 1080p gaming', 329.99, 'https://via.placeholder.com/300x300?text=RTX+3060', 'graphic_cards', 20),
('RX 7900 XTX Graphics Card', 'AMD Radeon RX 7900 XTX 24GB GDDR6, flagship AMD GPU, excellent 4K performance, FSR support', 999.99, 'https://via.placeholder.com/300x300?text=RX+7900+XTX', 'graphic_cards', 9),
('RX 7800 XT Graphics Card', 'AMD Radeon RX 7800 XT 16GB GDDR6, great 1440p gaming, competitive pricing, FSR 3 support', 499.99, 'https://via.placeholder.com/300x300?text=RX+7800+XT', 'graphic_cards', 13),
('RX 7700 XT Graphics Card', 'AMD Radeon RX 7700 XT 12GB GDDR6, solid 1440p performance, budget-friendly option', 449.99, 'https://via.placeholder.com/300x300?text=RX+7700+XT', 'graphic_cards', 14),
('RTX 3050 Graphics Card', 'NVIDIA GeForce RTX 3050 8GB GDDR6, entry-level ray tracing, perfect for budget gaming builds', 249.99, 'https://via.placeholder.com/300x300?text=RTX+3050', 'graphic_cards', 18),
('RX 7600 Graphics Card', 'AMD Radeon RX 7600 8GB GDDR6, excellent 1080p gaming, great value proposition', 269.99, 'https://via.placeholder.com/300x300?text=RX+7600', 'graphic_cards', 16),
('RTX 3090 Graphics Card', 'NVIDIA GeForce RTX 3090 24GB GDDR6X, previous gen flagship, still powerful for 4K gaming', 1299.99, 'https://via.placeholder.com/300x300?text=RTX+3090', 'graphic_cards', 5),

-- MEMORIES
('DDR5 32GB RAM Kit', '32GB DDR5 5600MHz Memory Kit (2x16GB), low latency, RGB lighting, XMP 3.0 support', 199.99, 'https://via.placeholder.com/300x300?text=DDR5+32GB', 'memories', 30),
('DDR4 16GB RAM Kit', '16GB DDR4 3200MHz Memory Kit (2x8GB), reliable performance, compatible with most systems', 89.99, 'https://via.placeholder.com/300x300?text=DDR4+16GB', 'memories', 50),
('DDR5 64GB RAM Kit', '64GB DDR5 6000MHz Memory Kit (2x32GB), high-speed memory for workstations and content creation', 449.99, 'https://via.placeholder.com/300x300?text=DDR5+64GB', 'memories', 15),
('DDR5 16GB RAM Kit', '16GB DDR5 5200MHz Memory Kit (2x8GB), entry-level DDR5, great for budget builds', 119.99, 'https://via.placeholder.com/300x300?text=DDR5+16GB', 'memories', 40),
('DDR4 32GB RAM Kit', '32GB DDR4 3600MHz Memory Kit (2x16GB), high-speed DDR4, RGB lighting, excellent value', 149.99, 'https://via.placeholder.com/300x300?text=DDR4+32GB', 'memories', 35),
('DDR5 48GB RAM Kit', '48GB DDR5 6000MHz Memory Kit (2x24GB), unique capacity, perfect for content creators', 329.99, 'https://via.placeholder.com/300x300?text=DDR5+48GB', 'memories', 12),
('DDR4 8GB RAM Stick', '8GB DDR4 3200MHz single stick, budget-friendly upgrade option', 34.99, 'https://via.placeholder.com/300x300?text=DDR4+8GB', 'memories', 60),
('DDR5 128GB RAM Kit', '128GB DDR5 5600MHz Memory Kit (4x32GB), extreme capacity for servers and workstations', 1299.99, 'https://via.placeholder.com/300x300?text=DDR5+128GB', 'memories', 3),
('DDR4 64GB RAM Kit', '64GB DDR4 3200MHz Memory Kit (4x16GB), high capacity for professional work', 299.99, 'https://via.placeholder.com/300x300?text=DDR4+64GB', 'memories', 8),
('DDR5 RGB 32GB Kit', '32GB DDR5 6000MHz RGB Memory Kit (2x16GB), premium RGB lighting, high performance', 229.99, 'https://via.placeholder.com/300x300?text=DDR5+RGB+32GB', 'memories', 25),

-- ACCESSORIES
('Mechanical Keyboard', 'RGB Mechanical Gaming Keyboard with Cherry MX Red switches, full-size layout, aluminum frame', 129.99, 'https://via.placeholder.com/300x300?text=Mechanical+Keyboard', 'accessories', 25),
('Gaming Mouse', 'High-precision gaming mouse with 16000 DPI sensor, RGB lighting, programmable buttons, ergonomic design', 79.99, 'https://via.placeholder.com/300x300?text=Gaming+Mouse', 'accessories', 40),
('Gaming Monitor 27"', '27-inch QHD 1440p gaming monitor, 165Hz refresh rate, 1ms response time, G-Sync compatible, HDR', 349.99, 'https://via.placeholder.com/300x300?text=Gaming+Monitor+27', 'accessories', 18),
('Wireless Keyboard', 'Ergonomic wireless keyboard with rechargeable battery, quiet keys, multi-device connectivity', 69.99, 'https://via.placeholder.com/300x300?text=Wireless+Keyboard', 'accessories', 30),
('Gaming Headset', '7.1 surround sound gaming headset with noise-canceling mic, RGB lighting, comfortable ear cups', 99.99, 'https://via.placeholder.com/300x300?text=Gaming+Headset', 'accessories', 35),
('4K Monitor 32"', '32-inch 4K UHD monitor, 60Hz, IPS panel, HDR400, USB-C connectivity, perfect for content creation', 499.99, 'https://via.placeholder.com/300x300?text=4K+Monitor+32', 'accessories', 12),
('Webcam 1080p', 'Full HD 1080p webcam with autofocus, built-in microphone, privacy shutter, USB plug-and-play', 59.99, 'https://via.placeholder.com/300x300?text=Webcam+1080p', 'accessories', 45),
('USB-C Hub', '7-in-1 USB-C hub with HDMI, USB 3.0 ports, SD card reader, PD charging, compact design', 39.99, 'https://via.placeholder.com/300x300?text=USB-C+Hub', 'accessories', 50),
('External SSD 1TB', 'Portable 1TB external SSD, USB 3.2 Gen 2, 1000MB/s read speed, shock-resistant, compact', 89.99, 'https://via.placeholder.com/300x300?text=External+SSD+1TB', 'accessories', 28),
('Gaming Mouse Pad', 'Large RGB gaming mouse pad, 900x400mm, smooth surface, water-resistant, RGB edge lighting', 29.99, 'https://via.placeholder.com/300x300?text=Gaming+Mouse+Pad', 'accessories', 55),
('Wireless Mouse', 'Ergonomic wireless mouse with 2400 DPI, long battery life, silent clicks, perfect for office use', 24.99, 'https://via.placeholder.com/300x300?text=Wireless+Mouse', 'accessories', 60),
('USB Flash Drive 128GB', 'High-speed 128GB USB 3.0 flash drive, compact design, metal casing, 5-year warranty', 19.99, 'https://via.placeholder.com/300x300?text=USB+128GB', 'accessories', 70),
('Monitor Stand', 'Adjustable dual monitor stand, VESA compatible, height/tilt/swivel adjustment, cable management', 79.99, 'https://via.placeholder.com/300x300?text=Monitor+Stand', 'accessories', 22),
('Laptop Stand', 'Aluminum laptop stand with adjustable height, ergonomic design, improves airflow, portable', 34.99, 'https://via.placeholder.com/300x300?text=Laptop+Stand', 'accessories', 38),
('USB Microphone', 'Professional USB condenser microphone with cardioid pattern, studio-quality recording, plug-and-play', 89.99, 'https://via.placeholder.com/300x300?text=USB+Microphone', 'accessories', 20),
('External HDD 2TB', 'Portable 2TB external hard drive, USB 3.0, shock-resistant, password protection, backup software', 69.99, 'https://via.placeholder.com/300x300?text=External+HDD+2TB', 'accessories', 32),
('Gaming Chair', 'Ergonomic gaming chair with lumbar support, adjustable armrests, 360° swivel, high-quality materials', 249.99, 'https://via.placeholder.com/300x300?text=Gaming+Chair', 'accessories', 15),
('Desk Speakers', '2.0 desktop speakers with Bluetooth, USB connectivity, rich bass, compact design, volume control', 49.99, 'https://via.placeholder.com/300x300?text=Desk+Speakers', 'accessories', 25),
('Cable Management Kit', 'Complete cable management kit with clips, ties, sleeves, and organizers for clean desk setup', 14.99, 'https://via.placeholder.com/300x300?text=Cable+Kit', 'accessories', 80),
('Laptop Cooling Pad', 'USB-powered laptop cooling pad with 5 fans, adjustable height, LED lighting, quiet operation', 39.99, 'https://via.placeholder.com/300x300?text=Cooling+Pad', 'accessories', 42),
('HDMI Cable 4K', 'High-speed HDMI 2.1 cable, 6ft length, supports 4K@120Hz, HDR, Ethernet, gold-plated connectors', 24.99, 'https://via.placeholder.com/300x300?text=HDMI+Cable', 'accessories', 65),
('USB-C Cable', 'USB-C to USB-C cable, 100W power delivery, 10Gbps data transfer, 6ft length, braided design', 19.99, 'https://via.placeholder.com/300x300?text=USB-C+Cable', 'accessories', 58),
('Keyboard Wrist Rest', 'Ergonomic gel keyboard wrist rest, reduces strain, comfortable support, easy to clean', 16.99, 'https://via.placeholder.com/300x300?text=Wrist+Rest', 'accessories', 48),
('Surge Protector', '8-outlet surge protector with USB charging ports, 2000J protection, LED indicator, 6ft cord', 29.99, 'https://via.placeholder.com/300x300?text=Surge+Protector', 'accessories', 35);

