-- Create or drop the hotel_management database and then use it

DROP DATABASE IF EXISTS hotel_management;
CREATE DATABASE hotel_management;
USE hotel_management;

-- Table to store user information (Admins, Receptionists, Housekeepers)
CREATE TABLE users (
    u_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(50) NOT NULL -- 'admin', 'receptionist', 'housekeeper'
);

-- Table to store room details
CREATE TABLE rooms (
    room_num INT AUTO_INCREMENT PRIMARY KEY,
    room_type VARCHAR(50) NOT NULL, -- e.g., single, double, suite
    room_status VARCHAR(50) NOT NULL, -- available, occupied, maintenance
    rate_plan DECIMAL(10, 2) NOT NULL
);

-- Table to store guest information
CREATE TABLE guests (
    guest_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    contact_num VARCHAR(15) NOT NULL,
    email_address VARCHAR(100) NOT NULL
);

-- Table to store reservations
CREATE TABLE reservations (
    confirmation_num VARCHAR(20) NOT NULL PRIMARY KEY,
    guest_id INT,
    room_num INT,
    check_in_date DATE NOT NULL,
    check_out_date DATE NOT NULL,
    nights INT NOT NULL,
    status VARCHAR(50) NOT NULL, -- confirmed, cancelled, checked-in, checked-out
    balance DECIMAL(10, 2),
    FOREIGN KEY (guest_id) REFERENCES guests(guest_id),
    FOREIGN KEY (room_num) REFERENCES rooms(room_num)
);


-- Table to store housekeeping tasks
CREATE TABLE housekeeping_tasks (
    task_id INT AUTO_INCREMENT PRIMARY KEY,
    room_num INT,
    assigned_to INT,
    task_description TEXT NOT NULL,
    status VARCHAR(50) NOT NULL, -- pending, in-progress, completed
    FOREIGN KEY (room_num) REFERENCES rooms(room_num),
    FOREIGN KEY (assigned_to) REFERENCES users(u_id)
);

-- Insert user
INSERT INTO users (username, password, role) VALUES 
('Nicholas Frischkorn', 'hashed_password', 'admin'),
('Mark Feick', '$2y$10$A1DvR4BCecOYOYHyh5InE.tPgeqb8fvVYMFJbGFsEXSHYLiKZWDEa', 'admin'),
('Lyubomir Dimitrov', 'hashed_password', 'admin'),
('Skyler Dipofi', 'hashed_password', 'admin'),
('Michael Anderson', 'hashed_password', 'admin'),
('Sarah Williams', 'hashed_password', 'receptionist'),
('David McGubin', 'hashed_password', 'housekeeper'),
('Laura Taylor', 'hashed_password', 'receptionist'),
('James Goober', 'hashed_password', 'housekeeper');

-- Insert  guests
INSERT INTO guests (first_name, last_name, contact_num, email_address) VALUES
('John', 'Doe', '555-1234', 'johndoe@example.com'),
('Jane', 'Smith', '555-5678', 'janesmith@example.com'),
('Alice', 'Johnson', '555-8765', 'alicej@example.com'),
('Robert', 'Brown', '555-4321', 'robertbrown@example.com'),
('Emily', 'Davis', '555-6789', 'emilydavis@example.com');


-- Insert hotel rooms (01-25 on each floor)
INSERT INTO rooms (room_num, room_type, room_status, rate_plan) VALUES
-- Floor 1
('101', 'Single', 'available', 100.00),
('102', 'Double', 'occupied', 150.00),
('103', 'Suite', 'available', 250.00),
('104', 'Single', 'maintenance', 90.00),
('105', 'Double', 'available', 160.00),
('106', 'Suite', 'occupied', 230.00),
('107', 'Single', 'available', 100.00),
('108', 'Double', 'occupied', 150.00),
('109', 'Suite', 'available', 250.00),
('110', 'Single', 'maintenance', 90.00),
('111', 'Double', 'available', 160.00),
('112', 'Suite', 'occupied', 240.00),
('113', 'Single', 'available', 100.00),
('114', 'Double', 'occupied', 150.00),
('115', 'Suite', 'available', 250.00),
('116', 'Single', 'maintenance', 90.00),
('117', 'Double', 'available', 160.00),
('118', 'Suite', 'occupied', 220.00),
('119', 'Single', 'available', 100.00),
('120', 'Double', 'occupied', 150.00),
('121', 'Suite', 'available', 250.00),
('122', 'Single', 'maintenance', 90.00),
('123', 'Double', 'available', 160.00),
('124', 'Suite', 'occupied', 200.00),
('125', 'Single', 'available', 100.00),

-- Floor 2
('201', 'Single', 'available', 100.00),
('202', 'Double', 'occupied', 150.00),
('203', 'Suite', 'available', 250.00),
('204', 'Single', 'maintenance', 90.00),
('205', 'Double', 'available', 160.00),
('206', 'Suite', 'occupied', 230.00),
('207', 'Single', 'available', 100.00),
('208', 'Double', 'occupied', 150.00),
('209', 'Suite', 'available', 250.00),
('210', 'Single', 'maintenance', 90.00),
('211', 'Double', 'available', 160.00),
('212', 'Suite', 'occupied', 240.00),
('213', 'Single', 'available', 100.00),
('214', 'Double', 'occupied', 150.00),
('215', 'Suite', 'available', 250.00),
('216', 'Single', 'maintenance', 90.00),
('217', 'Double', 'available', 160.00),
('218', 'Suite', 'occupied', 220.00),
('219', 'Single', 'available', 100.00),
('220', 'Double', 'occupied', 150.00),
('221', 'Suite', 'available', 250.00),
('222', 'Single', 'maintenance', 90.00),
('223', 'Double', 'available', 160.00),
('224', 'Suite', 'occupied', 200.00),
('225', 'Single', 'available', 100.00),

