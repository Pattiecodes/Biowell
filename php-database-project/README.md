# PHP Database Project

This project is designed to demonstrate how to create a MySQL database using PHP. It includes the necessary files to connect to a MySQL server and execute SQL statements to create a new database.

## Project Structure

```
php-database-project
├── index.php                     # Main entry point and page router
├── admin_*.php                  # Admin dashboard/pages (tickets, quotations, products, etc.)
├── agent_*.php                  # Agent dashboard/pages (tickets, quotations, profile, etc.)
├── biowell.css                  # App styling
├── biowell.js                   # Client-side interactions
├── migrate.php                  # Idempotent schema/data migration runner
├── src
│   ├── config/database.php      # Database connection settings
│   ├── db.php                   # Shared DB bootstrap/helpers
│   ├── create_database.php      # Database creation script
│   └── biowell_insurance.sql    # SQL schema and seed data
├── scripts/import-db.ps1        # PowerShell helper to import database
├── composer.json                # Composer configuration
└── README.md                    # Project documentation
```

## Requirements

- PHP 7.0 or higher
- MySQL server

## Credits

By Team Albania

Members:
- James Patrick De Ocampo
