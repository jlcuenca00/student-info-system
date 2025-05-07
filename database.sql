-- Create Database
CREATE DATABASE IF NOT EXISTS student_information_system;
USE student_information_system;

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('student', 'teacher', 'admin') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Departments Table
CREATE TABLE IF NOT EXISTS departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
);

-- Courses Table
CREATE TABLE IF NOT EXISTS courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    department_id INT,
    FOREIGN KEY (department_id) REFERENCES departments(id)
);

-- Teachers Table
CREATE TABLE IF NOT EXISTS teachers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    department_id INT,
    email VARCHAR(100),
    mobile_no VARCHAR(20),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (department_id) REFERENCES departments(id)
);

-- Students Table
CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    student_id VARCHAR(20) NOT NULL UNIQUE,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    photo VARCHAR(255) DEFAULT 'default.jpg',
    age INT,
    date_of_birth DATE,
    admission_date DATE,
    mobile_no VARCHAR(20),
    nationality VARCHAR(100),
    email VARCHAR(100),
    address TEXT,
    department_id INT,
    course_id INT,
    year_level INT,
    section VARCHAR(20),
    enrollment_status ENUM('Enrolled', 'Not Enrolled', 'Leave of Absence', 'Graduated', 'Dropped') DEFAULT 'Enrolled',
    units_taken_current INT DEFAULT 0,
    units_taken_overall INT DEFAULT 0,
    gpa DECIMAL(3,2) DEFAULT 1.0,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (department_id) REFERENCES departments(id),
    FOREIGN KEY (course_id) REFERENCES courses(id)
);

-- Admins Table
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Insert Departments
INSERT INTO departments (name) VALUES 
('BAA'), 
('CSE'), 
('THM'), 
('DIPLOMA');

-- Insert Courses
INSERT INTO courses (name, department_id) VALUES 
('Business Administration', 1),
('Accounting', 1),
('Computer Science', 2),
('Information Technology', 2),
('Tourism Management', 3),
('Hospitality Management', 3),
('Diploma in Culinary Arts', 4),
('Diploma in Hotel Operations', 4);

-- Insert Users (password is bcrypt hashed 'password123')
-- Admins
INSERT INTO users (username, password, role) VALUES 
('admin1', '$2y$10$8MNuapTe.0aRU7MsHX/Ywe2ERG0N0syW5qWl.X8hJECw2ZqrNuGfq', 'admin'),
('admin2', '$2y$10$8MNuapTe.0aRU7MsHX/Ywe2ERG0N0syW5qWl.X8hJECw2ZqrNuGfq', 'admin'),
('admin3', '$2y$10$8MNuapTe.0aRU7MsHX/Ywe2ERG0N0syW5qWl.X8hJECw2ZqrNuGfq', 'admin');

-- Teachers
INSERT INTO users (username, password, role) VALUES 
('teacher1', '$2y$10$8MNuapTe.0aRU7MsHX/Ywe2ERG0N0syW5qWl.X8hJECw2ZqrNuGfq', 'teacher'),
('teacher2', '$2y$10$8MNuapTe.0aRU7MsHX/Ywe2ERG0N0syW5qWl.X8hJECw2ZqrNuGfq', 'teacher'),
('teacher3', '$2y$10$8MNuapTe.0aRU7MsHX/Ywe2ERG0N0syW5qWl.X8hJECw2ZqrNuGfq', 'teacher');

-- Students
INSERT INTO users (username, password, role) VALUES 
('student1', '$2y$10$8MNuapTe.0aRU7MsHX/Ywe2ERG0N0syW5qWl.X8hJECw2ZqrNuGfq', 'student'),
('student2', '$2y$10$8MNuapTe.0aRU7MsHX/Ywe2ERG0N0syW5qWl.X8hJECw2ZqrNuGfq', 'student'),
('student3', '$2y$10$8MNuapTe.0aRU7MsHX/Ywe2ERG0N0syW5qWl.X8hJECw2ZqrNuGfq', 'student'),
('student4', '$2y$10$8MNuapTe.0aRU7MsHX/Ywe2ERG0N0syW5qWl.X8hJECw2ZqrNuGfq', 'student'),
('student5', '$2y$10$8MNuapTe.0aRU7MsHX/Ywe2ERG0N0syW5qWl.X8hJECw2ZqrNuGfq', 'student'),
('student6', '$2y$10$8MNuapTe.0aRU7MsHX/Ywe2ERG0N0syW5qWl.X8hJECw2ZqrNuGfq', 'student'),
('student7', '$2y$10$8MNuapTe.0aRU7MsHX/Ywe2ERG0N0syW5qWl.X8hJECw2ZqrNuGfq', 'student'),
('student8', '$2y$10$8MNuapTe.0aRU7MsHX/Ywe2ERG0N0syW5qWl.X8hJECw2ZqrNuGfq', 'student'),
('student9', '$2y$10$8MNuapTe.0aRU7MsHX/Ywe2ERG0N0syW5qWl.X8hJECw2ZqrNuGfq', 'student'),
('student10', '$2y$10$8MNuapTe.0aRU7MsHX/Ywe2ERG0N0syW5qWl.X8hJECw2ZqrNuGfq', 'student');

