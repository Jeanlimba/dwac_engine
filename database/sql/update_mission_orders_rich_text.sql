ALTER TABLE mission_orders 
ADD COLUMN signatory_name VARCHAR(255) DEFAULT 'NGUBI Mac',
ADD COLUMN signatory_role VARCHAR(255) DEFAULT 'Managing Director',
ADD COLUMN sign_city VARCHAR(255) DEFAULT 'Kinshasa',
ADD COLUMN footer_text TEXT,
ADD COLUMN agency_name VARCHAR(255),
ADD COLUMN agency_address TEXT,
ADD COLUMN agency_phone VARCHAR(255);

UPDATE mission_orders SET footer_text = 'Nous prions aux Autorités Politico-Administratives, militaires et policières de faciliter libre passage, d’apporter assistance et accorder l’immunité liée aux fonctions du porteur de ce document.' WHERE footer_text IS NULL;
