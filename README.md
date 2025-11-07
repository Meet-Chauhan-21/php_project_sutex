# VidyaGuru College - Admission Management System

A comprehensive web-based admission management system for VidyaGuru College with Google OAuth authentication, student profile management, and online admission form submission.

## 🚀 Features

- **Google OAuth 2.0 Authentication** - Secure login with Google accounts
- **Student Profile Management** - View and edit profile information
- **Online Admission Form** - Submit admission applications for various programs
- **Admin Dashboard** - Manage applications and student data
- **Responsive Design** - Works on desktop, tablet, and mobile devices
- **Modern UI** - Clean and professional interface with blue gradient theme

## 📋 Programs Offered

- Bachelor of Computer Applications (BCA)
- Bachelor of Business Administration (BBA)
- Bachelor of Commerce (BCom)
- Bachelor of Teaching (BTeach/B.Ed)
- Master of Business Administration (MBA)
- Master of Computer Applications (MCA)

## 🛠️ Installation & Setup

### Prerequisites

- **XAMPP** (Apache + MySQL + PHP 7.4+)
- **Google Cloud Console** account for OAuth credentials
- **GitHub** account (optional, for version control)

### Step 1: Clone the Repository

```bash
git clone https://github.com/Mohit7276/php_project_sutex.git
cd php_project_sutex
```

### Step 2: Setup Google OAuth Credentials

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select existing one
3. Enable Google+ API
4. Create OAuth 2.0 credentials:
   - Application type: Web application
   - Authorized redirect URIs: `http://localhost:8081/php/google_callback.php`
5. Copy `php/google_credentials.example.php` to `php/google_credentials.php`
6. Add your credentials:

```php
<?php
define('GOOGLE_CLIENT_ID', 'YOUR_CLIENT_ID_HERE');
define('GOOGLE_CLIENT_SECRET', 'YOUR_CLIENT_SECRET_HERE');
define('GOOGLE_REDIRECT_URI', 'http://localhost:8081/php/google_callback.php');
?>
```

### Step 3: Database Setup

1. Start XAMPP (Apache + MySQL)
2. Open phpMyAdmin: `http://localhost/phpmyadmin`
3. Run the database creation script:
   - Visit: `http://localhost:8081/create_vidhyaguru_db.php`
   - Or manually import: `create_vidhyaguru_db.sql`

### Step 4: Configure Database Connection

Edit `php/config.php` if needed:

```php
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'vidhyaguru_db';
$DB_PORT = 3306;
```

### Step 5: Start the Application

1. Move the project to XAMPP htdocs: `C:\xampp\htdocs\phpwebsite`
2. Start Apache and MySQL in XAMPP Control Panel
3. Visit: `http://localhost:8081/index.html`

## 📂 Project Structure

```
phpwebsite/
├── php/
│   ├── google_auth.php          # Google OAuth handler
│   ├── google_callback.php      # OAuth callback
│   ├── google_config.php        # OAuth configuration
│   ├── google_credentials.php   # Credentials (not in git)
│   ├── submit_admission.php     # Form submission handler
│   ├── get_user_profile.php     # Profile data API
│   ├── update_user_profile.php  # Profile update API
│   └── config.php               # Database configuration
├── assets/
│   ├── admission-form.css       # Admission form styles
│   ├── auth-pages.css          # Login/Register styles
│   └── professional-enhancements.css
├── images/                      # Images and SVG files
├── index.html                   # Home page
├── admission.html              # Admission form
├── profile.html                # Student profile
├── login.html                  # Login page
├── register.html               # Registration page
├── admin.html                  # Admin dashboard
└── README.md                   # This file
```

## 🔒 Security Notes

- **Never commit** `google_credentials.php` - it contains sensitive OAuth credentials
- The `.gitignore` file prevents this file from being tracked
- Always use environment variables or separate credential files for sensitive data
- Enable HTTPS in production
- Regenerate OAuth credentials if accidentally exposed

## 🎨 Customization

### Change Color Scheme

Edit color variables in `new.css` and `assets/admission-form.css`:

```css
/* Blue theme (default) */
background: linear-gradient(135deg, rgba(37, 99, 235, 0.95) 0%, rgba(29, 78, 216, 0.9) 100%);
```

### Add New Programs

Edit the program options in `admission.html`:

```html
<option value="new-program">New Program Name</option>
```

## 📊 Database Tables

- **users** - Student/user accounts
- **applications** - Admission applications
- **admin_users** - Admin accounts

## 🧪 Testing

Test pages are available:
- `test_submit_form.html` - Test admission form submission
- `php/test_admission_submit.php` - View table structure and test data

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Open a Pull Request

## 📝 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 👨‍💻 Developer

**Mohit Vanjara**
- GitHub: [@Mohit7276](https://github.com/Mohit7276)

## 📞 Support

For issues or questions:
1. Open an issue on GitHub
2. Contact: info@vidyaguru.edu

---

**Note**: This is a college project for educational purposes. Please ensure proper security measures before deploying to production.
