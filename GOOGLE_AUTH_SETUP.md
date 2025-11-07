# Google OAuth 2.0 Login Setup Guide

## 🔧 Setup Instructions

### Step 1: Google Cloud Console Setup

1. **Go to Google Cloud Console**: https://console.cloud.google.com/
2. **Create a new project** (or select existing one)
3. **Enable Google+ API**:
   - Go to "APIs & Services" > "Library"
   - Search for "Google+ API" or "People API"
   - Click "Enable"

4. **Create OAuth 2.0 Credentials**:
   - Go to "APIs & Services" > "Credentials"
   - Click "Create Credentials" > "OAuth client ID"
   - Choose "Web application"
   - Add authorized origins:
     - `http://localhost:8081`
     - `http://localhost`
     - Your production domain (e.g., `https://yourdomain.com`)
   - Add authorized redirect URIs:
     - `http://localhost:8081/php/google_callback.php`
     - Your production callback URL

5. **Copy your credentials**:
   - Client ID (looks like: `123456789-abcdef.apps.googleusercontent.com`)
   - Client Secret (looks like: `GOCSPX-abcdef123456`)

### Step 2: Update Configuration

1. **Edit `login.html`**:
   - Replace `YOUR_GOOGLE_CLIENT_ID.apps.googleusercontent.com` with your actual Client ID

2. **Edit `php/google_config.php`**:
   - Replace `YOUR_GOOGLE_CLIENT_ID` with your Client ID
   - Replace `YOUR_GOOGLE_CLIENT_SECRET` with your Client Secret

### Step 3: Database Setup

The database has been automatically configured with:
- `google_id` column in users table
- `given_name` and `family_name` columns
- `user_sessions` table for session management
- `last_login` tracking

### Step 4: Test the Integration

1. Go to `http://localhost:8081/login.html`
2. Click the "Sign in with Google" button
3. Complete Google authentication
4. User should be redirected to profile page

## 🔒 Security Features

- JWT token verification
- Session management
- Secure user data handling
- Protection against CSRF attacks

## 📁 Files Created/Modified

- `login.html` - Updated with Google Sign-In button and JavaScript
- `php/google_auth.php` - Backend authentication handler
- `php/google_config.php` - Configuration file
- `php/setup_google_auth.php` - Database setup script

## 🌐 How It Works

1. User clicks "Sign in with Google" button
2. Google OAuth popup appears
3. User authenticates with Google
4. Google returns JWT token to client
5. Client sends token to `php/google_auth.php`
6. Server verifies token and creates/updates user
7. User is logged in and redirected to profile

## 🔧 Troubleshooting

**Common Issues:**

1. **"Invalid Client ID"**: Make sure you've replaced the placeholder with your actual Client ID
2. **"Redirect URI mismatch"**: Add your exact URL to authorized redirect URIs in Google Console
3. **"Access blocked"**: Make sure Google+ API is enabled in your project

**Debug Mode:**
- Check browser console for JavaScript errors
- Check PHP error logs for server-side issues
- Verify database connection in `config.php`

## 🚀 Production Deployment

When deploying to production:

1. Update `GOOGLE_REDIRECT_URI` in `google_config.php`
2. Add production domain to Google Cloud Console
3. Use HTTPS for all URLs
4. Set secure session cookies
5. Enable proper error logging

## 📝 Testing Accounts

For testing, you can use any Google account. The system will:
- Create new users automatically
- Link existing email accounts
- Update user information on subsequent logins

---

*Last updated: August 17, 2025*
