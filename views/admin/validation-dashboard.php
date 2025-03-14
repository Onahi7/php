<?php
require_once '../../includes/core/MealManager.php';
require_once '../../includes/auth/Auth.php';

// Ensure user is logged in and has validation team role
if (!Auth::isValidationTeam()) {
    header('Location: ' . BASE_PATH . '/admin/login.php');
    exit;
}

$mealManager = MealManager::getInstance();
?>

<div class="grid grid-cols-12 gap-6">
    <!-- Meal Validation Section -->
    <div class="col-span-12 lg:col-span-8">
        <div class="bg-white shadow-sm rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-900">Meal Validation</h2>
                    <div class="text-sm text-gray-500">
                        Validation Team: <?= htmlspecialchars($_SESSION['user']['name']) ?>
                    </div>
                </div>

                <!-- Meal Type Selection -->
                <div class="mb-6">
                    <div class="flex space-x-4">
                        <button onclick="setMealType('morning')" 
                                class="flex-1 px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
                            <span class="block text-lg font-semibold">Morning Meal</span>
                            <span class="block text-sm opacity-75">06:00 - 10:00</span>
                        </button>
                        <button onclick="setMealType('evening')" 
                                class="flex-1 px-4 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 transition-colors">
                            <span class="block text-lg font-semibold">Evening Meal</span>
                            <span class="block text-sm opacity-75">17:00 - 21:00</span>
                        </button>
                    </div>
                    <div id="selectedMeal" class="mt-3 text-center text-lg font-medium text-gray-700"></div>
                </div>

                <!-- Validation Form -->
                <form id="validationForm" class="space-y-6">
                    <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken() ?>">
                    <input type="hidden" name="meal_type" id="mealType">
                    <input type="hidden" name="validator_id" value="<?= $_SESSION['user']['id'] ?>">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="identifier" class="block text-sm font-medium text-gray-700">Phone Number</label>
                            <div class="mt-1">
                                <input type="text" name="identifier" id="identifier" 
                                       class="block w-full px-3 py-2 rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                       placeholder="Enter participant's phone">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Barcode Scanner</label>
                            <button type="button" onclick="startScanner()" 
                                    class="mt-1 w-full inline-flex items-center justify-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                </svg>
                                Scan Barcode
                            </button>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" 
                                class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Validate Meal
                        </button>
                    </div>
                </form>

                <!-- Recent Validations -->
                <div class="mt-8">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Validations</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Participant</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Meal</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                </tr>
                            </thead>
                            <tbody id="recentValidations" class="bg-white divide-y divide-gray-200">
                                <!-- Filled dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Section -->
    <div class="col-span-12 lg:col-span-4 space-y-6">
        <!-- Today's Stats -->
        <div class="bg-white shadow-sm rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Today's Stats</h3>
                <div class="space-y-4">
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <h4 class="font-medium text-blue-700">Morning Meal</h4>
                        <p class="text-2xl font-bold text-blue-600" id="morningCount">0</p>
                        <div class="text-sm text-blue-500 flex justify-between">
                            <span>Validated</span>
                            <span id="morningTime">06:00 - 10:00</span>
                        </div>
                    </div>
                    <div class="bg-green-50 p-4 rounded-lg">
                        <h4 class="font-medium text-green-700">Evening Meal</h4>
                        <p class="text-2xl font-bold text-green-600" id="eveningCount">0</p>
                        <div class="text-sm text-green-500 flex justify-between">
                            <span>Validated</span>
                            <span id="eveningTime">17:00 - 21:00</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Validation Team Stats -->
        <div class="bg-white shadow-sm rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Team Performance</h3>
                <div id="teamStats" class="space-y-4">
                    <!-- Filled dynamically -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scanner Modal -->
<div id="scannerModal" class="hidden fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
    <div class="bg-white p-6 rounded-lg max-w-lg w-full mx-4">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium">Scan Participant's Barcode</h3>
            <button onclick="stopScanner()" class="text-gray-400 hover:text-gray-500">
                <span class="sr-only">Close</span>
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div id="scanner-container" class="aspect-video bg-gray-100 rounded-lg overflow-hidden"></div>
        <p class="mt-2 text-sm text-gray-500">Position the barcode within the scanner view</p>
    </div>
</div>

<script src="https://unpkg.com/@zxing/library@latest"></script>
<script>
let selectedMealType = null;
let codeReader = new ZXing.BrowserMultiFormatReader();
let scannerActive = false;

// Initialize the page
document.addEventListener('DOMContentLoaded', function() {
    updateStats();
    updateRecentValidations();
    updateTeamStats();
    checkMealTime();

    // Update stats every minute
    setInterval(() => {
        updateStats();
        updateRecentValidations();
        updateTeamStats();
        checkMealTime();
    }, 60000);

    // Set up form submission
    document.getElementById('validationForm').addEventListener('submit', function(e) {
        e.preventDefault();
        validateMeal();
    });
});

