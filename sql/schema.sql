-- USERS TABLE
CREATE TABLE users (
  user_id INT AUTO_INCREMENT PRIMARY KEY,
  first_name VARCHAR(50) NOT NULL,
  last_name VARCHAR(50) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- EVENTS TABLE
CREATE TABLE events (
    event_id INT AUTO_INCREMENT PRIMARY KEY,
    host_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    description TEXT,
    location VARCHAR(255) NOT NULL,
    start_time DATETIME NOT NULL,
    end_time DATETIME,
    theme VARCHAR(100),
    image_path VARCHAR(255),   
    is_canceled TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_events_host
        FOREIGN KEY (host_id) REFERENCES users(user_id)
        ON DELETE CASCADE
);

-- RSVPS TABLE
CREATE TABLE rsvps (
  rsvp_id INT AUTO_INCREMENT PRIMARY KEY,
  event_id INT NOT NULL,
  user_id INT NOT NULL,
  status ENUM('yes','no','maybe') NOT NULL DEFAULT 'maybe',
  responded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_rsvps_event
    FOREIGN KEY (event_id) REFERENCES events(event_id)
    ON DELETE CASCADE,
  CONSTRAINT fk_rsvps_user
    FOREIGN KEY (user_id) REFERENCES users(user_id)
    ON DELETE CASCADE,
  CONSTRAINT uc_event_user UNIQUE (event_id, user_id)
);
