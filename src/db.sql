CREATE TABLE print_jobs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    filename VARCHAR(255),
    extracted_path VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE print_plates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    print_job_id INT NOT NULL,
    plate_index INT,
    gcode_filename VARCHAR(255),
    FOREIGN KEY (print_job_id) REFERENCES print_jobs(id)
);

CREATE TABLE plate_filaments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    plate_id INT NOT NULL,
    filament_id INT,
    filament_type VARCHAR(50),
    filament_color VARCHAR(20),
    used_g DECIMAL(8,3),
    used_m DECIMAL(8,3),
    tray_info_idx VARCHAR(20),
    FOREIGN KEY (plate_id) REFERENCES print_plates(id)
);

CREATE TABLE printers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100),
  serial_number VARCHAR(64),
  mqtt_user VARCHAR(64),
  mqtt_access_code VARCHAR(64),
  ip_address VARCHAR(64),
  status VARCHAR(32),
  last_seen DATETIME
);

CREATE TABLE printer_ams_slots (
  id INT AUTO_INCREMENT PRIMARY KEY,
  printer_id INT,
  slot_index INT,
  filament_type VARCHAR(32),
  filament_color VARCHAR(16),
  is_external TINYINT(1),
  updated_at DATETIME,
  FOREIGN KEY (printer_id) REFERENCES printers(id)
);

CREATE TABLE print_queue (
  id INT AUTO_INCREMENT PRIMARY KEY,
  printer_id INT NULL,
  job_id INT,
  plate_id INT,
  status ENUM('queued','assigned','printing','completed','failed'),
  requested_at DATETIME,
  started_at DATETIME,
  completed_at DATETIME,
  FOREIGN KEY (printer_id) REFERENCES printers(id)
);
