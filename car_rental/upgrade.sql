-- Run this INSTEAD of database.sql if you already have data you want to keep.
USE car_rental;

CREATE TABLE IF NOT EXISTS payments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  booking_id INT NOT NULL,
  user_id INT NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  method ENUM('bank','mtn','airtel') NOT NULL,
  account_ref VARCHAR(60) NOT NULL,
  transaction_ref VARCHAR(30) NOT NULL UNIQUE,
  status ENUM('paid','refund_pending','refunded') NOT NULL DEFAULT 'paid',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS notifications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  message VARCHAR(255) NOT NULL,
  link VARCHAR(150) DEFAULT NULL,
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_user_read (user_id, is_read),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS sms_log (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT DEFAULT NULL,
  phone VARCHAR(30) NOT NULL,
  message VARCHAR(480) NOT NULL,
  status ENUM('simulated','sent','failed') NOT NULL,
  response VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
