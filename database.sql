

-- 1. Users Table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(15),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Properties Table
CREATE TABLE properties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    city VARCHAR(100) NOT NULL,
    address TEXT,
    price DECIMAL(10,2) NOT NULL,
    gender ENUM('male', 'female', 'both') NOT NULL,
    rating DECIMAL(3,1) DEFAULT 0,
    description TEXT,
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. Amenities Table
CREATE TABLE amenities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
);

-- 4. Property Amenities (Junction Table)
CREATE TABLE property_amenities (
    property_id INT NOT NULL,
    amenity_id INT NOT NULL,
    PRIMARY KEY (property_id, amenity_id),
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    FOREIGN KEY (amenity_id) REFERENCES amenities(id) ON DELETE CASCADE
);

-- 5. Interested Users Table
CREATE TABLE interested_users (
    user_id INT NOT NULL,
    property_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, property_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
);

-- ============================================
-- SAMPLE DATA
-- ============================================

-- Amenities
INSERT INTO amenities (name) VALUES
('WiFi'), ('AC'), ('Laundry'), ('Meals Included'),
('Parking'), ('Hot Water'), ('TV'), ('Study Room'),
('Security'), ('Power Backup');

-- Sample Properties
INSERT INTO properties (name, city, address, price, gender, rating, description, image) VALUES
('Sunrise PG', 'Delhi', '12, Lajpat Nagar, New Delhi', 6500, 'female', 4.2,
 'A comfortable and safe PG for female students near metro station. Homely atmosphere with tasty meals.', 'property1.jpg'),

('Green Nest PG', 'Bangalore', '45, Koramangala 5th Block, Bangalore', 8000, 'male', 4.5,
 'Modern PG with high-speed WiFi and AC rooms. Walking distance from major tech parks.', 'property2.jpg'),

('Student Hub', 'Pune', '7, Kothrud, Pune', 5500, 'both', 3.8,
 'Affordable co-ed PG with all basic amenities. Regular housekeeping and 24/7 security.', 'property3.jpg'),

('Royal Residency', 'Mumbai', '23, Andheri West, Mumbai', 10000, 'male', 4.7,
 'Premium PG with attached bathroom, AC, and fully furnished rooms near colleges.', 'property4.jpg'),

('City Comfort PG', 'Hyderabad', '89, Himayat Nagar, Hyderabad', 7000, 'female', 4.0,
 'Well-maintained ladies PG with strict security, meals, and a rooftop terrace.', 'property5.jpg'),

('Scholar Stay', 'Chennai', '34, T. Nagar, Chennai', 6000, 'both', 4.3,
 'Co-ed PG popular among engineering students. Excellent food and study environment.', 'property6.jpg');

-- Map Amenities to Properties
INSERT INTO property_amenities (property_id, amenity_id) VALUES
(1,1),(1,4),(1,6),(1,9),
(2,1),(2,2),(2,3),(2,7),(2,10),
(3,1),(3,4),(3,6),(3,9),
(4,1),(4,2),(4,5),(4,7),(4,8),(4,9),(4,10),
(5,1),(5,2),(5,4),(5,6),(5,9),
(6,1),(6,4),(6,6),(6,8),(6,9);

-- Sample User (password: 'password123' hashed)
INSERT INTO users (name, email, password, phone) VALUES
('Test Student', 'test@student.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9876543210');
