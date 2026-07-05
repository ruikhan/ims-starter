# IMS — Inventory Management System
**Stack:** PHP · MySQL · XAMPP · Vanilla CSS/JS

---

## Quick Setup (XAMPP)

### 1 — Copy files
Place the entire `ims-starter/` folder inside:
```
D:\Xampp\htdocs\ims-starter\
```

### 2 — Create the database
1. Open **phpMyAdmin** → `http://localhost/phpmyadmin`
2. Click **Import** → choose `database/ims.sql` → click **Go**

### 3 — Check DB credentials
Open `config/db.php` and verify:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'ims_db');
define('DB_USER', 'root');
define('DB_PASS', '');   // Your XAMPP MySQL password
```

### 4 — Open in browser
```
http://localhost/ims-starter/
```

---

## Default Login
| Email | Password | Role |
|---|---|---|
| admin@ims.com | Admin@123 | Admin |
| maria@ims.com | Admin@123 | Staff |

---

## File Structure
```
ims-starter/
│
├── config/
│   └── db.php                  # PDO database connection
│
├── includes/
│   ├── auth.php                # Session, login guard, role check
│   └── helpers.php             # Utility functions (updateStockStatus, formatMoney…)
│
├── partials/
│   ├── header.php              # Sidebar + topbar layout shell
│   └── footer.php              # Closing tags + JS include
│
├── assets/
│   ├── css/style.css           # Full design system
│   └── js/app.js               # UI helpers, confirm dialogs
│
├── products/
│   ├── index.php               # List + search + filter
│   ├── add.php                 # Add product form
│   ├── edit.php                # Edit product form
│   └── delete.php              # Delete handler
│
├── categories/
│   ├── index.php               # List categories
│   ├── add.php                 # Add form
│   ├── edit.php                # Edit form
│   └── delete.php              # Delete handler
│
├── stock/
│   ├── index.php               # Stock-in / Stock-out forms
│   └── history.php             # Transaction log
│
├── users/
│   ├── index.php               # User list (admin only)
│   ├── add.php                 # Add user
│   ├── edit.php                # Edit user + change password
│   └── delete.php              # Delete handler
│
├── database/
│   └── ims.sql                 # Full schema + seed data
│
├── index.php                   # Dashboard
├── login.php                   # Login page
├── logout.php                  # Session destroy + redirect
└── .htaccess                   # Security rules
```

---

## Role Permissions
| Feature | Admin | Staff |
|---|---|---|
| View Dashboard | ✅ | ✅ |
| View Products | ✅ | ✅ |
| Add/Edit/Delete Products | ✅ | ❌ |
| View Categories | ✅ | ✅ |
| Add/Edit/Delete Categories | ✅ | ❌ |
| Stock In / Stock Out | ✅ | ✅ |
| View Transaction History | ✅ | ✅ |
| User Management | ✅ | ❌ |

---

## Key Behaviours
- **Auto stock status** — `updateStockStatus()` recalculates `in_stock / low_stock / out_of_stock` after every transaction
- **Negative stock guard** — stock-out is blocked server-side if quantity would go below 0
- **Duplicate SKU check** — enforced at DB level (UNIQUE) and in form validation
- **Root admin protection** — user ID 1 cannot be deleted or demoted
- **Flash messages** — stored in `$_SESSION['flash']`, shown once then cleared
