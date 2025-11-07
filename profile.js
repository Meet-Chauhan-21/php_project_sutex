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
    
    // Force profile picture load after everything is ready
    setTimeout(() => {
        console.log('🖼️ Force loading profile picture after initialization...');
        loadProfilePicture();
    }, 500);
    
    // Initialize event listeners
    initializeEventListeners();
    
    // Initialize tab navigation
    initializeTabNavigation();
    
    // Initialize inline editing
    initializeInlineEditing();
    
    // Initialize profile picture functionality
    initializeProfilePicture();
    
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
    // Clear any existing data to prevent stale state
    currentUserData = null;

    try {
        // Always try to fetch from PHP backend first for fresh data
        console.log('Loading user profile from database...');
        const response = await fetch('php/get_user_profile.php', {
            method: 'GET',
            credentials: 'include'
        });
        const result = await response.json();
        
        if (result.success) {
            console.log('✅ Profile loaded from database');
            currentUserData = result.user;
            
            // Ensure additional_info is properly structured
            if (!currentUserData.additional_info || typeof currentUserData.additional_info !== 'object') {
                currentUserData.additional_info = {};
            }
            
            // Log profile data for debugging
            console.log('Profile data loaded:', {
                id: currentUserData.id,
                name: `${currentUserData.first_name} ${currentUserData.last_name}`,
                email: currentUserData.email,
                phone: currentUserData.phone,
                program: currentUserData.program,
                has_picture: !!currentUserData.profile_picture,
                additional_fields: Object.keys(currentUserData.additional_info).length
            });
            
            // Update all profile sections with fresh data
            updateAllProfileSections(currentUserData, session);
            
            // Force profile picture display after all sections are updated
            if (currentUserData.profile_picture) {
                console.log('🖼️ Explicitly displaying profile picture after data load');
                console.log('Profile picture length:', currentUserData.profile_picture.length);
                displayProfilePicture(currentUserData.profile_picture);
            } else {
                console.log('⚠️ No profile picture found in database data, trying fresh load...');
                // Force a fresh load from database
                setTimeout(() => {
                    loadProfilePicture();
                }, 500);
            }
            
            // Save to localStorage for offline access
            localStorage.setItem('currentUser', JSON.stringify(currentUserData));
            console.log('Profile data saved to localStorage');
            
        } else {
            console.log('❌ Database profile load failed:', result.error);
            // Try to load from localStorage as fallback
            await loadFromLocalStorage(session);
        }
    } catch (error) {
        console.error('❌ Error loading profile from database:', error);
        // Fallback to localStorage
        await loadFromLocalStorage(session);
    }
}

// Fallback function to load from localStorage
async function loadFromLocalStorage(session) {
    console.log('Loading profile from localStorage...');
    
    const savedUser = localStorage.getItem('currentUser');
    if (savedUser) {
        try {
            currentUserData = JSON.parse(savedUser);
            console.log('✅ Profile loaded from localStorage');
            updateAllProfileSections(currentUserData, session);
        } catch (error) {
            console.error('❌ Error parsing localStorage data:', error);
            // Create mock data as last resort
            createFallbackProfile(session);
        }
    } else {
        console.log('No localStorage data found, creating fallback profile');
        createFallbackProfile(session);
    }
}

