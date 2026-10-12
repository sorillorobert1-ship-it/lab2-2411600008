LABORATORY EXERCISE 5 - INVENTORY MANAGEMENT SYSTEM
Laravel 12 / XAMPP / MySQL

IMPORTANT:
This package is an overlay for the Laravel project you already created:
C:\xampp\htdocs\hotel-reservation

1. Copy the files/folders from this package into your Laravel project, preserving the folder structure.

2. Open Command Prompt:
   cd C:\xampp\htdocs\hotel-reservation

3. Install Breeze:
   composer require laravel/breeze --dev
   php artisan breeze:install blade
   npm install
   npm run build

   If Breeze asks about testing framework, use the default option.

4. Copy the files from this package AFTER Breeze installation if Breeze overwrites any
   resources/views/auth or resources/views/layouts files.

5. Create your MySQL database in phpMyAdmin, for example:
   inventory_system

6. Update .env:
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=inventory_system
   DB_USERNAME=root
   DB_PASSWORD=

7. Run:
   php artisan migrate
   php artisan db:seed --class=ProductSeeder

8. Create a test user:
   php artisan tinker

   Then:
   \App\Models\User::create([
       'name' => 'Admin User',
       'email' => 'admin@example.com',
       'password' => bcrypt('password123'),
   ]);

   exit

9. Start the server:
   php artisan serve

10. Open:
    http://127.0.0.1:8000

Login:
Email: admin@example.com
Password: password123

MAIN PAGES:
Dashboard: /dashboard
Products:  /products
Add:       /products/create

NOTE:
The laboratory handout says "Bootstrap". This implementation uses Bootstrap 5
through the official CDN in the master layout and custom CSS in public/css/inventory.css.
Authentication is protected by Laravel Breeze routes/middleware.
