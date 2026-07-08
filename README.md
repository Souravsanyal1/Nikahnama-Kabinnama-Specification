# Professional Nikahnama Management System v2.0

A clean, responsive, production-ready web application built using **PHP 8, MySQL (PDO), CSS3, JavaScript, and Bootstrap 5** to record, manage, verify, and export Islamic Marriage Certificates (Nikahnama/Kabinnama).

---

## Features
- **Dashboard Analytics:** Displays interactive cards counting total, daily, and monthly certificate registry updates.
- **Wizard Forms:** Multi-tab visual registration workflows for Groom, Bride, Witnesses, Mahr, and Registrar details with client-side block validations and server-side safety checks.
- **Serial Generators:** Automated, collision-resistant unique serial numbering templates for Certificate numbers (`NIK-YYYYMMDD-XXXX`) and Registrations (`REG-YYYYMMDD-XXXX`).
- **A4 Portrait Printable Design:** Complete with a golden-accented double Islamic border, Arabic Bismillah calligraphy titles, background watermark, signature slots, and QR codes.
- **Dynamic PDF Printing:** Click and instantly print or save as vector PDF.
- **Public Verification & QR Codes:** Allows scanning of certificate QR codes or typing the unique certificate ID publicly to authenticate authenticity instantly (hiding sensitive contact data).
- **Security Protocols:** High protection against SQL Injection (prepared PDO queries), Session Hijacking (inactivity timeouts, IP checking), XSS (output filtering helpers), and CSRF (tokens).

---

## Technical Architecture
```
project/
├── app/
│   ├── config/
│   │   └── database.php       # DB Connection wrapper (PDO)
│   ├── controllers/
│   │   ├── AuthController.php  # Authenticated logins & logouts
│   │   └── NikahController.php # CRUD operations, validation, & searches
│   ├── models/
│   │   └── Nikahnama.php       # Database representation of marriage records
│   └── helpers/
│       └── session.php        # CSRF, XSS, and secure session handlers
├── assets/
│   ├── css/
│   │   ├── style.css          # Premium accents & dashboard layout (Accent Orange: #FF8A00)
│   │   └── print.css          # High-resolution A4 portrait printing rules
│   └── js/
│       └── main.js            # Tab navigation, real-time AJAX search handler
├── database/
│   └── schema.sql             # MySQL schema and admin seed data
├── index.php                  # Landing page with public verification search bar
├── login.php                  # Secure officer login gate
├── dashboard.php              # Registry statistics, search, and activity log
├── create.php                 # Registration wizard
├── edit.php                   # Modification wizard
├── view.php                   # Record inspector and tool actions
├── print.php                  # Printable certificate layout
├── pdf.php                    # Vector-sharp PDF printing gateway
├── verify.php                 # Public authenticity verification page
└── logout.php                 # Safe session destruction
```

---

## Setup & Installation

### 1. Prerequisites
- PHP 8.0 or higher.
- MySQL Server (e.g. via XAMPP, Laragon, or standalone).
- Apache or Nginx Web Server.

### 2. Database Setup
1. Create a MySQL database named `nikahnama_db`.
2. Import the schema file located in `database/schema.sql`. You can use PHPMyAdmin or run the command below:
   ```bash
   mysql -u root -p -e "source database/schema.sql"
   ```
   *Note: The script automatically seeds a default administrator.*

### 3. Administrator / Registrar Credentials
- **Username:** `admin`
- **Password:** `admin123`