function checkMealTime() {
    const now = new Date();
    const hour = now.getHours();
    
    // Morning meal: 6:00 - 10:00
    // Evening meal: 17:00 - 21:00
    const isMorningTime = hour >= 6 && hour < 10;
    const isEveningTime = hour >= 17 && hour < 21;
    
    const morningBtn = document.querySelector('button[onclick="setMealType(\'morning\')"]');
    const eveningBtn = document.querySelector('button[onclick="setMealType(\'evening\')"]');
    
    morningBtn.disabled = !isMorningTime;
    eveningBtn.disabled = !isEveningTime;
    
    morningBtn.classList.toggle('opacity-50', !isMorningTime);
    eveningBtn.classList.toggle('opacity-50', !isEveningTime);

    if (!isMorningTime && !isEveningTime) {
        showNotification('Warning', 'Outside of meal service hours', 'warning');
    }
}

function setMealType(type) {
    selectedMealType = type;
    document.getElementById('mealType').value = type;
    document.getElementById('selectedMeal').textContent = type === 'morning' ? 'Morning Meal Selected' : 'Evening Meal Selected';
    
    const buttons = document.querySelectorAll('button[onclick^="setMealType"]');
    buttons.forEach(btn => {
        btn.classList.remove('ring-2', 'ring-offset-2');
        if (btn.getAttribute('onclick').includes(type)) {
            btn.classList.add('ring-2', 'ring-offset-2');
        }
    });
}

function validateMeal() {
    if (!selectedMealType) {
        showNotification('Error', 'Please select a meal type', 'error');
        return;
    }

    const form = document.getElementById('validationForm');
    const formData = new FormData(form);

    fetch('/includes/handlers/meal_handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Success', 'Meal validated successfully', 'success');
            form.reset();
            selectedMealType = null;
            document.getElementById('selectedMeal').textContent = '';
            updateStats();
            updateRecentValidations();
            updateTeamStats();
        } else {
            showNotification('Error', data.error, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error', 'Failed to validate meal', 'error');
    });
}

function updateStats() {
    fetch('/includes/handlers/meal_handler.php?action=stats')
        .then(response => response.json())
        .then(data => {
            document.getElementById('morningCount').textContent = data.morning || 0;
            document.getElementById('eveningCount').textContent = data.evening || 0;
        })
        .catch(error => console.error('Error:', error));
}

function updateRecentValidations() {
    fetch('/includes/handlers/meal_handler.php?action=recent')
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById('recentValidations');
            tbody.innerHTML = '';
            
            data.validations.forEach(validation => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        ${new Date(validation.served_at).toLocaleTimeString()}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        ${validation.participant_name}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        ${validation.meal_type.charAt(0).toUpperCase() + validation.meal_type.slice(1)}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                            Validated
                        </span>
                    </td>
                `;
                tbody.appendChild(row);
            });
        })
        .catch(error => console.error('Error:', error));
}

function updateTeamStats() {
    fetch('/includes/handlers/meal_handler.php?action=team_stats')
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('teamStats');
            container.innerHTML = '';
            
            data.team_members.forEach(member => {
                const total = member.validations_today || 0;
                const morning = member.morning_validations || 0;
                const evening = member.evening_validations || 0;
                
                const div = document.createElement('div');
                div.className = 'bg-gray-50 p-4 rounded-lg';
                div.innerHTML = `
                    <div class="flex justify-between items-center mb-2">
                        <h4 class="font-medium text-gray-700">${member.name}</h4>
                        <span class="text-sm text-gray-500">${total} today</span>
                    </div>
                    <div class="grid grid-cols-2 gap-2 text-sm">
                        <div class="text-blue-600">Morning: ${morning}</div>
                        <div class="text-green-600">Evening: ${evening}</div>
                    </div>
                `;
                container.appendChild(div);
            });
        })
        .catch(error => console.error('Error:', error));
}

function startScanner() {
    if (scannerActive) return;
    
    const modal = document.getElementById('scannerModal');
    modal.classList.remove('hidden');
    
    codeReader.decodeFromVideoDevice(null, 'scanner-container', (result, err) => {
        if (result) {
            document.getElementById('identifier').value = result.text;
            stopScanner();
            validateMeal();
        }
        if (err && !(err instanceof ZXing.NotFoundException)) {
            console.error(err);
        }
    }).then(() => {
        scannerActive = true;
    });
}

function stopScanner() {
    if (!scannerActive) return;
    
    codeReader.reset();
    scannerActive = false;
    document.getElementById('scannerModal').classList.add('hidden');
}

function showNotification(title, message, type) {
    // You can implement this using your preferred notification library
    // For now, we'll use a simple alert
    alert(`${title}: ${message}`);
}
</script>
