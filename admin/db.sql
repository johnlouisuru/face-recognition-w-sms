
-- Create database
CREATE DATABASE IF NOT EXISTS attendance_system;
USE attendance_system;

-- Students table
CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(50) UNIQUE NOT NULL,
    student_name VARCHAR(100) NOT NULL,
    grade VARCHAR(50) NOT NULL,
    face_descriptor TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Attendance table
CREATE TABLE IF NOT EXISTS attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(50) NOT NULL,
    student_name VARCHAR(100) NOT NULL,
    grade VARCHAR(50) NOT NULL,
    timestamp DATETIME NOT NULL,
    FOREIGN KEY (student_id) REFERENCES students(student_id)
);

-- Create index for faster queries
CREATE INDEX idx_student_id ON attendance(student_id);
CREATE INDEX idx_timestamp ON attendance(timestamp);