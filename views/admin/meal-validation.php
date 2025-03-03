<?php
require_once '../../includes/core/MealManager.php';

if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
    header('HTTP/1.1 403 Forbidden');
    exit('Invalid CSRF token');
}

$mealManager = MealManager::getInstance();
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white shadow-sm rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Meal Validation</h2>

            <div class="mb-8">
                <div class="flex space-x-4 mb-4">
                    <button onclick="setMealType('morning')" 
                            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        Morning Meal
                    </button>
                    <button onclick="setMealType('evening')" 
                            class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                        Evening Meal
                    </button>
                </div>
                <div id="selectedMeal" class="text-lg font-medium text-gray-700 mb-4"></div>
            </div>

            <form id="validationForm" class="space-y-6">
                <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken() ?>">
                <input type="hidden" name="meal_type" id="mealType">

                <div>
                    <label for="identifier" class="block text-sm font-medium text-gray-700">Phone Number or Scan Barcode</label>
                    <div class="mt-1 flex rounded-md shadow-sm">
                        <input type="text" name="identifier" id="identifier" 
                               class="flex-1 min-w-0 block w-full px-3 py-2 rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                               placeholder="Enter phone number or scan barcode">
                        <button type="button" onclick="startScanner()" 
                                class="ml-3 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Scan
                        </button>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Validate
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Scanner Modal -->
    <div id="scannerModal" class="hidden fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center">
        <div class="bg-white p-6 rounded-lg max-w-lg w-full">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium">Scan Barcode</h3>
                <button onclick="stopScanner()" class="text-gray-400 hover:text-gray-500">
                    <span class="sr-only">Close</span>
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div id="scanner-container"></div>
        </div>
    </div>

    <!-- Stats Section -->
    <div class="mt-8 bg-white shadow-sm rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Today's Meal Stats</h3>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="font-medium text-gray-700">Morning Meal</h4>
                    <p class="text-2xl font-bold text-blue-600" id="morningCount">0</p>
                    <p class="text-sm text-gray-500">served today</p>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="font-medium text-gray-700">Evening Meal</h4>
                    <p class="text-2xl font-bold text-green-600" id="eveningCount">0</p>
                    <p class="text-sm text-gray-500">served today</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/@zxing/library@latest"></script>
<script>
let selectedMealType = null;
let codeReader = new ZXing.BrowserMultiFormatReader();

function setMealType(type) {
    selectedMealType = type;
    document.getElementById('mealType').value = type;
    document.getElementById('selectedMeal').textContent = 
        `Selected: ${type.charAt(0).toUpperCase() + type.slice(1)} Meal`;
}

function startScanner() {
    const modal = document.getElementById('scannerModal');
    modal.classList.remove('hidden');
    
    codeReader.decodeFromVideoDevice(null, 'scanner-container', (result, err) => {
        if (result) {
            document.getElementById('identifier').value = result.text;
            stopScanner();
        }
    });
}

function stopScanner() {
    codeReader.reset();
    document.getElementById('scannerModal').classList.add('hidden');
}

document.getElementById('validationForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    if (!selectedMealType) {
        alert('Please select a meal type first');
        return;
    }
    
    const formData = new FormData(this);
    
    fetch('/includes/handlers/meal_handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Success', 'Meal validated successfully', 'success');
            updateStats();
            this.reset();
            document.getElementById('selectedMeal').textContent = '';
            selectedMealType = null;
        } else {
            showNotification('Error', data.error, 'error');
        }
    })
    .catch(error => {
        showNotification('Error', 'An error occurred', 'error');
        console.error('Error:', error);
    });
});

function updateStats() {
    fetch('/includes/handlers/meal_handler.php?action=stats')
        .then(response => response.json())
        .then(data => {
            document.getElementById('morningCount').textContent = data.morning || 0;
            document.getElementById('eveningCount').textContent = data.evening || 0;
        });
}

// Initial stats update
updateStats();

// Update stats every minute
setInterval(updateStats, 60000);

function showNotification(title, message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg ${
        type === 'success' ? 'bg-green-500' : 
        type === 'error' ? 'bg-red-500' : 
        'bg-blue-500'
    } text-white max-w-md z-50`;
    
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

    setTimeout(() => {
        notification.remove();
    }, 5000);
}
</script>
