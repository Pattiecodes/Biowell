# BioWell Insurance Management Application

This application was developed for **BioWell** to meet the business requirements of an insurance agency company.

## Project Goals

- Centralize insurance operations in one web application.
- Support role-based workflows for admins and agents.
- Streamline day-to-day tasks such as managing products, quotations, tickets, announcements, and events.
- Maintain a reliable MySQL-backed data layer for business records.

## How the Solution Accomplishes These Goals

- Implements dedicated admin and agent pages for operational tasks (products, quotations, tickets, and profile/workflow screens).
- Uses session-based role access to separate responsibilities between user types.
- Connects application features to a shared MySQL database via reusable configuration in `src/config/database.php`.
- Organizes the project into focused PHP modules to support maintainability and future enhancements.

## Requirements

- PHP 7.4 or higher
- MySQL server
- Composer

## Credits

By Team Albania

Members:
- James Patrick De Ocampo
