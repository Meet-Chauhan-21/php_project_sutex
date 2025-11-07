// Enhanced Authentication JavaScript for VidyaGuru College

// Mobile menu functionality
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const nav = document.getElementById('nav');

    if (mobileMenuBtn && nav) {
        mobileMenuBtn.addEventListener('click', function() {
            nav.classList.toggle('active');
            const isOpen = nav.classList.contains('active');
            mobileMenuBtn.setAttribute('aria-expanded', isOpen);
        });
    }

    // Check if user is logged in and update profile link
    updateProfileLink();
    
    // Check for Google login state
    checkGoogleLoginState();

    // Registration form handling
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', handleRegistration);
        
        // Add real-time validation
        const firstNameField = registerForm.querySelector('#firstName');
        const lastNameField = registerForm.querySelector('#lastName');
        const phoneField = registerForm.querySelector('#phone');
        const emailField = registerForm.querySelector('#email');
        const passwordField = registerForm.querySelector('#password');
        const confirmPasswordField = registerForm.querySelector('#confirmPassword');
        
        if (firstNameField) {
            firstNameField.addEventListener('blur', () => validateRequiredField(firstNameField, 'First name is required'));
            firstNameField.addEventListener('input', () => clearFieldError(firstNameField));
        }
        
        if (lastNameField) {
            lastNameField.addEventListener('blur', () => validateRequiredField(lastNameField, 'Last name is required'));
            lastNameField.addEventListener('input', () => clearFieldError(lastNameField));
        }
        
        if (phoneField) {
            phoneField.addEventListener('blur', () => validatePhone(phoneField));
            phoneField.addEventListener('input', () => clearFieldError(phoneField));
        }
        
        if (emailField) {
            emailField.addEventListener('blur', () => validateEmail(emailField));
            emailField.addEventListener('input', () => clearFieldError(emailField));
        }
        
        if (passwordField) {
            passwordField.addEventListener('input', () => validatePassword(passwordField));
        }
        
        if (confirmPasswordField) {
            confirmPasswordField.addEventListener('blur', () => validateConfirmPassword(passwordField, confirmPasswordField));
            confirmPasswordField.addEventListener('input', () => clearFieldError(confirmPasswordField));
        }
    }

    // Login form handling
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', handleLogin);
        
        // Add real-time validation
        const emailField = loginForm.querySelector('#loginEmail');
        const passwordField = loginForm.querySelector('#loginPassword');
        
        if (emailField) {
            emailField.addEventListener('blur', () => validateEmail(emailField));
            emailField.addEventListener('input', () => clearFieldError(emailField));
        }
        
        if (passwordField) {
            passwordField.addEventListener('input', () => clearFieldError(passwordField));
        }
    }
});

// Registration handler with enhanced validation
function handleRegistration(e) {
    e.preventDefault();
    
    // Clear previous errors
    clearAllErrors();
    
    const formData = new FormData(e.target);
    const userData = {
        email: formData.get('email'),
        password: formData.get('password'),
        confirmPassword: formData.get('confirmPassword'),
        firstName: formData.get('firstName') || '',
        lastName: formData.get('lastName') || '',
        phone: formData.get('phone') || ''
    };

    // Comprehensive validation
    const validationResults = [
        validateRequiredField(document.getElementById('firstName'), 'First name is required'),
        validateRequiredField(document.getElementById('lastName'), 'Last name is required'),
        validatePhone(document.getElementById('phone')),
        validateEmail(document.getElementById('email')),
        validatePassword(document.getElementById('password')),
        validateConfirmPassword(document.getElementById('password'), document.getElementById('confirmPassword'))
    ];

    if (!validationResults.every(result => result)) {
        showAlert('Please fix the errors below', 'error');
        return;
    }

    // Show loading state
    const submitBtn = e.target.querySelector('.btn-primary');
    setButtonLoading(submitBtn, 'Creating Account...');

    // Call PHP registration endpoint
    fetch('php/register_user.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(userData)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            showAlert('Account created successfully! Please log in.', 'success');
            resetButton(submitBtn, 'Signup');
            
            // Add success animation to form
            e.target.style.animation = 'bounceIn 0.6s ease-out';
            
            setTimeout(() => {
                window.location.href = 'login.html';
            }, 2000);
        } else {
            showAlert(result.error || 'Registration failed. Please try again.', 'error');
            resetButton(submitBtn, 'Signup');
        }
    })
    .catch(error => {
        console.error('Registration error:', error);
        showAlert('Network error. Please try again later.', 'error');
        resetButton(submitBtn, 'Signup');
    });
}

