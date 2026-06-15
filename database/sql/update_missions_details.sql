ALTER TABLE missions 
ADD COLUMN duration_days INT DEFAULT 0,
ADD COLUMN hours_per_day DECIMAL(5, 2) DEFAULT 0.00,
ADD COLUMN means_of_transport VARCHAR(100);
