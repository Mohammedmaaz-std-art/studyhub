CREATE TABLE users (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  username      VARCHAR(100) NOT NULL,
  email         VARCHAR(150) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role          ENUM('beginner', 'intermediate') DEFAULT 'beginner',
  created_at    DATETIME DEFAULT NOW()
);

CREATE TABLE subjects (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  user_id     INT NOT NULL,
  name        VARCHAR(200) NOT NULL,
  description TEXT,
  difficulty  ENUM('basic', 'intermediate', 'advanced') DEFAULT 'basic',
  color_tag   VARCHAR(20) DEFAULT '#4A90D9',
  created_at  DATETIME DEFAULT NOW(),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE tasks (
  id                INT AUTO_INCREMENT PRIMARY KEY,
  user_id           INT NOT NULL,
  subject_id        INT NOT NULL,
  title             VARCHAR(300) NOT NULL,
  description       TEXT,
  due_date          DATE,
  priority          ENUM('low', 'medium', 'high') DEFAULT 'medium',
  estimated_minutes INT DEFAULT 25,
  actual_minutes    INT DEFAULT 0,
  is_done           TINYINT(1) DEFAULT 0,
  created_at        DATETIME DEFAULT NOW(),
  FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE,
  FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
);

CREATE TABLE steps (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  task_id    INT NOT NULL,
  step_text  VARCHAR(500) NOT NULL,
  step_order INT DEFAULT 1,
  is_done    TINYINT(1) DEFAULT 0,
  FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE
);

CREATE TABLE quiz_attempts (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  user_id      INT NOT NULL,
  subject_id   INT NOT NULL,
  score        INT NOT NULL,
  total        INT NOT NULL,
  passed       TINYINT(1) DEFAULT 0,
  attempted_at DATETIME DEFAULT NOW(),
  FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE,
  FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
);

CREATE TABLE progress_logs (
  id               INT AUTO_INCREMENT PRIMARY KEY,
  user_id          INT NOT NULL,
  task_id          INT NOT NULL,
  duration_minutes INT DEFAULT 0,
  completed_at     DATETIME DEFAULT NOW(),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE
);

CREATE TABLE certificates (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  user_id      INT NOT NULL,
  subject_id   INT NOT NULL,
  subject_name VARCHAR(200) NOT NULL,
  score        INT NOT NULL,
  issued_at    DATETIME DEFAULT NOW(),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

ALTER TABLE users ADD COLUMN course VARCHAR(100) DEFAULT NULL;
ALTER TABLE users ADD COLUMN onboarding_done TINYINT(1) DEFAULT 0;