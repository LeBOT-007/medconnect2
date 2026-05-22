ALTER TABLE utilisateurs 
ADD COLUMN code_verification INT NULL,
ADD COLUMN est_valide TINYINT(1) DEFAULT 0;

ajout des tables 
ALTER TABLE medecin ADD COLUMN telephone VARCHAR(20) NULL;
ALTER TABLE medecin ADD COLUMN adresse TEXT NULL;

// ajouter ca dans votre de donne 
DROP TABLE IF EXISTS disponibilite;

-- 2. On crée la table avec la colonne 'jour' attendue par le ORDER BY FIELD de ton PHP
CREATE TABLE disponibilite (
    id_disponibilite INT AUTO_INCREMENT PRIMARY KEY,
    id_medecin INT NOT NULL,
    jour VARCHAR(20) NOT NULL, -- C'est cette colonne qui manquait ou avait un autre nom !
    heure_debut TIME NOT NULL,
    heure_fin TIME NOT NULL,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_medecin) REFERENCES medecin(id_medecin) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-------le lien de connection

https://precut-fetch-grapple.ngrok-free.dev/medconnect2/