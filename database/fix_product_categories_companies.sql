USE al_habib_pharmacy;

INSERT INTO categories (name)
SELECT 'Pain Relief' WHERE NOT EXISTS (SELECT 1 FROM categories WHERE name = 'Pain Relief');
INSERT INTO categories (name)
SELECT 'Vitamins' WHERE NOT EXISTS (SELECT 1 FROM categories WHERE name = 'Vitamins');
INSERT INTO categories (name)
SELECT 'Antibiotics' WHERE NOT EXISTS (SELECT 1 FROM categories WHERE name = 'Antibiotics');
INSERT INTO categories (name)
SELECT 'Allergy' WHERE NOT EXISTS (SELECT 1 FROM categories WHERE name = 'Allergy');
INSERT INTO categories (name)
SELECT 'Gastric Care' WHERE NOT EXISTS (SELECT 1 FROM categories WHERE name = 'Gastric Care');
INSERT INTO categories (name)
SELECT 'Respiratory Care' WHERE NOT EXISTS (SELECT 1 FROM categories WHERE name = 'Respiratory Care');
INSERT INTO categories (name)
SELECT 'Diabetes Care' WHERE NOT EXISTS (SELECT 1 FROM categories WHERE name = 'Diabetes Care');
INSERT INTO categories (name)
SELECT 'Heart Care' WHERE NOT EXISTS (SELECT 1 FROM categories WHERE name = 'Heart Care');
INSERT INTO categories (name)
SELECT 'Eye Care' WHERE NOT EXISTS (SELECT 1 FROM categories WHERE name = 'Eye Care');

INSERT INTO companies (name)
SELECT 'Panadol Care' WHERE NOT EXISTS (SELECT 1 FROM companies WHERE name = 'Panadol Care');
INSERT INTO companies (name)
SELECT 'Pfizer Health' WHERE NOT EXISTS (SELECT 1 FROM companies WHERE name = 'Pfizer Health');
INSERT INTO companies (name)
SELECT 'Eva Pharma' WHERE NOT EXISTS (SELECT 1 FROM companies WHERE name = 'Eva Pharma');
INSERT INTO companies (name)
SELECT 'Sanofi Care' WHERE NOT EXISTS (SELECT 1 FROM companies WHERE name = 'Sanofi Care');
INSERT INTO companies (name)
SELECT 'Bayer' WHERE NOT EXISTS (SELECT 1 FROM companies WHERE name = 'Bayer');
INSERT INTO companies (name)
SELECT 'GSK' WHERE NOT EXISTS (SELECT 1 FROM companies WHERE name = 'GSK');
INSERT INTO companies (name)
SELECT 'Equate Health' WHERE NOT EXISTS (SELECT 1 FROM companies WHERE name = 'Equate Health');
INSERT INTO companies (name)
SELECT 'Adel Germany' WHERE NOT EXISTS (SELECT 1 FROM companies WHERE name = 'Adel Germany');

UPDATE products
SET category_id = (SELECT category_id FROM categories WHERE name = 'Pain Relief' LIMIT 1)
WHERE name LIKE '%Panadol%' OR name LIKE '%Ibuprofen%' OR name LIKE '%Aspirin%' OR name LIKE '%Celecoxib%'
   OR generic_name LIKE '%Acetaminophen%' OR generic_name LIKE '%Paracetamol%' OR generic_name LIKE '%Ibuprofen%'
   OR generic_name LIKE '%Aspirin%' OR generic_name LIKE '%Celecoxib%';

UPDATE products
SET category_id = (SELECT category_id FROM categories WHERE name = 'Vitamins' LIMIT 1)
WHERE name LIKE '%Vitamin%' OR name LIKE '%Ascorbic%' OR generic_name LIKE '%Ascorbic%';

UPDATE products
SET category_id = (SELECT category_id FROM categories WHERE name = 'Antibiotics' LIMIT 1)
WHERE name LIKE '%Amoxicillin%' OR name LIKE '%Azithromycin%' OR name LIKE '%Doxycycline%'
   OR generic_name LIKE '%Amoxicillin%' OR generic_name LIKE '%Azithromycin%' OR generic_name LIKE '%Doxycycline%';

UPDATE products
SET category_id = (SELECT category_id FROM categories WHERE name = 'Allergy' LIMIT 1)
WHERE name LIKE '%Cetirizine%' OR name LIKE '%Zyrtec%' OR name LIKE '%Allergy%' OR generic_name LIKE '%Cetirizine%';

UPDATE products
SET category_id = (SELECT category_id FROM categories WHERE name = 'Gastric Care' LIMIT 1)
WHERE name LIKE '%Omeprazole%' OR name LIKE '%Prilosec%' OR name LIKE '%Gastric%' OR name LIKE '%Antacid%'
   OR generic_name LIKE '%Omeprazole%';

UPDATE products
SET category_id = (SELECT category_id FROM categories WHERE name = 'Respiratory Care' LIMIT 1)
WHERE name LIKE '%Ventolin%' OR name LIKE '%Inhaler%' OR name LIKE '%Cold%' OR name LIKE '%Flu%'
   OR generic_name LIKE '%Salbutamol%' OR generic_name LIKE '%Albuterol%' OR generic_name LIKE '%Dextromethorphan%'
   OR generic_name LIKE '%Phenylephrine%';

