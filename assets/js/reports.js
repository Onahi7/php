document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const reportTypeSelect = document.getElementById('report_type');
    const formatSelect = document.getElementById('format');
    const dateFromInput = document.getElementById('date_from');
    const dateToInput = document.getElementById('date_to');

    // Initialize date pickers
    dateFromInput.valueAsDate = new Date();
    dateToInput.valueAsDate = new Date();

    // Form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();

        const submitButton = form.querySelector('button[type="submit"]');
        const originalText = submitButton.innerHTML;
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner"></span> Generating...';

        const formData = new FormData(form);

        fetch('/includes/handlers/report_handler.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Create download link
                const downloadUrl = '/uploads/reports/' + data.filename;
                window.location.href = downloadUrl;

                // Show success message
                showNotification('Success', 'Report generated successfully', 'success');

                // Refresh report list
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            } else {
                showNotification('Error', data.error || 'Failed to generate report', 'error');
            }
        })
        .catch(error => {
            showNotification('Error', 'An error occurred', 'error');
            console.error('Error:', error);
        })
        .finally(() => {
            submitButton.disabled = false;
            submitButton.innerHTML = originalText;
        });
    });

    // Show notification
    function showNotification(title, message, type = 'info') {
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
    }

    // Date validation
    dateFromInput.addEventListener('change', validateDates);
    dateToInput.addEventListener('change', validateDates);

    function validateDates() {
        const fromDate = new Date(dateFromInput.value);
        const toDate = new Date(dateToInput.value);

        if (fromDate > toDate) {
            dateToInput.value = dateFromInput.value;
            showNotification('Warning', 'End date cannot be before start date', 'error');
        }
    }

    // Dynamic form fields based on report type
    reportTypeSelect.addEventListener('change', function() {
        const type = this.value;
        
        // Show/hide specific fields based on report type
        switch(type) {
            case 'participants':
                // Add any specific fields for participants report
                break;
            case 'payments':
                // Add any specific fields for payments report
                break;
            case 'activities':
                // Add any specific fields for activities report
                break;
        }
    });

    // Format specific validations
    formatSelect.addEventListener('change', function() {
        const format = this.value;
        
        // Show format specific options or warnings
        switch(format) {
            case 'pdf':
                // PDF specific options
                break;
            case 'csv':
                // CSV specific options
                break;
            case 'excel':
                // Excel specific options
                break;
        }
    });
});
