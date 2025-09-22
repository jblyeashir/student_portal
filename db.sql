-- Database: student_portal

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('super_admin','admin','counsellor','student') NOT NULL DEFAULT 'student',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE students (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  phone VARCHAR(30),
  country VARCHAR(100),
  subject VARCHAR(255),
  extra JSON NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE stages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  position INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE applications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  university VARCHAR(255),
  program VARCHAR(255),
  current_stage_id INT DEFAULT NULL,
  extra JSON NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  FOREIGN KEY (current_stage_id) REFERENCES stages(id) ON DELETE SET NULL
);

CREATE TABLE application_stage_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  application_id INT NOT NULL,
  stage_id INT NOT NULL,
  status VARCHAR(100) DEFAULT 'pending',
  notes TEXT,
  updated_by INT,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE,
  FOREIGN KEY (stage_id) REFERENCES stages(id) ON DELETE CASCADE
);

CREATE TABLE doc_types (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(150) NOT NULL,
  required TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE documents (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  doc_type_id INT NOT NULL,
  file_path VARCHAR(255) NOT NULL,
  status ENUM('pending','verified','rejected') DEFAULT 'pending',
  uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  FOREIGN KEY (doc_type_id) REFERENCES doc_types(id) ON DELETE CASCADE
);

CREATE TABLE notes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  message TEXT,
  added_by INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);

CREATE TABLE notifications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  type ENUM('email','sms') NOT NULL,
  message TEXT,
  sent_at TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Default stages
INSERT INTO stages (title, position) VALUES
('Consultation',1),
('Shortlisting',2),
('Application',3),
('Conditional Offer',4),
('Documents Send',5),
('Offer',6),
('Bank Payment',7),
('Viva',8),
('Visa',9),
('Pre-departure',10);

-- Default document types
INSERT INTO doc_types (title, required) VALUES
('Passport',1),
('Transcript',1),
('IELTS/TOEFL',0),
('Statement of Purpose',0),
('Offer Letter',0);

-- Super admin (change password after import)
INSERT INTO users (name,email,password,role) VALUES
('Super Admin','super@example.com','$2y$10$wH4dH8p2bJvZ7wq8ZxQhWO8z3YcQMh6vZz0Qk3Yb6r6o8v1Qy8V0K','super_admin');
