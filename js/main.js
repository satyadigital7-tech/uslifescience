/**
 * US Lifescience - Main JavaScript
 * Handles global interactions, loaders, navigations, and contact form AJAX submissions.
 */

document.addEventListener('DOMContentLoaded', () => {
    
    // 1. Loading Screen Removal
    const loader = document.getElementById('loading-screen');
    if (loader) {
        window.addEventListener('load', () => {
            setTimeout(() => {
                loader.style.opacity = '0';
                loader.style.visibility = 'hidden';
            }, 600); // smooth duration
        });
        
        // Backup safety check: if window.load is too slow, clear loader in 3 seconds anyway
        setTimeout(() => {
            loader.style.opacity = '0';
            loader.style.visibility = 'hidden';
        }, 3000);
    }

    // 2. Sticky Header & Back-to-Top Button Scroll Listener
    const header = document.querySelector('header');
    const backToTop = document.querySelector('.float-top');
    
    window.addEventListener('scroll', () => {
        if (window.scrollY > 50) {
            header.classList.add('sticky');
        } else {
            header.classList.remove('sticky');
        }
        
        if (window.scrollY > 300) {
            if (backToTop) backToTop.classList.add('show');
        } else {
            if (backToTop) backToTop.classList.remove('show');
        }
    });

    // 3. Mobile Hamburger Menu Toggle
    const hamburger = document.querySelector('.hamburger');
    const navMenu = document.querySelector('.nav-menu');
    
    if (hamburger && navMenu) {
        hamburger.addEventListener('click', () => {
            hamburger.classList.toggle('active');
            navMenu.classList.toggle('active');
        });
        
        // Close menu when clicking nav link
        document.querySelectorAll('.nav-menu a').forEach(link => {
            link.addEventListener('click', () => {
                hamburger.classList.remove('active');
                navMenu.classList.remove('active');
            });
        });
    }

    // 4. Smooth Scroll to Top Trigger
    if (backToTop) {
        backToTop.addEventListener('click', (e) => {
            e.preventDefault();
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }

    // 5. Contact Form AJAX Submission with Client Validation
    const enquiryForm = document.getElementById('enquiryForm');
    const formMessage = document.getElementById('formMessage');

    // Function to load CSRF token
    function loadCSRFToken() {
        if (!enquiryForm) return;
        fetch('php/get-captcha.php')
            .then(res => res.json())
            .then(data => {
                const csrfInput = enquiryForm.querySelector('input[name="csrf_token"]');
                if (csrfInput) csrfInput.value = data.csrf_token;
            })
            .catch(err => console.error('Error fetching security token:', err));
    }

    // Call onload
    if (enquiryForm) {
        loadCSRFToken();
    }

    if (enquiryForm) {
        enquiryForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Clear message
            if (formMessage) {
                formMessage.style.display = 'none';
                formMessage.className = 'form-message';
                formMessage.innerText = '';
            }
            
            // Client side validation
            const name = document.getElementById('name').value.trim();
            const phone = document.getElementById('phone').value.trim();
            const email = document.getElementById('email').value.trim();
            const subject = document.getElementById('subject').value.trim();
            const message = document.getElementById('message').value.trim();
            
            if (!name || !phone || !email || !subject || !message) {
                showFormMessage('All fields are required.', 'error');
                return;
            }

            // Email validation
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailPattern.test(email)) {
                showFormMessage('Please enter a valid email address.', 'error');
                return;
            }

            // Phone validation (simple check)
            if (phone.length < 10) {
                showFormMessage('Please enter a valid phone number (at least 10 digits).', 'error');
                return;
            }

            // Show submitting state
            const submitBtn = enquiryForm.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Sending Enquiry...';

            // Create data payload
            const formData = new FormData(enquiryForm);

            // Fetch request
            fetch('php/contact.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;

                if (data.status === 'success') {
                    showFormMessage(data.message, 'success');
                    enquiryForm.reset();
                    
                    // Reload fresh CSRF token from backend
                    loadCSRFToken();
                } else {
                    showFormMessage(data.message, 'error');
                }
            })
            .catch(error => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
                showFormMessage('An unexpected error occurred. Please try again later.', 'error');
                console.error('Submission Error:', error);
            });
        });
    }

    function showFormMessage(text, type) {
        if (formMessage) {
            formMessage.innerText = text;
            formMessage.classList.add(type);
            formMessage.style.display = 'block';
            
            // Scroll to message
            formMessage.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    }
});
