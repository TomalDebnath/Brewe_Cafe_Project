# Brewé Café — PHP + MySQL University Project

A responsive cafe ordering demo built with HTML, CSS, JavaScript, PHP and MySQL.

## Features
- Customer login and registration (passwords stored with PHP `password_hash`)
- Product menu filtered by Coffee, Tea, Pastry and Dessert
- Cart add, increment, decrement and remove
- Stock-aware availability labels and server-side stock validation
- Checkout with Cash, Card and bKash demonstration choices
- Order and order-item records saved to MySQL
- Admin dashboard to update product stock and availability
- PDO prepared statements, CSRF checks and database transaction for checkout

## Setup with XAMPP
1. Copy the `Brewe_Cafe_Project` folder into `C:\xampp\htdocs\`.
2. Start Apache and MySQL from XAMPP Control Panel.
3. Open phpMyAdmin at `http://localhost/phpmyadmin`.
4. Import `database/original_database.sql` from this project.
5. Select the `north_end_cafe` database and import `database/migration.sql` once.
6. Then import `database/sample_images.sql` to use the supplied reference photos for menu items.
7. Open `config.php`. The default XAMPP settings are `root` with an empty password. Change them if yours differ.
8. If your folder name is not `Brewe_Cafe_Project`, update `BASE_URL` in `config.php` and the image path prefix in `database/sample_images.sql` before importing it.
9. Visit `http://localhost/Brewe_Cafe_Project/`.

## Demo login
- Customer: `customer` / `customer123`
- Admin: `admin` / `admin123`

The SQL seed account hashes are supplied in the original database file.

## Important payment note
Card and bKash are currently demonstration payment-method selections only. The application does not collect card numbers, PINs or OTPs and does not process real payments. Real payments require an approved payment gateway integration and merchant credentials.

## Important inventory note
The migration adds `stock_quantity`. A product is shown as unavailable when `available = 0` or stock is zero. Checkout checks stock again inside a database transaction before reducing inventory.