// Login handler with enhanced validation
function handleLogin(e) {
    e.preventDefault();
    
    // Clear previous errors
    clearAllErrors();
    
    const formData = new FormData(e.target);
    const loginData = {
        email: formData.get('email'),
        password: formData.get('password')
    };

    // Validation
    const validationResults = [
        validateEmail(document.getElementById('loginEmail')),
        validateRequiredField(document.getElementById('loginPassword'), 'Password is required')
    ];

    if (!validationResults.every(result => result)) {
        showAlert('Please fix the errors below', 'error');
        return;
    }

    // Show loading state
    const submitBtn = e.target.querySelector('.btn-primary');
    setButtonLoading(submitBtn, 'Signing In...');

    // Call PHP login endpoint
    fetch('php/login_user.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(loginData)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            // Store session data
            const sessionData = {
                email: result.user.email,
                name: result.user.name,
                id: result.user.id,
                loginTime: new Date().toISOString()
            };

            localStorage.setItem('vidyaGuruSession', JSON.stringify(sessionData));
            localStorage.setItem('userLoggedIn', 'true');

            showAlert('Welcome back! Redirecting...', 'success');
            resetButton(submitBtn, 'Login');
            
            // Add success animation
            e.target.style.animation = 'bounceIn 0.6s ease-out';
            
            setTimeout(() => {
                // Check if there's a redirect URL stored
                const redirectUrl = localStorage.getItem('redirectAfterLogin');
                if (redirectUrl) {
                    localStorage.removeItem('redirectAfterLogin');
                    window.location.href = redirectUrl;
                } else {
                    window.location.href = 'profile.html';
                }
            }, 1500);
        } else {
            showAlert(result.error || 'Login failed. Please try again.', 'error');
            resetButton(submitBtn, 'Login');
            
            // Highlight error fields with animation
            const emailField = document.getElementById('loginEmail');
            const passwordField = document.getElementById('loginPassword');
            
            if (emailField) {
                emailField.classList.add('error');
                setTimeout(() => emailField.classList.remove('error'), 3000);
            }
            if (passwordField) {
                passwordField.classList.add('error');
                setTimeout(() => passwordField.classList.remove('error'), 3000);
            }
        }
    })
    .catch(error => {
        console.error('Login error:', error);
        showAlert('Network error. Please try again later.', 'error');
        resetButton(submitBtn, 'Login');
    });
}

// Enhanced validation functions
function validateEmail(field) {
    if (!field) return false;
    
    const email = field.value.trim();
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    
    if (!email) {
        showFieldError(field, 'Email is required');
        return false;
    }
    
    if (!emailRegex.test(email)) {
        showFieldError(field, 'Please enter a valid email address');
        return false;
    }
    
    showFieldSuccess(field);
    return true;
}

function validatePassword(field) {
    if (!field) return false;
    
    const password = field.value;
    const minLength = 8;
    const hasUpperCase = /[A-Z]/.test(password);
    const hasLowerCase = /[a-z]/.test(password);
    const hasNumbers = /\d/.test(password);
    
    // Clear previous states
    clearFieldError(field);
    
    if (!password) {
        showFieldError(field, 'Password is required');
        return false;
    }
    
    if (password.length < minLength) {
        showFieldError(field, `Password must be at least ${minLength} characters long`);
        return false;
    }
    
    if (!hasUpperCase || !hasLowerCase || !hasNumbers) {
        showFieldError(field, 'Password must contain uppercase, lowercase and number');
        return false;
    }
    
    showFieldSuccess(field);
    return true;
}

function validateConfirmPassword(passwordField, confirmField) {
    if (!passwordField || !confirmField) return false;
    
    const password = passwordField.value;
    const confirmPassword = confirmField.value;
    
    if (!confirmPassword) {
        showFieldError(confirmField, 'Please confirm your password');
        return false;
    }
    
    if (password !== confirmPassword) {
        showFieldError(confirmField, 'Passwords do not match');
        return false;
    }
    
    showFieldSuccess(confirmField);
    return true;
}

function validatePhone(field) {
    if (!field) return true; // Phone is optional
    
    const phone = field.value.trim();
    const phoneRegex = /^[0-9]{10}$/;
    
    if (phone && !phoneRegex.test(phone)) {
        showFieldError(field, 'Please enter a valid 10-digit phone number');
        return false;
    }
    
    if (phone) {
        showFieldSuccess(field);
    }
    return true;
}

function validateRequiredField(field, message) {
    if (!field) return false;
    
    if (!field.value.trim()) {
        showFieldError(field, message);
        return false;
    }
    
    showFieldSuccess(field);
    return true;
}

