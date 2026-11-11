USE north_end_cafe;
UPDATE menu_items SET image = CONCAT('/Brewe_Cafe_Project/assets/images/', CASE
 WHEN name LIKE '%Cheese%' OR name LIKE '%Cake%' OR name LIKE '%Tiramisu%' OR name LIKE '%Brownie%' THEN 'cheesecake.jpg'
 WHEN name LIKE '%Croissant%' OR name LIKE '%Danish%' OR name LIKE '%Muffin%' THEN 'croissant.jpg'
 WHEN category = 'Tea' THEN 'tea.jpg'
 WHEN name LIKE '%Americano%' OR name LIKE '%Espresso%' THEN 'americano.jpg'
 ELSE 'latte.jpg' END);