-- Floor 3
('301', 'Single', 'available', 100.00),
('302', 'Double', 'occupied', 150.00),
('303', 'Suite', 'available', 250.00),
('304', 'Single', 'maintenance', 90.00),
('305', 'Double', 'available', 160.00),
('306', 'Suite', 'occupied', 230.00),
('307', 'Single', 'available', 100.00),
('308', 'Double', 'occupied', 150.00),
('309', 'Suite', 'available', 250.00),
('310', 'Single', 'maintenance', 90.00),
('311', 'Double', 'available', 160.00),
('312', 'Suite', 'occupied', 240.00),
('313', 'Single', 'available', 100.00),
('314', 'Double', 'occupied', 150.00),
('315', 'Suite', 'available', 250.00),
('316', 'Single', 'maintenance', 90.00),
('317', 'Double', 'available', 160.00),
('318', 'Suite', 'occupied', 220.00),
('319', 'Single', 'available', 100.00),
('320', 'Double', 'occupied', 150.00),
('321', 'Suite', 'available', 250.00),
('322', 'Single', 'maintenance', 90.00),
('323', 'Double', 'available', 160.00),
('324', 'Suite', 'occupied', 200.00),
('325', 'Single', 'available', 100.00),

-- Floor 4
('401', 'Single', 'available', 100.00),
('402', 'Double', 'occupied', 150.00),
('403', 'Suite', 'available', 250.00),
('404', 'Single', 'maintenance', 90.00),
('405', 'Double', 'available', 160.00),
('406', 'Suite', 'occupied', 230.00),
('407', 'Single', 'available', 100.00),
('408', 'Double', 'occupied', 150.00),
('409', 'Suite', 'available', 250.00),
('410', 'Single', 'maintenance', 90.00),
('411', 'Double', 'available', 160.00),
('412', 'Suite', 'occupied', 240.00),
('413', 'Single', 'available', 100.00),
('414', 'Double', 'occupied', 150.00),
('415', 'Suite', 'available', 250.00),
('416', 'Single', 'maintenance', 90.00),
('417', 'Double', 'available', 160.00),
('418', 'Suite', 'occupied', 220.00),
('419', 'Single', 'available', 100.00),
('420', 'Double', 'occupied', 150.00),
('421', 'Suite', 'available', 250.00),
('422', 'Single', 'maintenance', 90.00),
('423', 'Double', 'available', 160.00),
('424', 'Suite', 'occupied', 200.00),
('425', 'Single', 'available', 100.00),

-- Floor 5
('501', 'Single', 'available', 100.00),
('502', 'Double', 'occupied', 150.00),
('503', 'Suite', 'available', 250.00),
('504', 'Single', 'maintenance', 90.00),
('505', 'Double', 'available', 160.00),
('506', 'Suite', 'occupied', 230.00),
('507', 'Single', 'available', 100.00),
('508', 'Double', 'occupied', 150.00),
('509', 'Suite', 'available', 250.00),
('510', 'Single', 'maintenance', 90.00),
('511', 'Double', 'available', 160.00),
('512', 'Suite', 'occupied', 240.00),
('513', 'Single', 'available', 100.00),
('514', 'Double', 'occupied', 150.00),
('515', 'Suite', 'available', 250.00),
('516', 'Single', 'maintenance', 90.00),
('517', 'Double', 'available', 160.00),
('518', 'Suite', 'occupied', 220.00),
('519', 'Single', 'available', 100.00),
('520', 'Double', 'occupied', 150.00),
('521', 'Suite', 'available', 250.00),
('522', 'Single', 'maintenance', 90.00),
('523', 'Double', 'available', 160.00),
('524', 'Suite', 'occupied', 200.00),
('525', 'Single', 'available', 100.00);


-- Insert mock data
INSERT INTO reservations (confirmation_num, guest_id, room_num, check_in_date, check_out_date, nights, status, balance) VALUES
('CNF1001', 1, 101, '2025-01-10', '2025-01-15', 5, 'confirmed', 750.00),
('CNF1002', 2, 201, '2025-02-01', '2025-02-05', 4, 'checked-in', 640.00),
('CNF1003', 3, 301, '2025-01-20', '2025-01-22', 2, 'cancelled', 0.00),
('CNF1004', 4, 401, '2025-03-05', '2025-03-10', 5, 'confirmed', 800.00),
('CNF1005', 5, 501, '2025-01-15', '2025-01-20', 5, 'checked-out', 0.00);

-- Insert sample housekeeping tasks 
INSERT INTO housekeeping_tasks (room_num, assigned_to, task_description, status) VALUES
(501, 1, 'Clean room and replace linens', 'pending'), 
(401, 1, 'Restock minibar and clean bathroom', 'in-progress'),
(301, 1, 'Deep clean due to extended stay', 'completed'),
(401, 1, 'Vacuum carpets and dust furniture', 'pending'),
(222, 1, 'Sanitize high-touch surfaces', 'completed');


