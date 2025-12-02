# Ramsung Computer Store

### User Features

- User registration and login/logout
- Browse computer products by category (laptops, desktops, graphic cards, memories, accessories)
- View product details
- Search and filter products
- Add products to shopping cart
- Remove items from cart
- Update cart quantities
- Checkout and place orders
- View order history and order details

### Admin Features

- Admin login (shared with user login)
- Admin dashboard with statistics
- Add, edit, and delete products
- View all user orders
- Update order status
- Manage user accounts
- View order details

## Features Implemented

- User registration and login/logout  
- Browse products by category  
- Product search and filter  
- View product details with **Customer Reviews & Ratings**  
- Shopping cart functionality  
- **Advanced Checkout** with Credit Card/PayPal/GPay simulation  
- Order history  
- **Dark/Light Mode** Theme Toggle  
- Admin dashboard with **Glassmorphism Design**  
- Product management (CRUD)  
- Order management  
- User management  
- Responsive design (Bootstrap 5)  
- Client-side and server-side validation  
- Secure password storage  
- SQL injection protection  
- Session management

## Other Features

- Product search and filter  
- Responsive admin dashboard with Gradients  
- Inventory auto-update after orders  
- Session-based login/cart state  
- Dynamic Product Reviews  
- Theme Customization (Dark/Light)  
- Payment Gateway Simulation

## Technologies Used

- **HTML5** - Web page structure
- **Bootstrap 5** - Responsive styling and layout
- **JavaScript** - Client-side validation and interaction
- **PHP** - Server-side processing
- **MySQL** - Database backend
- **phpMyAdmin** - Database management (via XAMPP)

## Requirements

- XAMPP (or LAMP/WAMP) with PHP 7.4+ and MySQL
- Web browser (Chrome, Firefox, Edge, etc.)

## Installation & Setup

### Step 1: Install XAMPP

1. Download and install XAMPP from https://www.apachefriends.org/
2. Start Apache and MySQL services from the XAMPP Control Panel

### Step 2: Setup Database

1. Open phpMyAdmin (http://localhost/phpmyadmin)
2. Import the `database.sql` file:
   - Click on "Import" tab
   - Choose file: `database.sql`
   - Click "Go"
3. The database `computer_store` will be created with sample data

### Step 3: Configure Database Connection

1. Open `config/database.php`
2. Update database credentials if needed (default XAMPP settings):
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');  // Empty for default XAMPP
   define('DB_NAME', 'computer_store');
   ```

### Step 4: Place Project Files

1. Copy all project files to `C:\xampp\htdocs\final website\` (or your XAMPP htdocs directory)
2. Ensure the folder structure is maintained

### Step 5: Access the Application

1. Open your web browser
2. Navigate to: `http://localhost/final website/`
3. You should see the homepage

## Default Login Credentials

### Admin Account

- **Email:** admin123@comp.com
- **Password:** 123456

### User Account

- Register a new account from the registration page

## Project Structure

```
final website/
├── admin/                  # Admin panel pages
│   ├── index.php          # Admin dashboard
│   ├── products.php        # Product management
│   ├── product_add.php     # Add new product
│   ├── product_edit.php    # Edit product
│   ├── orders.php          # Order management
│   ├── order_details.php   # Order details
│   └── users.php           # User management
├── config/                 # Configuration files
│   ├── database.php        # Database connection
│   └── session.php          # Session management
├── includes/               # Reusable components
│   ├── header.php          # Page header/navigation
│   └── footer.php          # Page footer
├── index.php               # Homepage
├── products.php            # Product listing
├── product.php             # Product details
├── cart.php                # Shopping cart
├── checkout.php            # Checkout page
├── orders.php              # User orders
├── order_details.php       # Order details
├── login.php               # User login
├── register.php            # User registration
├── logout.php              # Logout handler
├── database.sql            # Database schema
└── README.md               # This file
```

## Security Features

- **Password Hashing:** All passwords are hashed using PHP's `password_hash()` function
- **SQL Injection Protection:** All database queries use prepared statements
- **Input Validation:** Both client-side (JavaScript) and server-side (PHP) validation
- **Session Management:** Secure session handling for user authentication
- **XSS Protection:** All user inputs are escaped using `htmlspecialchars()`

## Database Schema

### Tables

- **users** - User accounts (id, name, email, password, is_admin)
- **products** - Product catalog (id, name, description, price, image_url, category, stock)
- **cart** - Shopping cart items (id, user_id, product_id, quantity)
- **orders** - Order records (id, user_id, total_price, order_date, status)
- **order_items** - Individual items in orders (id, order_id, product_id, quantity, price)

## Troubleshooting

### Database Connection Error

- Ensure MySQL is running in XAMPP Control Panel
- Check database credentials in `config/database.php`
- Verify database `computer_store` exists in phpMyAdmin

### Session Issues

- Ensure `session.php` is included in all pages
- Check PHP session configuration in `php.ini`

### Images Not Displaying

- Product images use local paths in `pics/images/`
- Ensure the `pics` directory has the correct permissions

## Notes

- The application uses local images stored in `pics/images/`
- For production, replace placeholder image URLs with actual product images
- Update database credentials for production environment
- Consider adding HTTPS for secure transactions
- Implement additional security measures for production deployment

## License

This project is created by us only for final term project.

## Author
Krish Patel (5146558)
Purv Patel (5147124)
Krishna Patel (5147675)