// Create fallback profile data
function createFallbackProfile(session) {
    console.log('Creating fallback profile...');
    currentUserData = createMockUserData(session);
    updateAllProfileSections(currentUserData, session);
    showNotification('Using demo data. Please refresh to load your actual profile.', 'info');
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
    updateProfileCompletion(user);
    loadProfilePicture(); // Ensure profile picture is loaded
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
    console.log('Updating profile completion for user:', user);
    
    if (!user) {
        console.warn('No user data provided to updateProfileCompletion');
        return;
    }
    
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

    // Count completed fields and log details
    fieldChecks.forEach(field => {
        const isCompleted = !!field.check;
        console.log(`${field.name}: ${isCompleted ? '✓' : '✗'} (${field.check})`);
        if (isCompleted) {
            completed++;
        }
    });

    const percentage = Math.round((completed / total) * 100);
    const remaining = total - completed;

    console.log(`Profile completion calculated: ${percentage}% (${completed}/${total} fields)`);

    // Update progress circle
    const profileCompletionValue = document.getElementById('profileCompletionValue');
    const progressFill = document.getElementById('progressFill');
    
    if (profileCompletionValue) {
        profileCompletionValue.textContent = `${percentage}%`;
        console.log('Updated progress percentage to:', percentage + '%');
    } else {
        console.error('profileCompletionValue element not found');
    }

    if (progressFill) {
        progressFill.style.width = `${percentage}%`;
        
        // Remove existing completion classes
        progressFill.classList.remove('completion-low', 'completion-medium', 'completion-high', 'completion-complete');
        
        // Add appropriate completion class
        if (percentage === 100) {
            progressFill.classList.add('completion-complete');
        } else if (percentage >= 75) {
            progressFill.classList.add('completion-high');
        } else if (percentage >= 50) {
            progressFill.classList.add('completion-medium');
        } else {
            progressFill.classList.add('completion-low');
        }
        
        console.log('Updated progress bar to:', percentage + '%');
    } else {
        console.error('progressFill element not found');
    }

    // Update completion statistics
    const progressStats = document.querySelectorAll('.progress-stat .stat-number');
    const progressLabels = document.querySelectorAll('.progress-stat .stat-label');
    
    console.log('Progress stats elements found:', progressStats.length);
    console.log('Progress labels found:', progressLabels.length);
    
    progressLabels.forEach((label, index) => {
        const numberElement = progressStats[index];
        if (!numberElement) return;
        
        const labelText = label.textContent.toLowerCase();
        
        if (labelText.includes('completed')) {
            numberElement.textContent = completed;
            console.log('Updated completed count to:', completed);
        } else if (labelText.includes('remaining')) {
            numberElement.textContent = remaining;
            console.log('Updated remaining count to:', remaining);
        }
    });

    // Update the visual progress bar if needed
    updateProgressBarVisual(percentage);

    console.log(`Profile completion update completed: ${percentage}% (${completed}/${total} fields)`);
}

