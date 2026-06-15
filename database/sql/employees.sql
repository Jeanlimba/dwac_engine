CREATE TABLE employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    matricule VARCHAR(255),
    nom VARCHAR(255) NOT NULL,
    postnom VARCHAR(255),
    prenom VARCHAR(255),
    genre VARCHAR(50),
    etat_civil VARCHAR(50),
    date_naissance DATE,
    lieu_naissance VARCHAR(255),
    nationalite VARCHAR(255),
    adresse VARCHAR(255),
    ville VARCHAR(255),
    province VARCHAR(255),
    telephone_personnel VARCHAR(255),
    telephone_professionnel VARCHAR(255),
    email_personnel VARCHAR(255),
    email VARCHAR(255) NOT NULL,
    nombre_enfants INT,
    personne_contact VARCHAR(255),
    telephone_contact VARCHAR(255),
    departement_id INT,
    date_embauche DATE,
    statut VARCHAR(255),
    photo VARCHAR(255),
    cv VARCHAR(255),
    password VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);

ALTER TABLE employees
ADD COLUMN role VARCHAR(50) NOT NULL DEFAULT 'employee';

-- Available roles:
-- 'employee': Displayed as '--'
-- 'superviseur': Superviseur
-- 'manager': Manager
