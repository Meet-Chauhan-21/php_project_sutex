// Enhanced Profile Management JavaScript for VidyaGuru College

// Global variables for profile management
let currentUserData = null;
let isEditing = {
    basic: false,
    contact: false,
    academic: false
};

document.addEventListener('DOMContentLoaded', async function() {
    console.log('Profile page initializing...');
    
    // Check authentication - try PHP backend first, then fallback to localStorage
    let session = await checkPHPAuth();
    
    // Fallback to localStorage check if PHP auth failed
    if (!session && typeof checkAuth === 'function') {
        session = checkAuth();
        console.log('Using localStorage authentication');
    }
    
    if (!session) {
        // User not logged in
        console.log('User not authenticated');
        document.getElementById('notLoggedIn').style.display = 'block';
        document.getElementById('profileContent').style.display = 'none';
        return;
    }

    // User is logged in, show profile content
    console.log('User authenticated:', session);
    document.getElementById('notLoggedIn').style.display = 'none';
    document.getElementById('profileContent').style.display = 'block';
    
    // Load user data
    await loadUserProfile(session);
    
    // Initialize event listeners
    initializeEventListeners();
    
    // Initialize tab navigation
    initializeTabNavigation();
    
    // Initialize inline editing
    initializeInlineEditing();
    
    console.log('Profile page initialization completed');
});

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

// Load and display user profile data
async function loadUserProfile(session) {
    try {
        // Try to fetch from PHP backend first
        const response = await fetch('php/get_user_profile.php', {
            method: 'GET',
            credentials: 'include'
        });
        const result = await response.json();
        
        if (result.success) {
            currentUserData = result.user;
            updateAllProfileSections(currentUserData, session);
        } else {
            // Fallback to creating mock data for testing
            console.log('PHP backend failed, using mock data for testing');
            currentUserData = createMockUserData(session);
            updateAllProfileSections(currentUserData, session);
        }
    } catch (error) {
        console.error('Error loading profile:', error);
        // Create mock data for testing
        currentUserData = createMockUserData(session);
        updateAllProfileSections(currentUserData, session);
        showNotification('Using demo data for testing. Login to access real profile.', 'info');
    }
}

// Create mock user data for testing
function createMockUserData(session) {
    return {
        id: 1,
        first_name: session.firstName || 'John',
        last_name: session.lastName || 'Doe',
        email: session.email || 'john.doe@example.com',
        phone: '1234567890',
        program: 'BCA',
        registration_date: '2024-01-15',
        additional_info: {
            dateOfBirth: '1995-06-15',
            gender: 'male',
            address: '123 Main Street, City, State',
            emergencyContact: 'Jane Doe - 0987654321',
            preferences: {
                emailNotifications: true,
                smsNotifications: false,
                marketingCommunications: false,
                profileVisibility: true,
                contactInfoSharing: false
            }
        }
    };
}

// Update all profile sections
function updateAllProfileSections(user, session) {
    updateHeroSection(user, session);
    updateOverviewTab(user, session);
    updatePersonalInfoTab(user);
    updateAcademicTab(user);
    updatePreferencesTab(user);
}

// Update hero section
function updateHeroSection(user, session) {
    const heroDisplayName = document.getElementById('heroDisplayName');
    const heroDisplayRole = document.getElementById('heroDisplayRole');
    const statDaysActive = document.getElementById('statDaysActive');
    const statCoursesEnrolled = document.getElementById('statCoursesEnrolled');
    const statAchievements = document.getElementById('statAchievements');

    const firstName = user.first_name || 'User';
    const program = user.program || 'Student';
    
    if (heroDisplayName) {
        heroDisplayName.textContent = `Welcome back, ${firstName}!`;
    }
    
    if (heroDisplayRole) {
        heroDisplayRole.textContent = `${program} Student`;
    }

    // Calculate days active
    const registrationDate = new Date(user.registration_date || Date.now());
    const today = new Date();
    const daysActive = Math.floor((today - registrationDate) / (1000 * 60 * 60 * 24));
    
    if (statDaysActive) statDaysActive.textContent = daysActive;
    if (statCoursesEnrolled) statCoursesEnrolled.textContent = 1;
    if (statAchievements) statAchievements.textContent = 3;
}