-- Insert Admin Records
INSERT INTO admins (user_id, first_name, last_name, email) VALUES 
(1, 'Admin', 'One', 'admin1@school.edu'),
(2, 'Admin', 'Two', 'admin2@school.edu'),
(3, 'Admin', 'Three', 'admin3@school.edu');

-- Insert Teacher Records
INSERT INTO teachers (user_id, first_name, last_name, department_id, email, mobile_no) VALUES 
(4, 'Teacher', 'One', 1, 'teacher1@school.edu', '9123456781'),
(5, 'Teacher', 'Two', 2, 'teacher2@school.edu', '9123456782'),
(6, 'Teacher', 'Three', 3, 'teacher3@school.edu', '9123456783');

-- Insert Student Records
INSERT INTO students (
    user_id, student_id, first_name, last_name, photo, age, date_of_birth, 
    admission_date, mobile_no, nationality, email, address, 
    department_id, course_id, year_level, section, 
    enrollment_status, units_taken_current, units_taken_overall, gpa
) VALUES 
(7, '2023-0001', 'John', 'Doe', 'student1.jpg', 20, '2003-05-15', '2023-06-01', '9123456001', 'Filipino', 'john.doe@student.edu', '123 Main St, Manila', 1, 1, 2, 'A', 'Enrolled', 21, 63, 1.75),
(8, '2023-0002', 'Jane', 'Smith', 'student2.jpg', 21, '2002-09-20', '2023-06-01', '9123456002', 'Filipino', 'jane.smith@student.edu', '456 Elm St, Quezon City', 1, 2, 2, 'B', 'Enrolled', 18, 54, 1.25),
(9, '2023-0003', 'Michael', 'Johnson', 'student3.jpg', 19, '2004-02-10', '2023-06-01', '9123456003', 'Filipino', 'michael.johnson@student.edu', '789 Oak St, Pasig', 2, 3, 1, 'A', 'Enrolled', 21, 21, 2.0),
(10, '2023-0004', 'Emily', 'Brown', 'student4.jpg', 20, '2003-11-05', '2023-06-01', '9123456004', 'American', 'emily.brown@student.edu', '101 Pine St, Makati', 2, 4, 1, 'B', 'Enrolled', 21, 21, 1.5),
(11, '2023-0005', 'David', 'Garcia', 'student5.jpg', 22, '2001-07-25', '2022-06-01', '9123456005', 'Filipino', 'david.garcia@student.edu', '202 Maple St, Taguig', 3, 5, 3, 'A', 'Enrolled', 18, 93, 1.0),
(12, '2023-0006', 'Sarah', 'Martinez', 'student6.jpg', 21, '2002-03-15', '2022-06-01', '9123456006', 'Filipino', 'sarah.martinez@student.edu', '303 Cedar St, Parañaque', 3, 6, 3, 'B', 'Enrolled', 18, 90, 1.25),
(13, '2023-0007', 'James', 'Wilson', 'student7.jpg', 20, '2003-08-30', '2023-01-06', '9123456007', 'Filipino', 'james.wilson@student.edu', '404 Birch St, Pasay', 4, 7, 2, 'A', 'Enrolled', 21, 63, 1.5),
(14, '2023-0008', 'Jessica', 'Anderson', 'student8.jpg', 19, '2004-12-10', '2023-01-06', '9123456008', 'Filipino', 'jessica.anderson@student.edu', '505 Walnut St, Manila', 4, 8, 1, 'B', 'Enrolled', 21, 21, 2.25),
(15, '2023-0009', 'Robert', 'Taylor', 'student9.jpg', 20, '2003-06-20', '2023-06-01', '9123456009', 'Filipino', 'robert.taylor@student.edu', '606 Spruce St, Quezon City', 2, 3, 2, 'A', 'Enrolled', 21, 63, 1.75),
(16, '2023-0010', 'Jennifer', 'Thomas', 'student10.jpg', 21, '2002-04-05', '2022-06-01', '9123456010', 'Filipino', 'jennifer.thomas@student.edu', '707 Cherry St, Makati', 1, 1, 3, 'B', 'Enrolled', 18, 93, 1.25);

-- Create directory for uploads if it doesn't exist
-- Note: This needs to be done in PHP as SQL cannot create directories