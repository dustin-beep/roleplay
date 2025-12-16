

-- BWK BST --

CREATE TABLE patients (
  patient_id   VARCHAR(12) PRIMARY KEY,      
  pin_hash     VARCHAR(255) NOT NULL,
  first_name   VARCHAR(80)  NOT NULL,
  last_name    VARCHAR(80)  NOT NULL,
  dob          DATE NULL,
  street       VARCHAR(120) NULL,
  city         VARCHAR(80)  NULL,
  phone        VARCHAR(40)  NULL,
  email        VARCHAR(120) NULL,
  created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE employees (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  username    VARCHAR(60) NOT NULL UNIQUE,
  display_name VARCHAR(120) NOT NULL,
  role        ENUM('systemadministrator','arzt','pflegekraft','auszubildender') NOT NULL,
  pin_hash    VARCHAR(255) NOT NULL,
  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE departments (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  name        VARCHAR(120) NOT NULL UNIQUE,
  created_by  INT NULL,
  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (created_by) REFERENCES employees(id)
);

CREATE TABLE beds (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  department_id INT NOT NULL,
  bed_code      VARCHAR(30) NOT NULL,         
  is_active     TINYINT(1) NOT NULL DEFAULT 1,
  UNIQUE(department_id, bed_code),
  FOREIGN KEY (department_id) REFERENCES departments(id)
);

CREATE TABLE bed_assignments (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  patient_id   VARCHAR(12) NOT NULL,
  bed_id       INT NOT NULL,
  assigned_by  INT NOT NULL,
  assigned_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  released_at  DATETIME NULL,
  FOREIGN KEY (patient_id) REFERENCES patients(patient_id),
  FOREIGN KEY (bed_id) REFERENCES beds(id),
  FOREIGN KEY (assigned_by) REFERENCES employees(id)
);

CREATE TABLE vitals (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  patient_id    VARCHAR(12) NOT NULL,
  measured_at   DATETIME NOT NULL,
  spo2          INT NULL,            
  rr_sys        INT NULL,           
  rr_dia        INT NULL,            
  pulse         INT NULL,             
  temp_c        DECIMAL(4,1) NULL,    
  bz            INT NULL,             
  notes         TEXT NULL,
  recorded_by   INT NOT NULL,
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (patient_id) REFERENCES patients(patient_id),
  FOREIGN KEY (recorded_by) REFERENCES employees(id)
);

CREATE TABLE documents (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  patient_id    VARCHAR(12) NOT NULL,
  doc_type      ENUM('arztbrief','medplan','sonstiges') NOT NULL,
  title         VARCHAR(160) NOT NULL,
  file_name     VARCHAR(255) NOT NULL,
  file_path     VARCHAR(500) NOT NULL,           
  created_by    INT NOT NULL,
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (patient_id) REFERENCES patients(patient_id),
  FOREIGN KEY (created_by) REFERENCES employees(id)
);

CREATE TABLE medication_plans (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  patient_id   VARCHAR(12) NOT NULL,
  created_by   INT NOT NULL,
  is_active    TINYINT(1) NOT NULL DEFAULT 1,
  created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (patient_id) REFERENCES patients(patient_id),
  FOREIGN KEY (created_by) REFERENCES employees(id)
);

CREATE TABLE medication_items (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  plan_id     INT NOT NULL,
  medication  VARCHAR(140) NOT NULL,
  dose        VARCHAR(80)  NOT NULL,
  schedule    ENUM('1-3_taeglich','1-5_woechentlich','bedarfsweise') NOT NULL,
  is_active   TINYINT(1) NOT NULL DEFAULT 1,
  FOREIGN KEY (plan_id) REFERENCES medication_plans(id)
);

ALTER TABLE patients
  ADD COLUMN emergency_contact VARCHAR(200) NULL,
  ADD COLUMN blood_group       VARCHAR(10)  NULL,
  ADD COLUMN insurance         VARCHAR(120) NULL,
  ADD COLUMN gender            ENUM('m','w','d','ka') NULL;

CREATE TABLE counters (
  name  VARCHAR(40) PRIMARY KEY,
  value INT NOT NULL
);

INSERT INTO counters (name,value) VALUES ('patients', 0)
  ON DUPLICATE KEY UPDATE value=value;

ALTER TABLE documents
  ADD COLUMN doc_seq INT NOT NULL DEFAULT 0;

CREATE UNIQUE INDEX ux_docs_seq
ON documents(patient_id, doc_type, doc_seq);
