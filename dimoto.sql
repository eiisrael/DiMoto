-- DiMoto (mobile-first, OSM/Leaflet) — Esquema
CREATE DATABASE IF NOT EXISTS dimoto CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE dimoto;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  role ENUM('passenger','driver','admin') NOT NULL,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  phone VARCHAR(30),
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS drivers (
  user_id INT PRIMARY KEY,
  vehicle_type ENUM('moto') NOT NULL DEFAULT 'moto',
  cnh VARCHAR(50) NOT NULL,
  vehicle_plate VARCHAR(20) NOT NULL,
  vehicle_model VARCHAR(80) NOT NULL,
  photo_path VARCHAR(255) NULL,
  cnh_path VARCHAR(255) NULL,
  is_online TINYINT(1) NOT NULL DEFAULT 0,
  current_lat DECIMAL(10,6),
  current_lng DECIMAL(10,6),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS ride_requests (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  passenger_id INT NOT NULL,
  pickup_lat DECIMAL(10,6) NOT NULL,
  pickup_lng DECIMAL(10,6) NOT NULL,
  drop_lat DECIMAL(10,6) NOT NULL,
  drop_lng DECIMAL(10,6) NOT NULL,
  status ENUM('pending','matched','cancelled') NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (passenger_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS rides (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  request_id BIGINT NOT NULL,
  passenger_id INT NOT NULL,
  driver_id INT,
  status ENUM('waiting_driver','en_route','in_progress','completed','cancelled') NOT NULL DEFAULT 'waiting_driver',
  fare_cents INT DEFAULT 0,
  started_at DATETIME NULL,
  ended_at DATETIME NULL,
  FOREIGN KEY (request_id) REFERENCES ride_requests(id) ON DELETE CASCADE,
  FOREIGN KEY (passenger_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (driver_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS payments (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  ride_id BIGINT NOT NULL,
  method ENUM('cash','card','pix') NOT NULL DEFAULT 'cash',
  status ENUM('pending','paid','failed') NOT NULL DEFAULT 'pending',
  amount_cents INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (ride_id) REFERENCES rides(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS ratings (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  ride_id BIGINT NOT NULL,
  rater_id INT NOT NULL,
  ratee_id INT NOT NULL,
  stars TINYINT NOT NULL CHECK (stars BETWEEN 1 AND 5),
  comment VARCHAR(300),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (ride_id) REFERENCES rides(id) ON DELETE CASCADE,
  FOREIGN KEY (rater_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (ratee_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS sessions (
  id CHAR(64) PRIMARY KEY,
  user_id INT NOT NULL,
  csrf_token CHAR(64) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  last_seen TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS admin_sessions (
  id CHAR(64) PRIMARY KEY,
  admin_id INT NOT NULL,
  csrf_token CHAR(64) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  last_seen TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS fare_config (
  id TINYINT PRIMARY KEY DEFAULT 1,
  base DECIMAL(10,2) NOT NULL DEFAULT 3.00,
  per_minute DECIMAL(10,2) NOT NULL DEFAULT 0.25,
  tier1_km DECIMAL(10,2) NOT NULL DEFAULT 2.00,
  tier2_km DECIMAL(10,2) NOT NULL DEFAULT 1.50,
  tier3_km DECIMAL(10,2) NOT NULL DEFAULT 1.00,
  surge_multiplier DECIMAL(10,2) NOT NULL DEFAULT 1.00
) ENGINE=InnoDB;

INSERT INTO fare_config (id) VALUES (1) ON DUPLICATE KEY UPDATE id=id;

-- Admin padrão (troque depois!)
INSERT INTO users (role,name,email,phone,password_hash)
VALUES ('admin','Admin DiMoto','admin@dimoto.local','', '$2y$10$2b22m8vKbo6d/3T5b6LwUeY7E8m3Uu7aOa1b7S2mXQx3q6nEJg1dm');
-- senha do hash acima = admin123

CREATE INDEX idx_drivers_online ON drivers(is_online);
CREATE INDEX idx_requests_status ON ride_requests(status);
CREATE INDEX idx_rides_status ON rides(status);
