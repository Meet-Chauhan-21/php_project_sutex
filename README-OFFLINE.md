# VidyaGuru College Website - Offline Version

This website has been configured to work completely offline without requiring an internet connection.

## Changes Made for Offline Operation

### 1. Fonts
- **Before**: Used Google Fonts (Inter, Poppins) from CDN
- **After**: Created `assets/fonts-local.css` with system font fallbacks
  - Inter → 'Inter-Local' (uses Segoe UI, Helvetica Neue, Arial)
  - Poppins → 'Poppins-Local' (uses Segoe UI, Helvetica Neue, Arial)

### 2. Icons
- **Before**: Used Font Awesome from CDN
- **After**: Created `assets/fontawesome-local.css` with local icon subset
  - Includes all icons used in the website
  - Uses CSS pseudo-elements with Unicode characters

### 3. Images
- **Before**: Used external images from Unsplash, RandomUser, etc.
- **After**: Created local SVG placeholders in `images/` folder:
  - `bca-program.svg` - BCA program image
  - `bba-program.svg` - BBA program image
  - `bcom-program.svg` - BCom program image
  - `bteach-program.svg` - BTeach program image
  - `profile-male.svg` - Male profile placeholder
  - `profile-female.svg` - Female profile placeholder
  - `hero-bg.svg` - Hero section background
  - `logo.svg` - College logo
  - `collage.jpg` - Background image (SVG format)

### 4. Favicon
- **Before**: Used PNG favicon
- **After**: Created SVG favicon (`cc_logo.svg`)

## File Structure
```
phpwebsite/
├── assets/
│   ├── fontawesome-local.css    # Local Font Awesome icons
│   └── fonts-local.css          # Local font fallbacks
├── images/
│   ├── bca-program.svg
│   ├── bba-program.svg
│   ├── bcom-program.svg
│   ├── bteach-program.svg
│   ├── profile-male.svg
│   ├── profile-female.svg
│   ├── hero-bg.svg
│   ├── logo.svg
│   └── collage.jpg
├── index.html                   # Updated with local assets
├── register.html               # Updated with local assets
├── login.html                  # Updated with local assets
├── profile.html                # Updated with local assets
├── enroll_1.html              # Updated with local assets
├── new.css                    # Updated font references
├── cc_logo.svg                # SVG favicon
└── other files...
```

## Features That Work Offline

✅ **All styling and layout**
- Typography using system fonts
- Icons from local CSS
- Responsive design
- All visual effects and animations

✅ **All images and graphics**
- Program images (as SVG placeholders)
- Profile pictures (as SVG placeholders)
- Logos and icons
- Background images

✅ **Complete functionality**
- Navigation between pages
- Form interactions
- JavaScript functionality
- Mobile responsiveness

## Browser Compatibility

The website will work in all modern browsers without internet:
- Chrome/Edge: Full support
- Firefox: Full support
- Safari: Full support
- Internet Explorer 11+: Basic support

## Performance Benefits

- **Faster loading**: No external requests
- **No 404 errors**: All assets are local
- **Consistent appearance**: No dependency on external CDNs
- **Privacy friendly**: No external tracking or fonts loading

## Notes

- The SVG placeholders are styled to match the color scheme
- System fonts provide good fallbacks for the original web fonts
- Icon subset includes all icons used in the current website
- All external dependencies have been eliminated

## Running the Website

Simply open any HTML file in a web browser. No server required for basic functionality. 
For full functionality including form submissions, run the Python server:

```bash
python server.py
```

Or use the batch file:
```bash
start_server.bat
```
