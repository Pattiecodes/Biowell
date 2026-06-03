# PHP Database Project

This project is designed to demonstrate how to create a MySQL database using PHP. It includes the necessary files to connect to a MySQL server and execute SQL statements to create a new database.

## Project Structure

```
php-database-project
├── index.php                     # Main app entry point and page routing
├── admin_*.php                   # Admin pages (products, tickets, quotations, etc.)
├── agent_*.php                   # Agent pages (home, profile, products, tickets, etc.)
├── admin_navbar.php              # Admin navigation
├── agent_navbar.php              # Agent navigation
├── biowell.css                   # Custom styles
├── biowell.js                    # Client-side UI behavior
├── migrate.php                   # Migration runner
├── src
│   ├── biowell_insurance.sql     # Main database schema/data SQL
│   ├── create_database.php       # Database creation helper
│   ├── db.php                    # Shared DB connection helpers
│   ├── config
│   │   └── database.php          # Environment/local DB configuration
│   └── migrations
│       └── 001_add_users_created_at.sql
├── scripts
│   └── import-db.ps1             # PowerShell DB import helper
├── composer.json                 # Composer configuration
└── README.md                     # Project documentation
```

## Requirements

- PHP 7.0 or higher
- MySQL server

## Credits

By Team Albania

Members:
- James Patrick De Ocampo
