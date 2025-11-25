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

### Step 3: Import Database in phpMyAdmin

1. **Start XAMPP Services**
   - Open XAMPP Control Panel
   - Start **Apache** and **MySQL** modules
   - Wait until both show green "Running" status

2. **Access phpMyAdmin**
   - Open your browser and go to: `http://localhost/phpmyadmin`
   - Alternative port: `http://localhost:8080/phpmyadmin`
   - Login with default credentials (Username: `root`, Password: leave blank)

3. **Create Database**
   - Click on "New" in the left sidebar
   - Enter database name: `vidhyaguru_db`
   - Select Collation: `utf8mb4_unicode_ci`
   - Click "Create"

4. **Import Database Structure & Data**
   - Select the `vidhyaguru_db` database from left sidebar
   - Click on "Import" tab at the top
   - Click "Choose File" button
   - Navigate to your project folder and select: `vidhyaguru_db (3).sql` or `create_vidhyaguru_db.sql`
   - Scroll down and click "Go" button
   - Wait for success message: "Import has been successfully finished"

5. **Verify Database Tables**
   - Click on `vidhyaguru_db` in left sidebar
   - You should see tables: `users`, `applications`, `admin_users`
   - Click on each table to verify structure

### Step 4: Configure Database Connection

Edit `php/config.php` if needed:

```php
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'vidhyaguru_db';
$DB_PORT = 3306;
```

### Step 5: Run the Website

#### Method 1: Using XAMPP (Recommended)

1. **Copy Project to XAMPP htdocs**
   ```bash
   # Copy the entire project folder to:
   C:\xampp\htdocs\phpwebsite
   ```

2. **Start XAMPP Services**
   - Open XAMPP Control Panel
   - Click "Start" on **Apache** module
   - Click "Start" on **MySQL** module
   - Both should show green "Running" status

3. **Access the Website**
   - Open your browser
   - Navigate to: `http://localhost/phpwebsite/index.html`
   - Alternative with port: `http://localhost:8081/index.html`

4. **Navigate the Website**
   - **Home Page**: `http://localhost/phpwebsite/index.html`
   - **Login Page**: `http://localhost/phpwebsite/login.html`
   - **Register Page**: `http://localhost/phpwebsite/register.html`
   - **Admission Form**: `http://localhost/phpwebsite/admission.html`
   - **Profile Page**: `http://localhost/phpwebsite/profile.html`
   - **Admin Dashboard**: `http://localhost/phpwebsite/admin.html`

#### Method 2: Using Batch File

1. Double-click `start_website.bat` in the project folder
2. Browser will automatically open the website

#### Troubleshooting

- **Port 80/443 already in use**: Change Apache port in XAMPP config
- **MySQL not starting**: Check if port 3306 is available
- **404 Error**: Verify project is in correct htdocs folder
- **Database connection error**: Check `php/config.php` settings

## 💾 How to Store Data in Database

### 1. **User Registration** (Automatic Storage)

When a user registers on the website:

1. Go to: `http://localhost/phpwebsite/register.html`
2. Fill in the registration form:
   - First Name, Last Name
   - Email (unique)
   - Password
3. Click "Register"
4. **Data automatically stored in `users` table**

**Verify in phpMyAdmin:**
```sql
SELECT * FROM users ORDER BY id DESC LIMIT 10;
```

### 2. **Google OAuth Login** (Automatic Storage)

When a user logs in with Google:

1. Click "Login with Google" on login page
2. Authorize with Google account
3. **User data automatically stored in `users` table**
   - Email, Name, Google ID
   - Profile picture URL

### 3. **Admission Form Submission** (Automatic Storage)

When a student submits admission application:

1. Go to: `http://localhost/phpwebsite/admission.html`
2. Fill in all required fields:
   - Personal Information
   - Contact Details
   - Program Selection
   - Educational Background
   - Guardian Information
3. Click "Submit Application"
4. **Data automatically stored in `applications` table**

**Verify in phpMyAdmin:**
```sql
SELECT id, full_name, email, program, status, submitted_at 
FROM applications 
ORDER BY submitted_at DESC;
```

### 4. **Profile Updates** (Automatic Storage)

When a user updates their profile:

1. Login to the website
2. Go to Profile page
3. Click "Edit Profile"
4. Update information (name, phone, address, etc.)
5. Click "Save Changes"
6. **Data automatically updated in `users` table**

### 5. **Manual Data Entry via phpMyAdmin**

To manually add data:

1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Select `vidhyaguru_db` database
3. Click on desired table (users/applications)
4. Click "Insert" tab
5. Fill in the form fields
6. Click "Go" to save

**Example: Add New User Manually**
```sql
INSERT INTO users (first_name, last_name, email, password_hash) 
VALUES ('John', 'Doe', 'john@example.com', '$2y$10$...');
```

**Example: Add New Application Manually**
```sql
INSERT INTO applications (user_id, first_name, last_name, email, phone, program, status) 
VALUES (1, 'John', 'Doe', 'john@example.com', '1234567890', 'BCA', 'pending');
```

### 6. **View Stored Data**

#### Via phpMyAdmin:
1. Open: `http://localhost/phpmyadmin`
2. Select `vidhyaguru_db`
3. Click on table name to view data
4. Click "Browse" to see all records

#### Via SQL Queries:
```sql
-- View all users
SELECT * FROM users;

-- View all applications
SELECT * FROM applications WHERE status = 'pending';

-- View applications with user details
SELECT a.*, u.email, u.first_name 
FROM applications a 
JOIN users u ON a.user_id = u.id;

-- Count applications by program
SELECT program, COUNT(*) as total 
FROM applications 
GROUP BY program;
```

### 7. **Admin Dashboard** (View & Manage Data)

1. Go to: `http://localhost/phpwebsite/admin.html`
2. Login with admin credentials
3. View all applications and user data
4. Approve/Reject applications
5. **Changes automatically saved to database**

## 📊 Database Schema

### Users Table
```sql
- id (Primary Key)
- first_name
- last_name
- email (Unique)
- password_hash
- phone
- address
- profile_picture
- created_at
- updated_at
```

### Applications Table
```sql
- id (Primary Key)
- user_id (Foreign Key → users.id)
- first_name, last_name, full_name
- email, phone, dob, gender
- program, session
- last_qualification, percentage, passing_year, board
- address, city, state, pincode
- guardian_name, guardian_phone
- status (pending/approved/rejected)
- submitted_at, updated_at
```

### Admin Users Table
```sql
- id (Primary Key)
- username (Unique)
- email (Unique)
- password_hash
- role (admin/super_admin)
- created_at, updated_at
```

### Step 5: Start the Application

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

## 🔧 API Endpoints

### User Management
- `php/register_user.php` - User registration
- `php/login_user.php` - User login
- `php/google_auth.php` - Google OAuth authentication
- `php/get_user_profile.php` - Get user profile data
- `php/update_user_profile.php` - Update user profile

### Application Management
- `php/submit_admission.php` - Submit admission application
- `php/get_application_status.php` - Check application status
- `php/applications_crud.php` - Admin: Manage applications

### Admin
- `php/admin_login.php` - Admin authentication
- `php/admin_dashboard_data.php` - Get dashboard statistics

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
