# BioWell

BioWell is a PHP and MySQL insurance management application built for the client **BioWell** to conform to the business requirements of an insurance agency company.

## Project goals

- Centralize insurance agency workflows in one application
- Support role-based operations for admins and agents
- Improve handling of insurance products, quotations, and customer support tickets
- Keep operational data consistent in a MySQL-backed system

## How the solution accomplishes the goals

- Provides admin and agent views with role-based access
- Implements modules for products, quotations, tickets, announcements, and events
- Uses shared database configuration and reusable PHP modules for maintainability
- Persists core business data through MySQL schema and migration support

## What this project includes

- Admin and agent views
- Insurance products, quotations, and tickets
- Database schema and migration scripts

## Project location

The application code is in:

`php-database-project`

## Requirements

- PHP 7.4+ (or PHP 8+)
- MySQL
- Composer

## Quick start

1. Go to the project directory:
   ```bash
   cd php-database-project
   ```
2. Install dependencies:
   ```bash
   composer install
   ```
3. Create the database using the schema file:
   - `src/biowell_insurance.sql`
4. Confirm database settings in:
   - `src/config/database.php`
   - or set `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, and `DB_PASS`
5. (Optional) Run the migration script:
   ```bash
   php migrate.php
   ```
6. Start the app:
   ```bash
   php -S localhost:8000
   ```
7. Open:
   - `http://localhost:8000/index.php`
