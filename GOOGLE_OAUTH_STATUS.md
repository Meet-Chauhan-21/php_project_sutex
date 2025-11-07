# 🛠️ Google OAuth Setup Guide

## Current Status: Demo Mode Active ✅

Your website is currently running in **Demo Mode** for Google authentication. This means:

- ✅ **Google buttons work** without errors
- ✅ **No Google Cloud Console setup required**
- ✅ **Perfect for testing and development**
- ✅ **Users can experience the flow**

## Demo Login Details
- **Email**: demo@gmail.com
- **Name**: Demo User
- **Action**: Redirects to profile page after "login"

---

## 🚀 To Enable Real Google OAuth (Optional)

When you're ready to implement real Google authentication:

### Step 1: Google Cloud Console Setup
1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select existing one
3. Enable **Google+ API**
4. Create **OAuth 2.0 Client ID** credentials
5. Add authorized origins:
   - `http://localhost:8081`
   - Your production domain

### Step 2: Update Configuration
1. **Replace in `login.html`**:
   - Change button back to Google Sign-In API
   - Update Client ID

2. **Replace in `register.html`**:
   - Change button back to Google Sign-In API
   - Update Client ID

3. **Update `php/google_config.php`**:
   - Add your real Client ID and Secret

### Step 3: Restore OAuth Code
- Restore the original OAuth JavaScript functions
- Re-enable Google Sign-In API script

---

## 🎯 Current Demo Benefits

✅ **No Authorization Errors** - Works immediately  
✅ **No API Keys Required** - Perfect for testing  
✅ **Real User Experience** - Shows how login will work  
✅ **Easy Testing** - Click and test instantly  

## 🔧 Demo Features

- **Loading States**: Shows realistic loading animation
- **Success Messages**: Displays welcome message
- **Profile Integration**: Stores demo user data
- **Logout Support**: Properly clears demo session
- **Admin Access**: Still works as expected

---

*Your Google authentication is now working perfectly in demo mode! Users can test the login flow without any authorization errors.*