// UI Helper functions
function showFieldError(field, message) {
    field.classList.remove('success');
    field.classList.add('error');
    
    const errorId = field.id + 'Error';
    let errorElement = document.getElementById(errorId);
    
    if (errorElement) {
        errorElement.textContent = message;
        errorElement.classList.add('show');
        errorElement.style.animation = 'fadeInUp 0.3s ease-out';
    }
    
    // Auto-clear error on input
    field.addEventListener('input', function clearOnInput() {
        clearFieldError(field);
        field.removeEventListener('input', clearOnInput);
    });
}

function showFieldSuccess(field) {
    field.classList.remove('error');
    field.classList.add('success');
    clearFieldError(field);
}

function clearFieldError(field) {
    if (!field) return;
    
    field.classList.remove('error');
    const errorId = field.id + 'Error';
    const errorElement = document.getElementById(errorId);
    
    if (errorElement) {
        errorElement.classList.remove('show');
        errorElement.textContent = '';
    }
}

function clearAllErrors() {
    // Remove error classes from all form controls
    document.querySelectorAll('.form-control').forEach(field => {
        field.classList.remove('error', 'success');
    });
    
    // Hide all error messages
    document.querySelectorAll('.error-message').forEach(error => {
        error.classList.remove('show');
        error.textContent = '';
    });
}

function setButtonLoading(button, text) {
    button.disabled = true;
    button.classList.add('loading');
    button.dataset.originalText = button.textContent;
    button.textContent = text;
}

function resetButton(button, text) {
    button.disabled = false;
    button.classList.remove('loading');
    button.textContent = text || button.dataset.originalText || 'Submit';
}

// Enhanced alert function
function showAlert(message, type) {
    // Remove existing alerts
    const existingAlert = document.querySelector('.custom-alert');
    if (existingAlert) {
        existingAlert.remove();
    }

    // Create alert element
    const alert = document.createElement('div');
    alert.className = `custom-alert alert-${type}`;
    alert.innerHTML = `
        <div class="alert-content">
            <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
            <span>${message}</span>
        </div>
    `;

    document.body.appendChild(alert);

    // Animate in
    setTimeout(() => alert.classList.add('show'), 100);

    // Remove after 5 seconds
    setTimeout(() => {
        alert.classList.remove('show');
        setTimeout(() => alert.remove(), 300);
    }, 5000);
}

// Profile link update function
function updateProfileLink() {
    const profileLink = document.getElementById('profileLink');
    const session = checkAuth();
    
    if (profileLink) {
        if (session) {
            profileLink.innerHTML = '<i class="fas fa-user-circle"></i> Profile';
            profileLink.href = 'profile.html';
            profileLink.title = 'View Profile';
        } else {
            profileLink.innerHTML = '<i class="fas fa-sign-in-alt"></i> Login';
            profileLink.href = 'login.html';
            profileLink.title = 'Login to your account';
        }
    }
}

// Authentication check function
function checkAuth() {
    const session = localStorage.getItem('vidyaGuruSession') || sessionStorage.getItem('vidyaGuruSession');
    const isLoggedIn = localStorage.getItem('userLoggedIn') === 'true';
    
    return (session && isLoggedIn) ? JSON.parse(session) : null;
}

// Check PHP backend authentication
async function checkPHPAuth() {
    try {
        const response = await fetch('php/get_user_profile.php', {
            method: 'GET',
            credentials: 'include'
        });
        const result = await response.json();
        
        if (result.success) {
            // Update localStorage to maintain compatibility
            const sessionData = {
                userId: result.user.id,
                email: result.user.email,
                firstName: result.user.first_name,
                lastName: result.user.last_name,
                role: 'student'
            };
            
            localStorage.setItem('vidyaGuruSession', JSON.stringify(sessionData));
            localStorage.setItem('userLoggedIn', 'true');
            
            return sessionData;
        } else {
            // Clear localStorage if PHP session is invalid
            localStorage.removeItem('vidyaGuruSession');
            localStorage.removeItem('userLoggedIn');
            return null;
        }
    } catch (error) {
        console.error('PHP Auth check error:', error);
        return null;
    }
}

// Logout function
function logout() {
    fetch('php/logout_user.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(result => {
        // Clear client-side session data
        localStorage.removeItem('vidyaGuruSession');
        sessionStorage.removeItem('vidyaGuruSession');
        localStorage.removeItem('userLoggedIn');
        
        showAlert('Logged out successfully!', 'success');
        setTimeout(() => {
            window.location.href = 'index.html';
        }, 1500);
    })
    .catch(error => {
        console.error('Logout error:', error);
        // Still clear client-side data even if server request fails
        localStorage.removeItem('vidyaGuruSession');
        sessionStorage.removeItem('vidyaGuruSession');
        localStorage.removeItem('userLoggedIn');
        
        showAlert('Logged out successfully!', 'success');
        setTimeout(() => {
            window.location.href = 'index.html';
        }, 1500);
    });
}

