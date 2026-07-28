USE al_habib_pharmacy;

UPDATE products
SET image_url = 'assets/images/products/equate-daytime-cold-flu.webp'
WHERE name LIKE '%Equate%'
   OR brand_name LIKE '%Equate%'
   OR name LIKE '%DayTime Cold%'
   OR name LIKE '%DayQuil%';

UPDATE products
SET image_url = 'assets/images/products/panadol.jpg'
WHERE name LIKE '%Panadol%'
   OR brand_name LIKE '%Panadol%'
   OR name LIKE '%Tylenol%'
   OR brand_name LIKE '%Tylenol%'
   OR generic_name LIKE '%Paracetamol%'
   OR generic_name LIKE '%Acetaminophen%';

UPDATE products
SET image_url = 'assets/images/products/vitamin-c-500mg.jpg'
WHERE name LIKE '%Vitamin C%'
   OR name LIKE '%Redoxon%'
   OR generic_name LIKE '%Ascorbic%'
   OR brand_name LIKE '%Redoxon%';

UPDATE products
SET image_url = 'assets/images/products/amoxicillin-500mg.jpg'
WHERE name LIKE '%Amoxicillin%'
   OR name LIKE '%Amoxil%'
   OR generic_name LIKE '%Amoxicillin%'
   OR brand_name LIKE '%Amoxil%';

UPDATE products
SET image_url = 'assets/images/products/ibuprofen-400mg.jpg'
WHERE name LIKE '%Ibuprofen%'
   OR name LIKE '%Advil%'
   OR name LIKE '%Brufen%'
   OR name LIKE '%Nurofen%'
   OR generic_name LIKE '%Ibuprofen%';

UPDATE products
SET image_url = 'assets/images/products/aspirin.jpg'
WHERE name LIKE '%Aspirin%'
   OR generic_name LIKE '%Aspirin%'
   OR generic_name LIKE '%Acetylsalicylic%';

UPDATE products
SET image_url = 'assets/images/products/cetirizine.jpg'
WHERE name LIKE '%Cetirizine%'
   OR name LIKE '%Zyrtec%'
   OR name LIKE '%Allergy%'
   OR generic_name LIKE '%Cetirizine%'
   OR dosage_form LIKE '%Antihistamine%';

UPDATE products
SET image_url = 'assets/images/products/azithromycin.jpg'
WHERE name LIKE '%Azithromycin%'
   OR name LIKE '%Zithromax%'
   OR generic_name LIKE '%Azithromycin%'
   OR brand_name LIKE '%Zithromax%';

UPDATE products
SET image_url = 'assets/images/products/omeprazole.jpg'
WHERE name LIKE '%Omeprazole%'
   OR name LIKE '%Prilosec%'
   OR name LIKE '%Antacid%'
   OR name LIKE '%Gastric%'
   OR name LIKE '%ENO%'
   OR generic_name LIKE '%Omeprazole%'
   OR dosage_form LIKE '%Antacid%';

UPDATE products
SET image_url = 'assets/images/products/ventolin-inhaler.jpg'
WHERE name LIKE '%Ventolin%'
   OR name LIKE '%Albuterol%'
   OR name LIKE '%Salbutamol%'
   OR generic_name LIKE '%Albuterol%'
   OR generic_name LIKE '%Salbutamol%'
   OR dosage_form LIKE '%Inhal%';

UPDATE products
SET image_url = 'assets/images/products/metformin-500mg.jpg'
WHERE name LIKE '%Metformin%'
   OR name LIKE '%Siofor%'
   OR generic_name LIKE '%Metformin%';

UPDATE products
SET image_url = 'assets/images/products/lisinopril-20mg.jpg'
WHERE name LIKE '%Lisinopril%'
   OR generic_name LIKE '%Lisinopril%';

UPDATE products
SET image_url = 'assets/images/products/doxycycline-100mg.jpg'
WHERE name LIKE '%Doxycycline%'
   OR generic_name LIKE '%Doxycycline%';

UPDATE products
SET image_url = 'assets/images/products/celecoxib.avif'
WHERE name LIKE '%Celecoxib%'
   OR name LIKE '%Celebrex%'
   OR generic_name LIKE '%Celecoxib%'
   OR brand_name LIKE '%Celebrex%';

UPDATE products
SET image_url = 'assets/images/products/cineraria-eye-drops.jpg'
WHERE name LIKE '%Cineraria%'
   OR generic_name LIKE '%Cineraria%'
   OR brand_name LIKE '%Cineraria%';

UPDATE products
SET image_url = 'assets/images/placeholders/default-product.png'
WHERE image_url IS NULL
   OR image_url = ''
   OR image_url LIKE '%default-medicine.svg%'
   OR image_url LIKE '%generic-capsules%';