// Update the visual progress bar animation
function updateProgressBarVisual(percentage) {
    const progressFill = document.getElementById('progressFill');
    if (!progressFill) {
        console.warn('Progress fill element not found');
        return;
    }

    // Animate the width change
    progressFill.style.width = `${percentage}%`;
    
    // Add CSS custom property for any advanced animations
    progressFill.style.setProperty('--progress', percentage);
    
    console.log('Updated progress bar visual to:', percentage + '%');
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
    console.log('Initializing event listeners...');
    
    // Hero action buttons
    const quickEditBtn = document.getElementById('quickEditBtn');
    const settingsBtn = document.getElementById('settingsBtn');
    
    console.log('quickEditBtn found:', !!quickEditBtn);
    console.log('settingsBtn found:', !!settingsBtn);
    
    if (quickEditBtn) {
        quickEditBtn.addEventListener('click', () => {
            console.log('Quick edit button clicked');
            // Switch to personal tab
            const personalTab = document.querySelector('[data-tab="personal"]');
            if (personalTab) {
                personalTab.click();
                showNotification('Switched to profile editing mode', 'info');
            } else {
                console.error('Personal tab not found');
            }
        });
    }
    
    if (settingsBtn) {
        settingsBtn.addEventListener('click', () => {
            console.log('Settings button clicked');
            // Switch to preferences tab
            const preferencesTab = document.querySelector('[data-tab="preferences"]');
            if (preferencesTab) {
                preferencesTab.click();
                showNotification('Switched to settings', 'info');
            } else {
                console.error('Preferences tab not found');
            }
        });
    }

    // Action panel buttons
    const logoutBtn = document.getElementById('logoutBtn');
    const saveAllBtn = document.getElementById('saveAllBtn');
    
    console.log('logoutBtn found:', !!logoutBtn);
    console.log('saveAllBtn found:', !!saveAllBtn);
    
    if (logoutBtn) {
        logoutBtn.addEventListener('click', () => {
            console.log('Logout button clicked');
            logout();
        });
    }
    
    if (saveAllBtn) {
        saveAllBtn.addEventListener('click', () => {
            console.log('Save all button clicked');
            saveAllChanges();
        });
    }

    // Make progress bar clickable
    const progressBarContainer = document.querySelector('.progress-bar-container');
    if (progressBarContainer) {
        progressBarContainer.addEventListener('click', () => {
            console.log('Progress bar clicked');
            guideProfileCompletion();
        });
        progressBarContainer.style.cursor = 'pointer';
        progressBarContainer.title = 'Click to get guidance on completing your profile';
    }

    // Preference toggles
    const preferenceToggles = document.querySelectorAll('.toggle-switch input[type="checkbox"]');
    preferenceToggles.forEach(toggle => {
        toggle.addEventListener('change', savePreferences);
    });

    // Quick action buttons (including security buttons)
    const actionButtons = document.querySelectorAll('.action-btn[data-action], .card-action-btn[data-action], .security-btn[data-action]');
    console.log('Action buttons found:', actionButtons.length);
    
    actionButtons.forEach((button, index) => {
        const action = button.getAttribute('data-action');
        console.log(`Action button ${index}: ${action}`);
        button.addEventListener('click', handleQuickAction);
    });

    // Progress stats click events
    const progressStats = document.querySelectorAll('.progress-stat');
    progressStats.forEach(stat => {
        stat.addEventListener('click', (e) => {
            const label = stat.querySelector('.stat-label').textContent.toLowerCase();
            console.log('Progress stat clicked:', label);
            
            // Remove active class from all stats
            progressStats.forEach(s => s.classList.remove('active'));
            // Add active class to clicked stat
            stat.classList.add('active');
            
            if (label.includes('completed')) {
                showCompletionDetails('completed');
            } else if (label.includes('remaining')) {
                showCompletionDetails('remaining');
            }
        });
    });

    // Modal close event
    const closeModalBtn = document.getElementById('closeCompletionDetails');
    const modalOverlay = document.getElementById('completionDetailsOverlay');
    
    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', closeCompletionModal);
    }
    
    if (modalOverlay) {
        modalOverlay.addEventListener('click', (e) => {
            if (e.target === modalOverlay) {
                closeCompletionModal();
            }
        });
    }

    // Change password modal events
    const changePasswordOverlay = document.getElementById('changePasswordOverlay');
    const closeChangePasswordBtn = document.getElementById('closeChangePassword');
    const cancelChangePasswordBtn = document.getElementById('cancelChangePassword');
    const changePasswordForm = document.getElementById('changePasswordForm');
    
    if (closeChangePasswordBtn) {
        closeChangePasswordBtn.addEventListener('click', closeChangePasswordModal);
    }
    
    if (cancelChangePasswordBtn) {
        cancelChangePasswordBtn.addEventListener('click', closeChangePasswordModal);
    }
    
    if (changePasswordOverlay) {
        changePasswordOverlay.addEventListener('click', (e) => {
            if (e.target === changePasswordOverlay) {
                closeChangePasswordModal();
            }
        });
    }
    
    if (changePasswordForm) {
        changePasswordForm.addEventListener('submit', handleChangePassword);
    }

    // Security section buttons - specific handlers
    const securityButtons = document.querySelectorAll('.security-btn');
    console.log('Security buttons found:', securityButtons.length);
    
    securityButtons.forEach((button, index) => {
        const action = button.getAttribute('data-action');
        console.log(`Security button ${index}: ${action}`);
        button.addEventListener('click', (e) => {
            e.preventDefault();
            handleSecurityAction(action);
        });
    });

    // Danger zone buttons
    const dangerButtons = document.querySelectorAll('.danger-btn');
    console.log('Danger buttons found:', dangerButtons.length);
    
    dangerButtons.forEach((button, index) => {
        const action = button.getAttribute('data-action');
        console.log(`Danger button ${index}: ${action}`);
        button.addEventListener('click', (e) => {
            e.preventDefault();
            handleDangerAction(action);
        });
    });

    // Password toggle buttons
    const passwordToggles = document.querySelectorAll('.password-toggle');
    passwordToggles.forEach(toggle => {
        toggle.addEventListener('click', (e) => {
            const targetId = toggle.getAttribute('data-target');
            const targetInput = document.getElementById(targetId);
            const icon = toggle.querySelector('i');
            
            if (targetInput.type === 'password') {
                targetInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                targetInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });

    // Password confirmation validation
    const newPasswordInput = document.getElementById('newPassword');
    const confirmPasswordInput = document.getElementById('confirmPassword');
    const matchIndicator = document.getElementById('passwordMatchIndicator');
    
    if (newPasswordInput && confirmPasswordInput && matchIndicator) {
        const validatePasswords = () => {
            const newPassword = newPasswordInput.value;
            const confirmPassword = confirmPasswordInput.value;
            
            if (confirmPassword === '') {
                matchIndicator.textContent = '';
                matchIndicator.className = 'password-match-indicator';
            } else if (newPassword === confirmPassword) {
                matchIndicator.textContent = '✓ Passwords match';
                matchIndicator.className = 'password-match-indicator match';
            } else {
                matchIndicator.textContent = '✗ Passwords do not match';
                matchIndicator.className = 'password-match-indicator no-match';
            }
        };
        
        newPasswordInput.addEventListener('input', validatePasswords);
        confirmPasswordInput.addEventListener('input', validatePasswords);
    }

    console.log('Event listeners initialized');
}

// Handle quick action button clicks
function handleQuickAction(event) {
    const action = event.currentTarget.getAttribute('data-action');
    console.log('Quick action triggered:', action);

    switch (action) {
        case 'edit-profile':
            // Switch to personal tab for profile editing
            const personalTab = document.querySelector('[data-tab="personal"]');
            if (personalTab) {
                personalTab.click();
                showNotification('Switched to profile editing mode', 'info');
            }
            break;

        case 'change-password':
            openChangePasswordModal();
            break;

        case 'download-data':
            downloadUserData();
            break;

        case 'privacy-settings':
            // Switch to preferences tab
            const preferencesTab = document.querySelector('[data-tab="preferences"]');
            if (preferencesTab) {
                preferencesTab.click();
                showNotification('Switched to privacy settings', 'info');
            }
            break;

        default:
            showNotification('Feature coming soon!', 'info');
            break;
    }
}

// Handle security action button clicks
function handleSecurityAction(action) {
    console.log('Security action triggered:', action);

    switch (action) {
        case 'change-password':
            openChangePasswordModal();
            break;

        case 'two-factor':
            showNotification('Two-factor authentication setup coming soon!', 'info');
            break;

        case 'login-sessions':
            showNotification('Active sessions management coming soon!', 'info');
            break;

        default:
            showNotification('Security feature coming soon!', 'info');
            break;
    }
}

// Handle danger action button clicks
function handleDangerAction(action) {
    console.log('Danger action triggered:', action);

    switch (action) {
        case 'deactivate-account':
            if (confirm('Are you sure you want to deactivate your account? This can be reversed later.')) {
                showNotification('Account deactivation feature coming soon!', 'info');
            }
            break;

        case 'delete-account':
            if (confirm('Are you sure you want to permanently delete your account? This action cannot be undone!')) {
                showNotification('Account deletion feature coming soon!', 'info');
            }
            break;

        default:
            showNotification('Account action coming soon!', 'info');
            break;
    }
}

// Download user data as JSON
function downloadUserData() {
    if (!currentUserData) {
        showNotification('No user data available to download', 'error');
        return;
    }

    try {
        // Create a clean copy of user data for download
        const dataToDownload = {
            personal_info: {
                first_name: currentUserData.first_name,
                last_name: currentUserData.last_name,
                email: currentUserData.email,
                phone: currentUserData.phone,
                program: currentUserData.program,
                registration_date: currentUserData.registration_date
            },
            additional_info: currentUserData.additional_info || {},
            download_date: new Date().toISOString(),
            data_format: 'JSON'
        };

        // Convert to JSON
        const jsonData = JSON.stringify(dataToDownload, null, 2);
        
        // Create blob and download
        const blob = new Blob([jsonData], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        
        // Create download link
        const link = document.createElement('a');
        link.href = url;
        link.download = `profile_data_${currentUserData.first_name}_${new Date().toISOString().split('T')[0]}.json`;
        
        // Trigger download
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        // Clean up
        URL.revokeObjectURL(url);
        
        showNotification('Profile data downloaded successfully!', 'success');
    } catch (error) {
        console.error('Error downloading user data:', error);
        showNotification('Failed to download profile data', 'error');
    }
}

// Guide user through profile completion
function guideProfileCompletion() {
    if (!currentUserData) {
        showNotification('No user data available', 'error');
        return;
    }

    // Check which fields are missing
    const fieldChecks = [
        { check: currentUserData.first_name && currentUserData.last_name, name: 'Basic Information (Name)', tab: 'personal', action: 'Add your full name' },
        { check: currentUserData.email, name: 'Email Address', tab: 'personal', action: 'Add your email address' },
        { check: currentUserData.phone, name: 'Phone Number', tab: 'personal', action: 'Add your phone number' },
        { check: currentUserData.program, name: 'Academic Program', tab: 'academic', action: 'Select your academic program' },
        { check: currentUserData.additional_info?.dateOfBirth, name: 'Date of Birth', tab: 'personal', action: 'Add your date of birth' },
        { check: currentUserData.additional_info?.address, name: 'Address', tab: 'personal', action: 'Add your address' },
        { check: currentUserData.additional_info?.emergencyContact, name: 'Emergency Contact', tab: 'personal', action: 'Add emergency contact info' },
        { check: currentUserData.profile_picture, name: 'Profile Picture', tab: 'overview', action: 'Upload a profile picture' }
    ];

    const missingFields = fieldChecks.filter(field => !field.check);

    if (missingFields.length === 0) {
        showNotification('🎉 Your profile is 100% complete!', 'success');
        return;
    }

    // Guide to the first missing field
    const firstMissing = missingFields[0];
    const targetTab = document.querySelector(`[data-tab="${firstMissing.tab}"]`);
    
    if (targetTab) {
        targetTab.click();
        showNotification(`Next step: ${firstMissing.action}`, 'info');
        
        // Show summary of remaining tasks
        setTimeout(() => {
            const remainingTasks = missingFields.map(field => field.action).join(', ');
            console.log('Remaining tasks:', remainingTasks);
        }, 1000);
    } else {
        showNotification(`Please ${firstMissing.action.toLowerCase()}`, 'info');
    }
}

// Refresh profile completion calculation
function refreshProfileCompletion() {
    if (!currentUserData) {
        showNotification('No user data to refresh', 'error');
        return;
    }

    console.log('Manually refreshing profile completion...');
    
    // Add visual feedback
    const refreshBtn = document.getElementById('refreshCompletionBtn');
    if (refreshBtn) {
        const originalText = refreshBtn.innerHTML;
        refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Refreshing...';
        refreshBtn.disabled = true;
        
        // Simulate refresh process
        setTimeout(() => {
            updateProfileCompletion(currentUserData);
            refreshBtn.innerHTML = originalText;
            refreshBtn.disabled = false;
            showNotification('Profile completion refreshed!', 'success');
        }, 1000);
    } else {
        updateProfileCompletion(currentUserData);
        showNotification('Profile completion refreshed!', 'success');
    }
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

// Profile picture functionality
function initializeProfilePicture() {
    const avatarEditBtn = document.getElementById('avatarEditBtn');
    const profilePictureInput = document.getElementById('profilePictureInput');
    const profileImage = document.getElementById('profileImage');
    const defaultAvatar = document.getElementById('defaultAvatar');

    if (!avatarEditBtn || !profilePictureInput || !profileImage) {
        console.log('Profile picture elements not found');
        return;
    }

    // Click edit button to trigger file input
    avatarEditBtn.addEventListener('click', () => {
        profilePictureInput.click();
    });

    // Handle file selection
    profilePictureInput.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (file) {
            handleProfilePictureUpload(file);
        }
    });

    // Load existing profile picture if any
    loadProfilePicture();
    console.log('Profile picture functionality initialized');
}

async function handleProfilePictureUpload(file) {
    // Validate file type
    const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!allowedTypes.includes(file.type)) {
        showNotification('Please select a valid image file (JPEG, PNG, GIF, or WebP)', 'error');
        return;
    }

    // Validate file size (max 5MB)
    const maxSize = 5 * 1024 * 1024; // 5MB
    if (file.size > maxSize) {
        showNotification('Image size must be less than 5MB', 'error');
        return;
    }

    try {
        // Convert to base64
        const base64 = await fileToBase64(file);
        console.log('File converted to base64, length:', base64.length);
        
        // Update profile with image
        await updateProfilePicture(base64);
        console.log('Profile picture update completed');
        
        // Display the image
        displayProfilePicture(base64);
        console.log('displayProfilePicture called');
        
        // Save profile picture for persistence
        saveProfilePicturePersistent(base64);
        
        showNotification('Profile picture updated successfully!', 'success');
    } catch (error) {
        console.error('Error uploading profile picture:', error);
        showNotification('Failed to upload profile picture', 'error');
    }
}

function fileToBase64(file) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onload = () => resolve(reader.result);
        reader.onerror = reject;
        reader.readAsDataURL(file);
    });
}

