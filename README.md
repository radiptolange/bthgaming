# BTH Gaming Esports - Professional Tournament Platform

BTH Gaming is an elite esports tournament management system designed for gaming communities to host, register, and track professional competitions.

## Technical Specifications
- **Backend**: PHP 8.1+ (Core PHP, OOP)
- **Database**: MySQL / MariaDB (12 core tables)
- **Frontend**: Bootstrap 5, Custom CSS3 (Neon Gaming Theme), Vanilla JavaScript
- **Interactivity**: AJAX (Live match polling)
- **Security**: PDO Prepared Statements, CSRF Protection, Password Hashing (Bcrypt)

---

## Features
- **Pro Dashboard**: Real-time statistics and elite management portal.
- **Tournament Lifecycle**: Creation -> Registration -> Approval -> Brackets -> Scoring.
- **Dynamic Brackets**: Support for Single Elimination (R16, QF, SF, Final).
- **Manual Overrides**: Full admin control over match-ups and team placements.
- **Live Scores**: AJAX polling allows visitors to see live results without page refreshes.
- **Media Gallery**: Integrated system for tournament and community highlights.
- **Team/Player Profiles**: Detailed performance tracking and rosters.
- **News System**: Post announcements and updates for the community.
- **Contact System**: Direct communication with community managers via the dashboard.

---

## Installation & Setup

### 1. Localhost (XAMPP/WAMP)
1. Copy the `bth-gaming` folder to your `htdocs` or `www` directory.
2. Ensure your MySQL server is running.
3. Access `http://localhost/bth-gaming/setup.php` in your browser.
4. Follow the on-screen instructions to initialize the database.

### 2. Shared Hosting (InfinityFree / cPanel)
1. Upload all files to your `public_html` or equivalent directory.
2. Create a MySQL database and user in your hosting control panel.
3. Import the `database.sql` file via phpMyAdmin.
4. Update the credentials in `config/database.php`.
5. Ensure the `/uploads` directory and its subfolders have write permissions (777 or 755).

---

## Audit & Bug Fix Log (v11.0)
- **Fixed SQL Errors**: Resolved "Base table not found" by synchronizing code with the advanced 12-table schema.
- **Fixed Link Integrity**: Corrected all dead links and standardized project paths.
- **Standardized Terminology**: Migrated from generic 'events' and 'fixtures' to 'Tournaments' and 'Matches'.
- **Fixed Image Uploads**: Implemented automatic directory creation (mkdir) to prevent "failed to open stream" errors.
- **Implemented Security**: Added CSRF protection to all POST forms (Contact, Login, Brackets).
- **Improved UI Visibility**: Added high-contrast CSS overrides for the admin panel and modals.
- **Functional Portal**: Completed missing management pages for Teams, Players, News, and Gallery.
- **Resolved Pathing**: Standardized file structure for standard shared hosting compatibility.

---

## Admin Credentials
- **Username**: `admin`
- **Password**: `admin123`

---
© 2024 Brother And The Hood Gaming Community. Professional Esports Management.
