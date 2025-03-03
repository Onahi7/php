export const initValidation = () => {
    // Validation rules
    const rules = {
        email: {
            pattern: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
            message: 'Please enter a valid email address'
        },
        phone: {
            pattern: /^\+?[\d\s-]{10,}$/,
            message: 'Please enter a valid phone number'
        },
        required: {
            pattern: /.+/,
            message: 'This field is required'
        },
        number: {
            pattern: /^\d+$/,
            message: 'Please enter a valid number'
        },
        password: {
            pattern: /^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/,
            message: 'Password must be at least 8 characters with letters and numbers'
        }
    };

    // Real-time validation
    const validateField = (input) => {
        const field = input.dataset.validate?.split(' ') || [];
        let isValid = true;
        let errorMessage = '';

        field.forEach(rule => {
            if (rules[rule] && !rules[rule].pattern.test(input.value)) {
                isValid = false;
                errorMessage = rules[rule].message;
            }
        });

        // Update UI
        const errorElement = input.nextElementSibling;
        if (!isValid) {
            input.classList.add('border-red-500');
            if (errorElement?.classList.contains('error-message')) {
                errorElement.textContent = errorMessage;
            } else {
                const error = document.createElement('span');
                error.className = 'error-message text-red-500 text-sm mt-1';
                error.textContent = errorMessage;
                input.parentNode.insertBefore(error, input.nextSibling);
            }
        } else {
            input.classList.remove('border-red-500');
            if (errorElement?.classList.contains('error-message')) {
                errorElement.remove();
            }
        }

        return isValid;
    };

    // Form submission handler
    const handleSubmit = (form) => {
        let isValid = true;
        const formData = new FormData();

        // Validate all fields
        form.querySelectorAll('input, select, textarea').forEach(field => {
            if (field.dataset.validate) {
                const fieldValid = validateField(field);
                isValid = isValid && fieldValid;
            }
            if (field.name) {
                formData.append(field.name, field.value);
            }
        });

        if (!isValid) {
            return false;
        }

        // Submit form data
        const submitButton = form.querySelector('[type="submit"]');
        if (submitButton) {
            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="spinner"></span> Processing...';
        }

        // Send form data
        fetch(form.action, {
            method: form.method || 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                showNotification('Success!', data.message, 'success');
                if (data.redirect) {
                    window.location.href = data.redirect;
                }
            } else {
                // Show error message
                showNotification('Error', data.message || 'An error occurred', 'error');
            }
        })
        .catch(error => {
            showNotification('Error', 'An error occurred', 'error');
        })
        .finally(() => {
            if (submitButton) {
                submitButton.disabled = false;
                submitButton.innerHTML = 'Submit';
            }
        });

        return false;
    };

    // Show notification
    const showNotification = (title, message, type = 'info') => {
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg ${
            type === 'success' ? 'bg-green-500' : 
            type === 'error' ? 'bg-red-500' : 
            'bg-blue-500'
        } text-white max-w-md z-50 transform transition-all duration-300 translate-y-0`;
        
        notification.innerHTML = `
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    ${type === 'success' ? '✓' : type === 'error' ? '✕' : 'ℹ'}
                </div>
                <div class="ml-3">
                    <p class="font-bold">${title}</p>
                    <p class="text-sm">${message}</p>
                </div>
            </div>
        `;

        document.body.appendChild(notification);

        // Remove notification after 5 seconds
        setTimeout(() => {
            notification.classList.add('translate-y-[-100%]', 'opacity-0');
            setTimeout(() => notification.remove(), 300);
        }, 5000);
    };

    // Initialize validation on all forms
    document.querySelectorAll('form[data-validate]').forEach(form => {
        // Add real-time validation
        form.querySelectorAll('input, select, textarea').forEach(field => {
            if (field.dataset.validate) {
                field.addEventListener('blur', () => validateField(field));
                field.addEventListener('input', () => validateField(field));
            }
        });

        // Handle form submission
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            handleSubmit(form);
        });
    });
};