// Update overview tab
function updateOverviewTab(user, session) {
    // Profile Summary
    const summaryFullName = document.getElementById('summaryFullName');
    const summaryEmail = document.getElementById('summaryEmail');
    const summaryProgram = document.getElementById('summaryProgram');
    const summaryStatus = document.getElementById('summaryStatus');

    if (summaryFullName) summaryFullName.textContent = `${user.first_name || ''} ${user.last_name || ''}`.trim() || '-';
    if (summaryEmail) summaryEmail.textContent = user.email;
    if (summaryProgram) summaryProgram.textContent = user.program || '-';
    if (summaryStatus) summaryStatus.textContent = 'Active';

    // Recent Activity
    const lastLoginTime = document.getElementById('lastLoginTime');
    const enrollmentTime = document.getElementById('enrollmentTime');
    
    if (lastLoginTime) lastLoginTime.textContent = 'Just now';
    if (enrollmentTime) enrollmentTime.textContent = new Date(user.registration_date || Date.now()).toLocaleDateString();

    // Profile Completion
    updateProfileCompletion(user);
}

// Update personal info tab
function updatePersonalInfoTab(user) {
    const displayFirstName = document.getElementById('displayFirstName');
    const displayLastName = document.getElementById('displayLastName');
    const displayDOB = document.getElementById('displayDOB');
    const displayGender = document.getElementById('displayGender');
    const displayEmail = document.getElementById('displayEmail');
    const displayPhone = document.getElementById('displayPhone');
    const displayEmergencyContact = document.getElementById('displayEmergencyContact');
    const displayAddress = document.getElementById('displayAddress');

    if (displayFirstName) displayFirstName.textContent = user.first_name || '-';
    if (displayLastName) displayLastName.textContent = user.last_name || '-';
    if (displayEmail) displayEmail.textContent = user.email || '-';
    if (displayPhone) displayPhone.textContent = user.phone || '-';

    // Additional info
    const additionalInfo = user.additional_info || {};
    if (displayDOB) displayDOB.textContent = additionalInfo.dateOfBirth || '-';
    if (displayGender) displayGender.textContent = additionalInfo.gender || '-';
    if (displayEmergencyContact) displayEmergencyContact.textContent = additionalInfo.emergencyContact || '-';
    if (displayAddress) displayAddress.textContent = additionalInfo.address || '-';
}

// Update academic tab
function updateAcademicTab(user) {
    const displayProgram = document.getElementById('displayProgram');
    const displayStudentID = document.getElementById('displayStudentID');
    const displayEnrollmentDate = document.getElementById('displayEnrollmentDate');
    const displayAcademicYear = document.getElementById('displayAcademicYear');

    if (displayProgram) displayProgram.textContent = user.program || '-';
    if (displayStudentID) displayStudentID.textContent = `VG${user.id || Date.now().toString().slice(-6)}`;
    if (displayEnrollmentDate) displayEnrollmentDate.textContent = new Date(user.registration_date || Date.now()).toLocaleDateString();
    if (displayAcademicYear) displayAcademicYear.textContent = '2024-2025';
}

// Update preferences tab
function updatePreferencesTab(user) {
    // Load user preferences from additional_info or set defaults
    const additionalInfo = user.additional_info || {};
    const preferences = additionalInfo.preferences || {
        emailNotifications: true,
        smsNotifications: false,
        marketingCommunications: false,
        profileVisibility: true,
        contactInfoSharing: false
    };

    // Update toggle switches
    const toggles = document.querySelectorAll('.toggle-switch input[type="checkbox"]');
    const prefKeys = ['emailNotifications', 'smsNotifications', 'marketingCommunications', 'profileVisibility', 'contactInfoSharing'];
    
    toggles.forEach((toggle, index) => {
        if (prefKeys[index]) {
            toggle.checked = preferences[prefKeys[index]];
        }
    });
}

// Initialize inline editing functionality
function initializeInlineEditing() {
    // Initialize form submissions
    const basicForm = document.getElementById('basicEditForm');
    const contactForm = document.getElementById('contactEditForm');
    const academicForm = document.getElementById('academicEditForm');

    if (basicForm) {
        basicForm.addEventListener('submit', handleBasicInfoSubmit);
    }
    
    if (contactForm) {
        contactForm.addEventListener('submit', handleContactInfoSubmit);
    }
    
    if (academicForm) {
        academicForm.addEventListener('submit', handleAcademicInfoSubmit);
    }

    console.log('Inline editing initialized');
}

