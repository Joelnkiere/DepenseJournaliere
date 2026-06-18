CREATE DATABASE IF NOT EXISTS dailyexpense CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE dailyexpense;

SET FOREIGN_KEY_CHECKS = 0;
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    theme ENUM('dark', 'light') NOT NULL DEFAULT 'dark',
    reset_token VARCHAR(64) DEFAULT NULL,
    reset_expires DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL UNIQUE,
    type ENUM('besoin', 'envie', 'epargne') NOT NULL DEFAULT 'besoin',
    INDEX idx_type (type)
) ENGINE=InnoDB;

INSERT IGNORE INTO categories (nom, type) VALUES
('Logement', 'besoin'), ('Alimentation', 'besoin'), ('Transport', 'besoin'),
('Sante', 'besoin'), ('Assurances', 'besoin'), ('Education', 'besoin'),
('Loisirs', 'envie'), ('Shopping', 'envie'), ('Restaurant', 'envie'),
('Voyages', 'envie'), ('Abonnements', 'envie'),
('Epargne', 'epargne'), ('Investissements', 'epargne');

CREATE TABLE IF NOT EXISTS revenue_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL UNIQUE,
    type ENUM('actif', 'passif') NOT NULL DEFAULT 'actif'
) ENGINE=InnoDB;

INSERT IGNORE INTO revenue_categories (nom, type) VALUES
('Salaire', 'actif'), ('Freelance', 'actif'), ('Investissements', 'passif'),
('Revenus locatifs', 'passif'), ('Prestations sociales', 'actif'),
('Ventes', 'actif'), ('Autres revenus', 'actif');

CREATE TABLE IF NOT EXISTS accounts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    nom VARCHAR(255) NOT NULL,
    type ENUM('compte_courant','epargne','investissement','immobilier','crypto','especes','autre') NOT NULL DEFAULT 'compte_courant',
    solde_initial DECIMAL(12,2) NOT NULL DEFAULT 0,
    solde_actuel DECIMAL(12,2) NOT NULL DEFAULT 0,
    devise VARCHAR(3) NOT NULL DEFAULT 'EUR',
    couleur VARCHAR(7) DEFAULT '#0d6efd',
    icone VARCHAR(50) DEFAULT 'wallet2',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS account_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    account_id INT NOT NULL,
    type ENUM('depot','retrait','virement') NOT NULL,
    montant DECIMAL(12,2) NOT NULL,
    description VARCHAR(255) DEFAULT NULL,
    date_transaction DATE NOT NULL,
    destination_account_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE CASCADE,
    FOREIGN KEY (destination_account_id) REFERENCES accounts(id) ON DELETE SET NULL,
    INDEX idx_account (account_id),
    INDEX idx_date (date_transaction)
) ENGINE=InnoDB;

