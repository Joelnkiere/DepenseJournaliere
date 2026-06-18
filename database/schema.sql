CREATE DATABASE IF NOT EXISTS dailyexpense CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE dailyexpense;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL UNIQUE,
    type ENUM('besoin', 'envie', 'epargne') NOT NULL DEFAULT 'besoin',
    INDEX idx_type (type)
) ENGINE=InnoDB;

INSERT INTO categories (nom, type) VALUES
('Logement', 'besoin'),
('Alimentation', 'besoin'),
('Transport', 'besoin'),
('Sante', 'besoin'),
('Assurances', 'besoin'),
('Education', 'besoin'),
('Loisirs', 'envie'),
('Shopping', 'envie'),
('Restaurant', 'envie'),
('Voyages', 'envie'),
('Abonnements', 'envie'),
('Epargne', 'epargne'),
('Investissements', 'epargne');

CREATE TABLE IF NOT EXISTS monthly_budgets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    mois VARCHAR(7) NOT NULL,
    revenu_mensuel DECIMAL(12,2) NOT NULL DEFAULT 0,
    epargne_auto DECIMAL(12,2) NOT NULL DEFAULT 0,
    cloture TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_month (user_id, mois),
    INDEX idx_user_mois (user_id, mois)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS budget_previsions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    budget_id INT NOT NULL,
    category_id INT NOT NULL,
    montant_prevu DECIMAL(12,2) NOT NULL DEFAULT 0,
    FOREIGN KEY (budget_id) REFERENCES monthly_budgets(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    UNIQUE KEY unique_budget_cat (budget_id, category_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS expenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    budget_id INT NOT NULL,
    category_id INT NOT NULL,
    montant DECIMAL(12,2) NOT NULL,
    description VARCHAR(255) DEFAULT NULL,
    date_depense DATE NOT NULL,
    est_prevu TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (budget_id) REFERENCES monthly_budgets(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    INDEX idx_budget (budget_id),
    INDEX idx_category (category_id),
    INDEX idx_date (date_depense)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS savings_goals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    titre VARCHAR(255) NOT NULL,
    montant_cible DECIMAL(12,2) NOT NULL,
    montant_actuel DECIMAL(12,2) NOT NULL DEFAULT 0,
    date_limite DATE DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS advisor_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    mois VARCHAR(7) NOT NULL,
    conseil_type VARCHAR(50) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_mois (user_id, mois)
) ENGINE=InnoDB;