UPDATE products
SET category_id = (SELECT category_id FROM categories WHERE name = 'Diabetes Care' LIMIT 1)
WHERE name LIKE '%Metformin%' OR generic_name LIKE '%Metformin%';

UPDATE products
SET category_id = (SELECT category_id FROM categories WHERE name = 'Heart Care' LIMIT 1)
WHERE name LIKE '%Lisinopril%' OR generic_name LIKE '%Lisinopril%';

UPDATE products
SET category_id = (SELECT category_id FROM categories WHERE name = 'Eye Care' LIMIT 1)
WHERE name LIKE '%Cineraria%' OR name LIKE '%Eye Drop%' OR dosage_form LIKE '%Ophthalmic%';

UPDATE products
SET company_id = (SELECT company_id FROM companies WHERE name = 'Panadol Care' LIMIT 1)
WHERE name LIKE '%Panadol%' OR brand_name LIKE '%Panadol%';

UPDATE products
SET company_id = (SELECT company_id FROM companies WHERE name = 'Pfizer Health' LIMIT 1)
WHERE name LIKE '%Amoxicillin%' OR name LIKE '%Azithromycin%' OR name LIKE '%Celecoxib%' OR name LIKE '%Lisinopril%'
   OR manufacturer_name LIKE '%Pfizer%' OR brand_name LIKE '%Celebrex%';

UPDATE products
SET company_id = (SELECT company_id FROM companies WHERE name = 'Eva Pharma' LIMIT 1)
WHERE name LIKE '%Vitamin%' OR name LIKE '%Doxycycline%' OR name LIKE '%Metformin%'
   OR manufacturer_name LIKE '%Eva%';

UPDATE products
SET company_id = (SELECT company_id FROM companies WHERE name = 'Sanofi Care' LIMIT 1)
WHERE name LIKE '%Cetirizine%' OR name LIKE '%Omeprazole%' OR manufacturer_name LIKE '%Sanofi%';

UPDATE products
SET company_id = (SELECT company_id FROM companies WHERE name = 'Bayer' LIMIT 1)
WHERE name LIKE '%Aspirin%' OR name LIKE '%Ibuprofen%' OR manufacturer_name LIKE '%Bayer%';

UPDATE products
SET company_id = (SELECT company_id FROM companies WHERE name = 'GSK' LIMIT 1)
WHERE name LIKE '%Ventolin%' OR manufacturer_name LIKE '%GSK%';

UPDATE products
SET company_id = (SELECT company_id FROM companies WHERE name = 'Equate Health' LIMIT 1)
WHERE name LIKE '%Equate%' OR brand_name LIKE '%Equate%';

UPDATE products
SET company_id = (SELECT company_id FROM companies WHERE name = 'Adel Germany' LIMIT 1)
WHERE name LIKE '%Cineraria%' OR manufacturer_name LIKE '%Adel%';

UPDATE products
SET category_id = CASE MOD(product_id, 9)
    WHEN 0 THEN (SELECT category_id FROM categories WHERE name = 'Pain Relief' LIMIT 1)
    WHEN 1 THEN (SELECT category_id FROM categories WHERE name = 'Vitamins' LIMIT 1)
    WHEN 2 THEN (SELECT category_id FROM categories WHERE name = 'Antibiotics' LIMIT 1)
    WHEN 3 THEN (SELECT category_id FROM categories WHERE name = 'Allergy' LIMIT 1)
    WHEN 4 THEN (SELECT category_id FROM categories WHERE name = 'Gastric Care' LIMIT 1)
    WHEN 5 THEN (SELECT category_id FROM categories WHERE name = 'Respiratory Care' LIMIT 1)
    WHEN 6 THEN (SELECT category_id FROM categories WHERE name = 'Diabetes Care' LIMIT 1)
    WHEN 7 THEN (SELECT category_id FROM categories WHERE name = 'Heart Care' LIMIT 1)
    ELSE (SELECT category_id FROM categories WHERE name = 'Eye Care' LIMIT 1)
END
WHERE category_id IS NULL OR category_id = 0;

UPDATE products
SET company_id = CASE MOD(product_id, 8)
    WHEN 0 THEN (SELECT company_id FROM companies WHERE name = 'Panadol Care' LIMIT 1)
    WHEN 1 THEN (SELECT company_id FROM companies WHERE name = 'Pfizer Health' LIMIT 1)
    WHEN 2 THEN (SELECT company_id FROM companies WHERE name = 'Eva Pharma' LIMIT 1)
    WHEN 3 THEN (SELECT company_id FROM companies WHERE name = 'Sanofi Care' LIMIT 1)
    WHEN 4 THEN (SELECT company_id FROM companies WHERE name = 'Bayer' LIMIT 1)
    WHEN 5 THEN (SELECT company_id FROM companies WHERE name = 'GSK' LIMIT 1)
    WHEN 6 THEN (SELECT company_id FROM companies WHERE name = 'Equate Health' LIMIT 1)
    ELSE (SELECT company_id FROM companies WHERE name = 'Adel Germany' LIMIT 1)
END
WHERE company_id IS NULL OR company_id = 0;