async function updateProfilePicture(base64Image) {
    try {
        const response = await fetch('php/update_user_profile.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            credentials: 'include',
            body: JSON.stringify({
                profile_picture: base64Image
            })
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const result = await response.json();
        if (!result.success) {
            throw new Error(result.message || 'Failed to update profile picture');
        }

        // Update currentUserData immediately
        if (currentUserData) {
            currentUserData.profile_picture = base64Image;
            // Update profile completion since picture was added
            updateProfileCompletion(currentUserData);
            // Also save to localStorage for persistence
            localStorage.setItem('currentUser', JSON.stringify(currentUserData));
            console.log('✅ Profile picture updated in memory and localStorage');
        }

        // Force immediate display of the updated picture
        console.log('🖼️ Forcing immediate profile picture display');
        displayProfilePicture(base64Image);
        
        // Also save to persistent storage
        saveProfilePicturePersistent(base64Image);

        return result;
    } catch (error) {
        console.log('PHP update failed, using localStorage fallback:', error);
        // Fallback for testing - store in localStorage
        if (currentUserData) {
            currentUserData.profile_picture = base64Image;
            localStorage.setItem('currentUser', JSON.stringify(currentUserData));
            // Update profile completion
            updateProfileCompletion(currentUserData);
        }
        return { success: true };
    }
}

function displayProfilePicture(base64Image) {
    const profileImage = document.getElementById('profileImage');
    const defaultAvatar = document.getElementById('defaultAvatar');

    console.log('displayProfilePicture called with:', base64Image ? `base64 image data (${base64Image.length} chars)` : 'no image');
    console.log('profileImage element found:', !!profileImage);
    console.log('defaultAvatar element found:', !!defaultAvatar);

    if (profileImage) {
        if (base64Image && base64Image.trim() !== '') {
            profileImage.src = base64Image;
            profileImage.style.display = 'block';
            profileImage.classList.add('show');
            console.log('✅ Profile image displayed');
            
            if (defaultAvatar) {
                defaultAvatar.style.display = 'none';
                defaultAvatar.classList.add('hidden');
                console.log('✅ Default avatar hidden');
            }
        } else {
            profileImage.style.display = 'none';
            profileImage.classList.remove('show');
            console.log('❌ Profile image hidden (no data)');
            
            if (defaultAvatar) {
                defaultAvatar.style.display = 'block';
                defaultAvatar.classList.remove('hidden');
                console.log('✅ Default avatar shown');
            }
        }
    } else {
        console.error('❌ profileImage element not found in DOM');
    }
}

async function loadProfilePicture() {
    console.log('🖼️ Loading profile picture...');
    
    // Step 1: Try to load from database (most reliable)
    try {
        console.log('📡 Attempting to load from database...');
        const response = await fetch('php/get_user_profile.php', {
            method: 'GET',
            credentials: 'include',
            cache: 'no-cache'
        });
        
        if (response.ok) {
            const data = await response.json();
            if (data.success && data.user && data.user.profile_picture) {
                console.log('✅ Found profile picture in database:', data.user.profile_picture.length, 'characters');
                displayProfilePicture(data.user.profile_picture);
                
                // Update currentUserData
                if (currentUserData) {
                    currentUserData.profile_picture = data.user.profile_picture;
                }
                
                // Save to persistent storage
                saveProfilePicturePersistent(data.user.profile_picture);
                return;
            } else {
                console.log('⚠️ No profile picture in database response');
            }
        } else {
            console.log('⚠️ Database request failed:', response.status);
        }
    } catch (error) {
        console.error('❌ Database load error:', error);
    }
    
    // Step 2: Try currentUserData
    if (currentUserData && currentUserData.profile_picture) {
        console.log('✅ Found profile picture in currentUserData');
        displayProfilePicture(currentUserData.profile_picture);
        return;
    }
    
    // Step 3: Try localStorage userProfilePicture (specific storage)
    const storedPicture = localStorage.getItem('userProfilePicture');
    if (storedPicture) {
        console.log('✅ Found profile picture in localStorage (specific)');
        displayProfilePicture(storedPicture);
        if (currentUserData) {
            currentUserData.profile_picture = storedPicture;
        }
        return;
    }

    // Step 4: Try localStorage currentUser
    const userData = localStorage.getItem('currentUser');
    if (userData) {
        try {
            const user = JSON.parse(userData);
            if (user.profile_picture) {
                console.log('✅ Found profile picture in localStorage (user data)');
                displayProfilePicture(user.profile_picture);
                if (currentUserData) {
                    currentUserData.profile_picture = user.profile_picture;
                }
                return;
            }
        } catch (error) {
            console.error('Error parsing localStorage user data:', error);
        }
    }

    console.log('ℹ️ No profile picture found anywhere, showing default avatar');
    displayProfilePicture(null);
}

function removeProfilePicture() {
    if (!confirm('Are you sure you want to remove your profile picture?')) {
        return;
    }

    // Call API to remove profile picture
    updateProfilePicture(null)
        .then(() => {
            displayProfilePicture(null);
            showNotification('Profile picture removed successfully!', 'success');
        })
        .catch(error => {
            console.error('Error removing profile picture:', error);
            showNotification('Failed to remove profile picture', 'error');
        });
}

// Save profile picture to multiple storage locations for persistence
function saveProfilePicturePersistent(base64Image) {
    try {
        // Save to localStorage
        if (currentUserData) {
            localStorage.setItem('currentUser', JSON.stringify(currentUserData));
        }
        
        // Also save to sessionStorage as backup
        if (currentUserData) {
            sessionStorage.setItem('currentUser', JSON.stringify(currentUserData));
        }
        
        // Save just the profile picture separately for easier retrieval
        localStorage.setItem('userProfilePicture', base64Image);
        sessionStorage.setItem('userProfilePicture', base64Image);
        
        console.log('Profile picture saved to persistent storage');
    } catch (error) {
        console.error('Error saving profile picture to storage:', error);
    }
}

// Show completion details modal
function showCompletionDetails(type) {
    console.log('Showing completion details for:', type);
    
    if (!currentUserData) {
        console.error('No user data available for completion details');
        return;
    }
    
    const modal = document.getElementById('completionDetailsOverlay');
    const title = document.getElementById('completionDetailsTitle');
    const fieldList = document.getElementById('completionFieldList');
    
    if (!modal || !title || !fieldList) {
        console.error('Modal elements not found');
        return;
    }
    
    // Define all required fields with their status
    const fieldChecks = [
        { 
            check: currentUserData.first_name && currentUserData.last_name, 
            name: 'Basic Information (Name)',
            description: 'First name and last name are required'
        },
        { 
            check: currentUserData.email, 
            name: 'Email Address',
            description: 'Valid email address for communication'
        },
        { 
            check: currentUserData.phone, 
            name: 'Phone Number',
            description: 'Contact phone number'
        },
        { 
            check: currentUserData.program, 
            name: 'Academic Program',
            description: 'Selected study program'
        },
        { 
            check: currentUserData.additional_info?.dateOfBirth, 
            name: 'Date of Birth',
            description: 'Your date of birth'
        },
        { 
            check: currentUserData.additional_info?.address, 
            name: 'Address',
            description: 'Current residential address'
        },
        { 
            check: currentUserData.additional_info?.emergencyContact, 
            name: 'Emergency Contact',
            description: 'Emergency contact information'
        },
        { 
            check: currentUserData.profile_picture, 
            name: 'Profile Picture',
            description: 'Your profile photo'
        }
    ];
    
    // Filter fields based on type
    let fieldsToShow = [];
    if (type === 'completed') {
        fieldsToShow = fieldChecks.filter(field => !!field.check);
        title.innerHTML = '<i class="fas fa-check-circle"></i> Completed Fields';
    } else if (type === 'remaining') {
        fieldsToShow = fieldChecks.filter(field => !field.check);
        title.innerHTML = '<i class="fas fa-exclamation-circle"></i> Remaining Fields';
    }
    
    // Generate HTML for fields
    fieldList.innerHTML = '';
    fieldsToShow.forEach(field => {
        const isCompleted = !!field.check;
        const fieldItem = document.createElement('div');
        fieldItem.className = `completion-field-item ${isCompleted ? 'completed' : 'incomplete'}`;
        
        fieldItem.innerHTML = `
            <div class="completion-field-icon ${isCompleted ? 'completed' : 'incomplete'}">
                <i class="fas fa-${isCompleted ? 'check' : 'times'}"></i>
            </div>
            <div class="completion-field-info">
                <div class="completion-field-name">${field.name}</div>
                <div class="completion-field-status">${field.description}</div>
            </div>
        `;
        
        fieldList.appendChild(fieldItem);
    });
    
    // Show modal
    modal.style.display = 'block';
    setTimeout(() => {
        modal.style.opacity = '1';
    }, 10);
}

// Close completion modal
function closeCompletionModal() {
    const modal = document.getElementById('completionDetailsOverlay');
    const progressStats = document.querySelectorAll('.progress-stat');
    
    if (modal) {
        modal.style.opacity = '0';
        setTimeout(() => {
            modal.style.display = 'none';
        }, 300);
    }
    
    // Remove active states from progress stats
    progressStats.forEach(stat => stat.classList.remove('active'));
}

// Open change password modal
function openChangePasswordModal() {
    console.log('Opening change password modal');
    const modal = document.getElementById('changePasswordOverlay');
    const form = document.getElementById('changePasswordForm');
    
    if (modal) {
        // Reset form
        if (form) {
            form.reset();
        }
        
        // Clear password match indicator
        const matchIndicator = document.getElementById('passwordMatchIndicator');
        if (matchIndicator) {
            matchIndicator.textContent = '';
            matchIndicator.className = 'password-match-indicator';
        }
        
        // Show modal with proper display
        modal.style.display = 'flex';
        modal.style.opacity = '0';
        
        // Use requestAnimationFrame for smooth animation
        requestAnimationFrame(() => {
            modal.style.opacity = '1';
        });
        
        // Focus on first input
        const firstInput = document.getElementById('currentPassword');
        if (firstInput) {
            setTimeout(() => firstInput.focus(), 150);
        }
        
        // Prevent body scroll
        document.body.style.overflow = 'hidden';
    } else {
        console.error('Change password modal not found');
    }
}

// Close change password modal
function closeChangePasswordModal() {
    const modal = document.getElementById('changePasswordOverlay');
    
    if (modal) {
        modal.style.opacity = '0';
        setTimeout(() => {
            modal.style.display = 'none';
            // Restore body scroll
            document.body.style.overflow = '';
        }, 300);
    }
}

// Handle change password form submission
async function handleChangePassword(e) {
    e.preventDefault();
    
    const currentPassword = document.getElementById('currentPassword').value;
    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    const submitBtn = document.getElementById('submitChangePassword');
    
    // Validate passwords match
    if (newPassword !== confirmPassword) {
        showNotification('Passwords do not match', 'error');
        return;
    }
    
    // Validate password length
    if (newPassword.length < 6) {
        showNotification('New password must be at least 6 characters long', 'error');
        return;
    }
    
    // Disable submit button
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
    
    try {
        const response = await fetch('php/change_password.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            credentials: 'include',
            body: JSON.stringify({
                currentPassword: currentPassword,
                newPassword: newPassword
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('Password updated successfully!', 'success');
            closeChangePasswordModal();
        } else {
            showNotification(data.error || 'Failed to update password', 'error');
        }
        
    } catch (error) {
        console.error('Error changing password:', error);
        showNotification('An error occurred while updating password', 'error');
    } finally {
        // Re-enable submit button
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-save"></i> Update Password';
    }
}