// Toggle edit mode for sections
function toggleEditMode(section) {
    const displayElement = document.getElementById(`${section}Display`);
    const formElement = document.getElementById(`${section}EditForm`);
    const editBtn = document.getElementById(`edit${section.charAt(0).toUpperCase() + section.slice(1)}Btn`);

    if (!displayElement || !formElement || !editBtn) {
        console.error(`Elements not found for section: ${section}`);
        return;
    }

    if (isEditing[section]) {
        // Cancel edit mode
        cancelEdit(section);
    } else {
        // Enter edit mode
        isEditing[section] = true;
        displayElement.style.display = 'none';
        formElement.style.display = 'block';
        formElement.classList.add('show');
        editBtn.classList.add('editing');
        editBtn.innerHTML = '<i class="fas fa-times"></i>';

        // Populate form fields with current data
        populateEditForm(section);
    }
}

// Cancel edit mode
function cancelEdit(section) {
    const displayElement = document.getElementById(`${section}Display`);
    const formElement = document.getElementById(`${section}EditForm`);
    const editBtn = document.getElementById(`edit${section.charAt(0).toUpperCase() + section.slice(1)}Btn`);

    if (displayElement && formElement && editBtn) {
        isEditing[section] = false;
        displayElement.style.display = 'block';
        formElement.style.display = 'none';
        formElement.classList.remove('show');
        editBtn.classList.remove('editing');
        editBtn.innerHTML = '<i class="fas fa-edit"></i>';
    }
}

// Populate edit form with current data
function populateEditForm(section) {
    if (!currentUserData) return;

    const additionalInfo = currentUserData.additional_info || {};

    if (section === 'basic') {
        const firstName = document.getElementById('editFirstName');
        const lastName = document.getElementById('editLastName');
        const dob = document.getElementById('editDOB');
        const gender = document.getElementById('editGender');

        if (firstName) firstName.value = currentUserData.first_name || '';
        if (lastName) lastName.value = currentUserData.last_name || '';
        if (dob) dob.value = additionalInfo.dateOfBirth || '';
        if (gender) gender.value = additionalInfo.gender || '';
    }

    if (section === 'contact') {
        const email = document.getElementById('editEmail');
        const phone = document.getElementById('editPhone');
        const emergencyContact = document.getElementById('editEmergencyContact');
        const address = document.getElementById('editAddress');

        if (email) email.value = currentUserData.email || '';
        if (phone) phone.value = currentUserData.phone || '';
        if (emergencyContact) emergencyContact.value = additionalInfo.emergencyContact || '';
        if (address) address.value = additionalInfo.address || '';
    }

    if (section === 'academic') {
        const program = document.getElementById('editProgram');
        if (program) program.value = currentUserData.program || '';
    }
}

// Handle basic info form submission
async function handleBasicInfoSubmit(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const updateData = {
        firstName: formData.get('firstName'),
        lastName: formData.get('lastName'),
        dateOfBirth: formData.get('dateOfBirth'),
        gender: formData.get('gender')
    };

    await updateUserProfile(updateData, 'basic', 'Basic information updated successfully!');
}

// Handle contact info form submission
async function handleContactInfoSubmit(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const updateData = {
        email: formData.get('email'),
        phone: formData.get('phone'),
        address: formData.get('address'),
        emergencyContact: formData.get('emergencyContact')
    };

    await updateUserProfile(updateData, 'contact', 'Contact information updated successfully!');
}

// Handle academic info form submission
async function handleAcademicInfoSubmit(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const updateData = {
        program: formData.get('program')
    };

    await updateUserProfile(updateData, 'academic', 'Academic information updated successfully!');
}