// Header scroll effect
window.addEventListener('scroll', function() {
    const header = document.getElementById('header');
    if (header) {
        if (window.scrollY > 100) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    }
});

// Password toggle function
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const toggle = input.nextElementSibling.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        toggle.classList.remove('fa-eye-slash');
        toggle.classList.add('fa-eye');
    } else {
        input.type = 'password';
        toggle.classList.remove('fa-eye');
        toggle.classList.add('fa-eye-slash');
    }
}

// Google Authentication Functions
function handleCredentialResponse(response) {
    console.log("Encoded JWT ID token: " + response.credential);
    
    // Send the credential to your backend for verification and login
    fetch('php/google_auth.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            credential: response.credential
        })
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            // Store session data
            const sessionData = {
                userId: result.user.id,
                email: result.user.email,
                firstName: result.user.first_name || result.user.given_name,
                lastName: result.user.last_name || result.user.family_name,
                fullName: result.user.full_name,
                picture: result.user.picture,
                loginMethod: 'google'
            };

            localStorage.setItem('vidyaGuruSession', JSON.stringify(sessionData));
            localStorage.setItem('userLoggedIn', 'true');
            localStorage.setItem('loginMethod', 'google');

            showAlert('Google login successful! Redirecting...', 'success');
            
            setTimeout(() => {
                window.location.href = 'profile.html';
            }, 1500);
        } else {
            showAlert(result.error || 'Google login failed. Please try again.', 'error');
        }
    })
    .catch(error => {
        console.error('Google login error:', error);
        showAlert('Network error during Google login. Please try again.', 'error');
    });
}

function loginWithGoogle() {
    // Clear any existing session data
    localStorage.removeItem('userLoggedIn');
    localStorage.removeItem('vidyaGuruSession');
    sessionStorage.clear();
    
    // Build Google OAuth URL
    const clientId = '848381710448-e350700oj5m3qg8cjpiaif47hqmfi2ik.apps.googleusercontent.com';
    const redirectUri = 'http://localhost:8081/php/google_callback.php';
    const scope = 'openid email profile';
    const responseType = 'code';
    const state = Math.random().toString(36).substring(2, 15); // CSRF protection
    
    // Store state for verification
    sessionStorage.setItem('oauth_state', state);
    
    const googleAuthUrl = `https://accounts.google.com/o/oauth2/auth?` +
        `client_id=${encodeURIComponent(clientId)}&` +
        `redirect_uri=${encodeURIComponent(redirectUri)}&` +
        `scope=${encodeURIComponent(scope)}&` +
        `response_type=${responseType}&` +
        `state=${state}`;
    
    // Redirect to Google OAuth
    window.location.href = googleAuthUrl;
}

function registerWithGoogle() {
    // For registration, use the same OAuth flow
    // The backend will handle creating new accounts
    loginWithGoogle();
}

function checkGoogleLoginState() {
    const loginMethod = localStorage.getItem('loginMethod');
    const userLoggedIn = localStorage.getItem('userLoggedIn');
    
    if (userLoggedIn === 'true' && (loginMethod === 'google' || loginMethod === 'google-demo')) {
        // User is logged in via Google (real or demo)
        updateUIForGoogleUser();
    }
}

function updateUIForGoogleUser() {
    const userName = localStorage.getItem('userName');
    const userPicture = localStorage.getItem('userPicture');
    
    // Update profile link with Google user info
    const profileLink = document.getElementById('profileLink');
    if (profileLink && userName) {
        profileLink.textContent = userName.split(' ')[0]; // First name only
        profileLink.href = 'profile.html';
        
        // Add Google profile picture if available
        if (userPicture) {
            profileLink.innerHTML = `<img src="${userPicture}" alt="Profile" style="width: 24px; height: 24px; border-radius: 50%; margin-right: 5px; vertical-align: middle;">${userName.split(' ')[0]}`;
        }
    }
}

// Enhanced logout function to handle Google users
function enhancedLogout() {
    const loginMethod = localStorage.getItem('loginMethod');
    
    if (loginMethod === 'google' || loginMethod === 'google-demo') {
        // Sign out from Google (only for real Google auth, demo doesn't need this)
        if (loginMethod === 'google' && typeof google !== 'undefined' && google.accounts && google.accounts.id) {
            google.accounts.id.disableAutoSelect();
        }
    }
    
    // Clear all local storage
    localStorage.removeItem('userLoggedIn');
    localStorage.removeItem('userEmail');
    localStorage.removeItem('userName');
    localStorage.removeItem('userPicture');
    localStorage.removeItem('loginMethod');
    
    // Redirect to home page
    window.location.href = 'index.html';
}
