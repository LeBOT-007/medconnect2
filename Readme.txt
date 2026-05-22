ALTER TABLE utilisateurs 
ADD COLUMN code_verification INT NULL,
ADD COLUMN est_valide TINYINT(1) DEFAULT 0;

-- Compte administrateur par défaut (mot de passe : password)
INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role) 
VALUES ('Admin', 'Super', 'moh.tyler110@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uXkWCiw7W', 'admin');
mot de passe :password