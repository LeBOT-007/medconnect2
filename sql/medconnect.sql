-- ============================================================
-- SCRIPT DE CRÉATION DE LA BASE DE DONNÉES MEDCONNECT (WAMP)
-- ============================================================

CREATE DATABASE IF NOT EXISTS medconnect_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE medconnect_db;

-- Table parente : UTILISATEURS
CREATE TABLE utilisateurs (
    id_utilisateur INT AUTO_INCREMENT,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    role ENUM('admin', 'medecin', 'patient') NOT NULL,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT pk_utilisateurs PRIMARY KEY (id_utilisateur)
) ENGINE=InnoDB;

-- Table enfant : PATIENTS
CREATE TABLE patients (
    id_patient INT AUTO_INCREMENT,
    id_utilisateur INT NOT NULL,
    telephone VARCHAR(20) NOT NULL,
    date_naissance DATE NOT NULL,
    adresse TEXT,
    CONSTRAINT pk_patients PRIMARY KEY (id_patient),
    CONSTRAINT fk_patients_utilisateurs FOREIGN KEY (id_utilisateur) 
        REFERENCES utilisateurs(id_utilisateur) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Table : SPECIALITE
CREATE TABLE specialite (
    id_specialite INT AUTO_INCREMENT,
    nom_specialite VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    CONSTRAINT pk_specialite PRIMARY KEY (id_specialite)
) ENGINE=InnoDB;

-- Table enfant : MEDECIN
CREATE TABLE medecin (
    id_medecin INT AUTO_INCREMENT,
    id_utilisateur INT NOT NULL,
    id_specialite INT NOT NULL,
    numero_ordre VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    CONSTRAINT pk_medecin PRIMARY KEY (id_medecin),
    CONSTRAINT fk_medecin_utilisateurs FOREIGN KEY (id_utilisateur) 
        REFERENCES utilisateurs(id_utilisateur) ON DELETE CASCADE,
    CONSTRAINT fk_medecin_specialite FOREIGN KEY (id_specialite) 
        REFERENCES specialite(id_specialite) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Table : DISPONIBILITÉ
CREATE TABLE disponibilite (
    id_disponibilite INT AUTO_INCREMENT,
    id_medecin INT NOT NULL,
    jour_semaine ENUM('Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche') NOT NULL,
    heure_debut TIME NOT NULL,
    heure_fin TIME NOT NULL,
    CONSTRAINT pk_disponibilite PRIMARY KEY (id_disponibilite),
    CONSTRAINT fk_disponibilite_medecin FOREIGN KEY (id_medecin) 
        REFERENCES medecin(id_medecin) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Table : RENDEZ_VOUS
CREATE TABLE rendez_vous (
    id_rdv INT AUTO_INCREMENT,
    id_medecin INT NOT NULL,
    id_patient INT NOT NULL,
    date_rdv DATE NOT NULL,
    heure_rdv TIME NOT NULL,
    statut ENUM('en_attente', 'valide', 'annule') DEFAULT 'en_attente',
    motif TEXT,
    CONSTRAINT pk_rendez_vous PRIMARY KEY (id_rdv),
    CONSTRAINT fk_rdv_medecin FOREIGN KEY (id_medecin) 
        REFERENCES medecin(id_medecin) ON DELETE CASCADE,
    CONSTRAINT fk_rdv_patients FOREIGN KEY (id_patient) 
        REFERENCES patients(id_patient) ON DELETE CASCADE
) ENGINE=InnoDB;
