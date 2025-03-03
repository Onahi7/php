import flatpickr from 'flatpickr';
import 'flatpickr/dist/flatpickr.min.css';

export const initDatepickers = () => {
    // Initialize date pickers with different configurations
    const initializePickers = () => {
        // Standard date picker
        const standardPickers = document.querySelectorAll('.datepicker');
        if (standardPickers.length) {
            flatpickr(standardPickers, {
                dateFormat: 'Y-m-d',
                allowInput: true,
                disableMobile: false
            });
        }

        // Date range picker
        const rangePickers = document.querySelectorAll('.daterange-picker');
        if (rangePickers.length) {
            flatpickr(rangePickers, {
                mode: 'range',
                dateFormat: 'Y-m-d',
                allowInput: true,
                disableMobile: false
            });
        }

        // Future date only picker (for event scheduling)
        const futurePickers = document.querySelectorAll('.future-datepicker');
        if (futurePickers.length) {
            flatpickr(futurePickers, {
                dateFormat: 'Y-m-d',
                minDate: 'today',
                allowInput: true,
                disableMobile: false
            });
        }

        // Time picker
        const timePickers = document.querySelectorAll('.timepicker');
        if (timePickers.length) {
            flatpickr(timePickers, {
                enableTime: true,
                noCalendar: true,
                dateFormat: 'H:i',
                time_24hr: true,
                minuteIncrement: 15
            });
        }
    };

    // Initialize all date pickers
    initializePickers();

    // Re-initialize on dynamic content load
    document.addEventListener('contentLoaded', initializePickers);

    // Handle date range validation
    const validateDateRange = (startDate, endDate) => {
        if (!startDate || !endDate) return true;
        return new Date(startDate) <= new Date(endDate);
    };

    // Add event listeners for date range validation
    document.querySelectorAll('.date-range-group').forEach(group => {
        const startInput = group.querySelector('.start-date');
        const endInput = group.querySelector('.end-date');
        
        if (startInput && endInput) {
            [startInput, endInput].forEach(input => {
                input.addEventListener('change', () => {
                    const isValid = validateDateRange(startInput.value, endInput.value);
                    if (!isValid) {
                        endInput.value = '';
                        alert('End date must be after start date');
                    }
                });
            });
        }
    });
};