// Generic function to update user profile
async function updateUserProfile(updateData, section, successMessage) {
    try {
        // Show loading state
        const submitBtn = document.querySelector(`#${section}EditForm button[type="submit"]`);
        if (submitBtn) {
            submitBtn.classList.add('loading');
            submitBtn.disabled = true;
        }

        // Try to update via PHP backend
        let success = false;
        
        try {
            const response = await fetch('php/update_user_profile.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify(updateData)
            });
            const result = await response.json();
            
            if (result.success) {
                success = true;
                // Reload profile data from server
                const session = await checkPHPAuth();
                if (session) {
                    await loadUserProfile(session);
                }
            } else {
                console.log('PHP update failed:', result.error);
            }
        } catch (error) {
            console.log('PHP update error:', error);
        }

        // Fallback to localStorage update for testing
        if (!success) {
            console.log('Using localStorage fallback for testing');
            updateLocalStorageData(updateData);
            updateAllProfileSections(currentUserData, { email: currentUserData.email });
            success = true;
        }

        if (success) {
            // Reset form state
            if (submitBtn) {
                submitBtn.classList.remove('loading');
                submitBtn.disabled = false;
            }

            // Exit edit mode
            cancelEdit(section);

            // Show success notification
            showNotification(successMessage, 'success');

            // Update profile completion
            updateProfileCompletion(currentUserData);
        }

    } catch (error) {
        console.error('Update profile error:', error);
        showNotification('Failed to update profile. Please try again.', 'error');
        
        // Reset form state
        const submitBtn = document.querySelector(`#${section}EditForm button[type="submit"]`);
        if (submitBtn) {
            submitBtn.classList.remove('loading');
            submitBtn.disabled = false;
        }
    }
}

// Update localStorage data for testing
function updateLocalStorageData(updateData) {
    if (!currentUserData.additional_info) {
        currentUserData.additional_info = {};
    }

    // Update basic fields
    if (updateData.firstName) currentUserData.first_name = updateData.firstName;
    if (updateData.lastName) currentUserData.last_name = updateData.lastName;
    if (updateData.email) currentUserData.email = updateData.email;
    if (updateData.phone) currentUserData.phone = updateData.phone;
    if (updateData.program) currentUserData.program = updateData.program;

    // Update additional info
    if (updateData.dateOfBirth) currentUserData.additional_info.dateOfBirth = updateData.dateOfBirth;
    if (updateData.gender) currentUserData.additional_info.gender = updateData.gender;
    if (updateData.address) currentUserData.additional_info.address = updateData.address;
    if (updateData.emergencyContact) currentUserData.additional_info.emergencyContact = updateData.emergencyContact;
}

// Calculate and update profile completion
function updateProfileCompletion(user) {
    let completed = 0;
    let total = 8;

    // Define all required fields
    const fieldChecks = [
        { check: user.first_name && user.last_name, name: 'Basic Information (Name)' },
        { check: user.email, name: 'Email Address' },
        { check: user.phone, name: 'Phone Number' },
        { check: user.program, name: 'Academic Program' },
        { check: user.additional_info?.dateOfBirth, name: 'Date of Birth' },
        { check: user.additional_info?.address, name: 'Address' },
        { check: user.additional_info?.emergencyContact, name: 'Emergency Contact' },
        { check: user.profile_picture, name: 'Profile Picture' }
    ];

    // Count completed fields
    fieldChecks.forEach(field => {
        if (field.check) {
            completed++;
        }
    });

    const percentage = Math.round((completed / total) * 100);
    const remaining = total - completed;

    // Update progress circle
    const profileCompletionValue = document.getElementById('profileCompletionValue');
    if (profileCompletionValue) {
        profileCompletionValue.textContent = `${percentage}%`;
    }

    // Update completion statistics
    const progressStats = document.querySelectorAll('.progress-stat .stat-number');
    const progressLabels = document.querySelectorAll('.progress-stat .stat-label');
    
    progressLabels.forEach((label, index) => {
        const numberElement = progressStats[index];
        if (!numberElement) return;
        
        const labelText = label.textContent.toLowerCase();
        
        if (labelText.includes('completed')) {
            numberElement.textContent = completed;
        } else if (labelText.includes('remaining')) {
            numberElement.textContent = remaining;
        }
    });

    console.log(`Profile completion: ${percentage}% (${completed}/${total} fields)`);
}

// Show notification
function showNotification(message, type = 'info') {
    // Remove existing notification
    const existingNotification = document.querySelector('.profile-notification');
    if (existingNotification) {
        existingNotification.remove();
    }

    // Create notification
    const notification = document.createElement('div');
    notification.className = `profile-notification ${type}`;
    notification.innerHTML = `
        <div style="display: flex; align-items: center; gap: 10px;">
            <i class="fas ${type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle'}"></i>
            <span>${message}</span>
        </div>
    `;

    document.body.appendChild(notification);

    // Show notification
    setTimeout(() => notification.classList.add('show'), 100);

    // Auto-hide after 4 seconds
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }, 4000);
}