-- Migration: keep monthly_budgets for backward compatibility
CREATE TABLE IF NOT EXISTS monthly_budgets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    mois VARCHAR(7) NOT NULL,
    revenu_mensuel DECIMAL(12,2) NOT NULL DEFAULT 0,
    epargne_auto DECIMAL(12,2) NOT NULL DEFAULT 0,
    cloture TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_month (user_id, mois)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS budget_periods (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    nom VARCHAR(255) DEFAULT NULL,
    type ENUM('daily','weekly','monthly','yearly','custom') NOT NULL DEFAULT 'monthly',
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    revenu_total DECIMAL(12,2) NOT NULL DEFAULT 0,
    cloture TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_dates (user_id, start_date, end_date)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS budget_previsions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    budget_id INT NOT NULL,
    category_id INT NOT NULL,
    montant_prevu DECIMAL(12,2) NOT NULL DEFAULT 0,
    FOREIGN KEY (budget_id) REFERENCES budget_periods(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    UNIQUE KEY unique_budget_cat (budget_id, category_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS revenues (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    category_id INT NOT NULL,
    budget_id INT DEFAULT NULL,
    account_id INT DEFAULT NULL,
    montant DECIMAL(12,2) NOT NULL,
    description VARCHAR(255) DEFAULT NULL,
    date_revenu DATE NOT NULL,
    est_recurrent TINYINT(1) NOT NULL DEFAULT 0,
    frequence VARCHAR(20) DEFAULT NULL,
    source VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES revenue_categories(id) ON DELETE CASCADE,
    FOREIGN KEY (budget_id) REFERENCES budget_periods(id) ON DELETE SET NULL,
    FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE SET NULL,
    INDEX idx_user_date (user_id, date_revenu)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS expenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    budget_id INT NOT NULL,
    period_id INT DEFAULT NULL,
    category_id INT NOT NULL,
    account_id INT DEFAULT NULL,
    montant DECIMAL(12,2) NOT NULL,
    description VARCHAR(255) DEFAULT NULL,
    date_depense DATE NOT NULL,
    est_prevu TINYINT(1) NOT NULL DEFAULT 0,
    image_path VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (budget_id) REFERENCES monthly_budgets(id) ON DELETE CASCADE,
    FOREIGN KEY (period_id) REFERENCES budget_periods(id) ON DELETE SET NULL,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE SET NULL,
    INDEX idx_budget (budget_id),
    INDEX idx_period (period_id),
    INDEX idx_category (category_id),
    INDEX idx_date (date_depense),
    INDEX idx_account (account_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS savings_goals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    account_id INT DEFAULT NULL,
    titre VARCHAR(255) NOT NULL,
    montant_cible DECIMAL(12,2) NOT NULL,
    montant_actuel DECIMAL(12,2) NOT NULL DEFAULT 0,
    date_limite DATE DEFAULT NULL,
    auto_save_type ENUM('none','percentage','fixed') NOT NULL DEFAULT 'none',
    auto_save_value DECIMAL(12,2) NOT NULL DEFAULT 0,
    auto_save_frequence VARCHAR(20) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE SET NULL,
    INDEX idx_user (user_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS recurring_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    category_id INT NOT NULL,
    account_id INT DEFAULT NULL,
    montant DECIMAL(12,2) NOT NULL,
    description VARCHAR(255) DEFAULT NULL,
    frequence ENUM('mensuel','bimestriel','trimestriel','annuel') NOT NULL DEFAULT 'mensuel',
    jour_execution INT NOT NULL DEFAULT 1,
    actif TINYINT(1) NOT NULL DEFAULT 1,
    prochaine_execution DATE DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_prochaine (prochaine_execution)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type VARCHAR(50) NOT NULL DEFAULT 'info',
    titre VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    lu TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_lu (user_id, lu),
    INDEX idx_created (created_at)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS budget_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    nom VARCHAR(255) NOT NULL,
    description TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS budget_template_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    template_id INT NOT NULL,
    category_id INT NOT NULL,
    montant_prevu DECIMAL(12,2) NOT NULL DEFAULT 0,
    FOREIGN KEY (template_id) REFERENCES budget_templates(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    UNIQUE KEY unique_template_cat (template_id, category_id)
) ENGINE=InnoDB;



CREATE TABLE IF NOT EXISTS financial_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    period_id INT DEFAULT NULL,
    type_planner VARCHAR(50) NOT NULL DEFAULT 'rule',
    plan_data JSON NOT NULL,
    actif TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (period_id) REFERENCES budget_periods(id) ON DELETE SET NULL,
    INDEX idx_user_actif (user_id, actif)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS plan_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    plan_id INT NOT NULL,
    type VARCHAR(50) NOT NULL,
    goal_id INT DEFAULT NULL,
    label VARCHAR(255) NOT NULL,
    montant DECIMAL(12,2) NOT NULL DEFAULT 0,
    priorite INT NOT NULL DEFAULT 5,
    execute TINYINT(1) NOT NULL DEFAULT 0,
    execute_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (plan_id) REFERENCES financial_plans(id) ON DELETE CASCADE,
    FOREIGN KEY (goal_id) REFERENCES savings_goals(id) ON DELETE SET NULL,
    INDEX idx_plan (plan_id),
    INDEX idx_execute (execute)
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;