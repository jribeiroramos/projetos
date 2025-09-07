-- criar database (se ainda não existir)
CREATE DATABASE IF NOT EXISTS projetos_app
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE projetos_app;

-- tabela de usuários
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  is_admin TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- tabela de status de projetos
CREATE TABLE IF NOT EXISTS statuses (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- tabela de partes interessadas
CREATE TABLE IF NOT EXISTS stakeholders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  type ENUM('Cliente','Fornecedor','Outro') NOT NULL DEFAULT 'Cliente',
  email VARCHAR(190) NULL,
  phone VARCHAR(40) NULL,
  notes TEXT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- tabela de projetos
CREATE TABLE IF NOT EXISTS projects (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(200) NOT NULL,
  requested_by VARCHAR(150) NULL,              -- texto livre (para compatibilidade)
  stakeholder_id INT NULL,                     -- fk opcional
  status_id INT NOT NULL,
  description TEXT NULL,
  date_idea DATE NULL,
  date_start DATE NULL,
  date_due DATE NULL,
  date_done DATE NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_projects_status FOREIGN KEY (status_id) REFERENCES statuses(id),
  CONSTRAINT fk_projects_stakeholder FOREIGN KEY (stakeholder_id) REFERENCES stakeholders(id)
) ENGINE=InnoDB;

-- índices
CREATE INDEX idx_projects_status ON projects(status_id);
CREATE INDEX idx_projects_stakeholder ON projects(stakeholder_id);

-- inserir status básicos
INSERT INTO statuses (name, sort_order, is_active) VALUES
  ('Cogitado', 1, 1),
  ('Planejado', 2, 1),
  ('Em andamento', 3, 1),
  ('Concluído', 4, 1),
  ('Cancelado', 5, 1);

-- criar usuário admin inicial (senha precisa de hash manual)
-- substitua <HASH> por resultado de: php -r "echo password_hash('admin123', PASSWORD_DEFAULT), PHP_EOL;"
INSERT INTO users (name,email,password_hash,is_admin)
VALUES ('Admin','admin@local','<HASH>',1);
