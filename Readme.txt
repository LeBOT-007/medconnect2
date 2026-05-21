ALTER TABLE utilisateurs 
ADD COLUMN code_verification INT NULL,
ADD COLUMN est_valide TINYINT(1) DEFAULT 0;