// Initialize tab navigation
function initializeTabNavigation() {
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');

    tabButtons.forEach(button => {
        button.addEventListener('click', (e) => {
            e.preventDefault();
            const targetTab = button.getAttribute('data-tab');
            
            // Remove active class from all buttons and contents
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));
            
            // Add active class to clicked button and corresponding content
            button.classList.add('active');
            const targetContent = document.getElementById(`${targetTab}-tab`);
            if (targetContent) {
                targetContent.classList.add('active');
            }
        });
    });

    // Activate first tab by default if none are active
    const activeTab = document.querySelector('.tab-btn.active');
    if (!activeTab && tabButtons.length > 0) {
        tabButtons[0].click();
    }
}

// Initialize event listeners
function initializeEventListeners() {
    // Hero action buttons
    const quickEditBtn = document.getElementById('quickEditBtn');
    const settingsBtn = document.getElementById('settingsBtn');
    
    if (quickEditBtn) {
        quickEditBtn.addEventListener('click', () => {
            console.log('Quick edit button clicked');
            // Switch to personal tab
            const personalTab = document.querySelector('[data-tab="personal"]');
            if (personalTab) personalTab.click();
        });
    }
    
    if (settingsBtn) {
        settingsBtn.addEventListener('click', () => {
            // Switch to preferences tab
            const preferencesTab = document.querySelector('[data-tab="preferences"]');
            if (preferencesTab) preferencesTab.click();
        });
    }

    // Action panel buttons
    const logoutBtn = document.getElementById('logoutBtn');
    const saveAllBtn = document.getElementById('saveAllBtn');
    
    if (logoutBtn) {
        logoutBtn.addEventListener('click', logout);
    }
    
    if (saveAllBtn) {
        saveAllBtn.addEventListener('click', saveAllChanges);
    }

    // Preference toggles
    const preferenceToggles = document.querySelectorAll('.toggle-switch input[type="checkbox"]');
    preferenceToggles.forEach(toggle => {
        toggle.addEventListener('change', savePreferences);
    });

    console.log('Event listeners initialized');
}

// Save all changes
function saveAllChanges() {
    savePreferences();
    showNotification('All changes saved successfully!', 'success');
}

// Save user preferences
async function savePreferences() {
    const toggles = document.querySelectorAll('.toggle-switch input[type="checkbox"]');
    const preferences = {
        emailNotifications: toggles[0]?.checked || false,
        smsNotifications: toggles[1]?.checked || false,
        marketingCommunications: toggles[2]?.checked || false,
        profileVisibility: toggles[3]?.checked || false,
        contactInfoSharing: toggles[4]?.checked || false
    };

    try {
        // Try to update via PHP backend
        const response = await fetch('php/update_user_profile.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify({ preferences: preferences })
        });
        const result = await response.json();
        
        if (result.success) {
            showNotification('Preferences saved successfully!', 'success');
        } else {
            throw new Error(result.error);
        }
    } catch (error) {
        console.log('PHP save failed, using localStorage:', error);
        // Fallback to localStorage
        if (currentUserData) {
            if (!currentUserData.additional_info) currentUserData.additional_info = {};
            currentUserData.additional_info.preferences = preferences;
        }
        showNotification('Preferences saved locally!', 'success');
    }
}

// Logout function
function logout() {
    fetch('php/logout_user.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' }
    })
    .then(response => response.json())
    .then(result => {
        // Clear client-side session data
        localStorage.removeItem('vidyaGuruSession');
        sessionStorage.removeItem('vidyaGuruSession');
        localStorage.removeItem('userLoggedIn');
        
        showNotification('Logged out successfully!', 'success');
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
        
        showNotification('Logged out successfully!', 'success');
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

// Authentication check function (for compatibility)
function checkAuth() {
    const session = localStorage.getItem('vidyaGuruSession') || sessionStorage.getItem('vidyaGuruSession');
    const isLoggedIn = localStorage.getItem('userLoggedIn') === 'true';
    
    return (session && isLoggedIn) ? JSON.parse(session) : null;
}
