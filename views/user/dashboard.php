<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 transition-all duration-300">
    <!-- Welcome Card -->
    <div class="bg-white dark:bg-gray-800 shadow-lg rounded-xl p-6 mb-8 transition-all duration-300 transform hover:shadow-xl">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div class="mb-4 md:mb-0">
                <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white transition-colors duration-300">
                    Welcome, <?= htmlspecialchars($user['name']) ?>
                </h1>
                <p class="mt-1 text-gray-500 dark:text-gray-400 transition-colors duration-300">
                    Here's an overview of your Education Summit participation
                </p>
            </div>
            <div class="flex space-x-3">
                <span class="inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium bg-indigo-100 dark:bg-indigo-900 text-indigo-800 dark:text-indigo-200 transition-colors duration-300">
                    <svg class="-ml-1 mr-1.5 h-2 w-2 text-indigo-600 dark:text-indigo-400" fill="currentColor" viewBox="0 0 8 8">
                        <circle cx="4" cy="4" r="3" />
                    </svg>
                    Attendee
                </span>
                <span class="inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 transition-colors duration-300">
                    <svg class="-ml-1 mr-1.5 h-2 w-2 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 8 8">
                        <circle cx="4" cy="4" r="3" />
                    </svg>
                    <?= date('Y') ?>
                </span>
            </div>
        </div>
    </div>
    
    <!-- Status Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Registration Status -->
        <div class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/30 dark:to-blue-800/30 rounded-xl p-6 shadow-md transition-all duration-300 transform hover:translate-y-[-5px] hover:shadow-lg">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-blue-500 dark:bg-blue-600 rounded-md p-3 transition-colors duration-300">
                    <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-medium text-blue-900 dark:text-blue-300 transition-colors duration-300">Registration Status</h3>
                    <p class="mt-1 text-2xl font-bold text-blue-600 dark:text-blue-400 transition-colors duration-300">
                        <?= $user['registration_status'] ?>
                    </p>
                </div>
            </div>
            <div class="mt-4">
                <div class="w-full bg-blue-200 dark:bg-blue-700 rounded-full h-2.5 transition-colors duration-300">
                    <div class="bg-blue-600 dark:bg-blue-400 h-2.5 rounded-full transition-colors duration-300" style="width: <?= $user['registration_status'] === 'Confirmed' ? '100%' : '50%' ?>"></div>
                </div>
            </div>
        </div>
        
        <!-- Payment Status -->
        <div class="bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/30 dark:to-green-800/30 rounded-xl p-6 shadow-md transition-all duration-300 transform hover:translate-y-[-5px] hover:shadow-lg">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-green-500 dark:bg-green-600 rounded-md p-3 transition-colors duration-300">
                    <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-medium text-green-900 dark:text-green-300 transition-colors duration-300">Payment Status</h3>
                    <p class="mt-1 text-2xl font-bold text-green-600 dark:text-green-400 transition-colors duration-300">
                        <?= $user['payment_status'] ?>
                    </p>
                </div>
            </div>
            <div class="mt-4">
                <div class="w-full bg-green-200 dark:bg-green-700 rounded-full h-2.5 transition-colors duration-300">
                    <div class="bg-green-600 dark:bg-green-400 h-2.5 rounded-full transition-colors duration-300" style="width: <?= $user['payment_status'] === 'paid' ? '100%' : '0%' ?>"></div>
                </div>
            </div>
        </div>
        
        <!-- Days Until Summit -->
        <div class="bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/30 dark:to-purple-800/30 rounded-xl p-6 shadow-md transition-all duration-300 transform hover:translate-y-[-5px] hover:shadow-lg">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-purple-500 dark:bg-purple-600 rounded-md p-3 transition-colors duration-300">
                    <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-medium text-purple-900 dark:text-purple-300 transition-colors duration-300">Days Until Summit</h3>
                    <p class="mt-1 text-2xl font-bold text-purple-600 dark:text-purple-400 transition-colors duration-300">
                        <?= ceil((strtotime(SUMMIT_DATE) - time()) / (60 * 60 * 24)) ?>
                    </p>
                </div>
            </div>
            <div class="mt-4">
                <div class="w-full bg-purple-200 dark:bg-purple-700 rounded-full h-2.5 transition-colors duration-300">
                    <?php
                    $totalDays = 90; // Assuming 90 days countdown
                    $daysLeft = ceil((strtotime(SUMMIT_DATE) - time()) / (60 * 60 * 24));
                    $percentage = max(0, min(100, 100 - ($daysLeft / $totalDays * 100)));
                    ?>
                    <div class="bg-purple-600 dark:bg-purple-400 h-2.5 rounded-full transition-colors duration-300" style="width: <?= $percentage ?>%"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions & Upcoming Sessions -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Quick Actions -->
        <div class="lg:col-span-1">
            <div class="bg-white dark:bg-gray-800 shadow-lg rounded-xl p-6 transition-all duration-300 transform hover:shadow-xl">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4 transition-colors duration-300">Quick Actions</h2>
                
                <div class="space-y-3">
                    <a href="<?= BASE_PATH ?>/profile" 
                       class="group flex items-center w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors duration-300">
                        <svg class="mr-3 h-5 w-5 text-gray-400 dark:text-gray-500 group-hover:text-indigo-500 dark:group-hover:text-indigo-400 transition-colors duration-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        Update Profile
                    </a>
                    
                    <?php if ($user['payment_status'] !== 'paid'): ?>
                        <a href="<?= BASE_PATH ?>/payment" 
                           class="group flex items-center w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors duration-300">
                            <svg class="mr-3 h-5 w-5 text-gray-400 dark:text-gray-500 group-hover:text-green-500 dark:group-hover:text-green-400 transition-colors duration-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                            Make Payment
                        </a>
                    <?php endif; ?>
                    
                    <a href="<?= BASE_PATH ?>/schedule" 
                       class="group flex items-center w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors duration-300">
                        <svg class="mr-3 h-5 w-5 text-gray-400 dark:text-gray-500 group-hover:text-purple-500 dark:group-hover:text-purple-400 transition-colors duration-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        View Schedule
                    </a>
                    
                    <a href="<?= BASE_PATH ?>/speakers" 
                       class="group flex items-center w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors duration-300">
                        <svg class="mr-3 h-5 w-5 text-gray-400 dark:text-gray-500 group-hover:text-yellow-500 dark:group-hover:text-yellow-400 transition-colors duration-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        Browse Speakers
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Upcoming Sessions -->
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-gray-800 shadow-lg rounded-xl p-6 transition-all duration-300 transform hover:shadow-xl">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4 transition-colors duration-300">Upcoming Sessions</h2>
                
                <div class="space-y-4">
                    <!-- Session 1 -->
                    <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg transition-all duration-300 transform hover:translate-x-1">
                        <div class="flex justify-between">
                            <div>
                                <h3 class="text-md font-medium text-gray-900 dark:text-white transition-colors duration-300">Opening Keynote: The Future of Education</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 transition-colors duration-300">Dr. Maria Rodriguez</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-medium text-indigo-600 dark:text-indigo-400 transition-colors duration-300">Day 1</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400 transition-colors duration-300">9:00 AM - 10:30 AM</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Session 2 -->
                    <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg transition-all duration-300 transform hover:translate-x-1">
                        <div class="flex justify-between">
                            <div>
                                <h3 class="text-md font-medium text-gray-900 dark:text-white transition-colors duration-300">Workshop: Innovative Teaching Methods</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 transition-colors duration-300">Dr. James Chen</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-medium text-indigo-600 dark:text-indigo-400 transition-colors duration-300">Day 1</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400 transition-colors duration-300">11:00 AM - 12:30 PM</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Session 3 -->
                    <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg transition-all duration-300 transform hover:translate-x-1">
                        <div class="flex justify-between">
                            <div>
                                <h3 class="text-md font-medium text-gray-900 dark:text-white transition-colors duration-300">Panel Discussion: Technology in Education</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 transition-colors duration-300">Multiple Speakers</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-medium text-indigo-600 dark:text-indigo-400 transition-colors duration-300">Day 2</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400 transition-colors duration-300">10:00 AM - 11:30 AM</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-6 text-center">
                    <a href="<?= BASE_PATH ?>/schedule" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 dark:bg-indigo-700 hover:bg-indigo-700 dark:hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-300">
                        View Full Schedule
                        <svg class="ml-2 -mr-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <?php
    require_once __DIR__ . '/../../includes/helpers/barcode_helper.php';
    require_once __DIR__ . '/../../includes/handlers/meal_ticket_handler.php';

    $barcodeHelper = new BarcodeHelper();
    $barcodeData = $barcodeHelper->generateParticipantBarcode($user['id']);

    $db = new PDO("mysql:host=localhost;dbname=summit_db", "username", "password");
    $mealTicketHandler = new MealTicketHandler($db);

    // Get or generate today's meal ticket
    $today = date('Y-m-d');
    $mealTicket = $mealTicketHandler->hasValidTicket($user['id'], $today);
    if (!$mealTicket && $user['payment_status'] === 'paid') {
        $ticketCode = $mealTicketHandler->generateMealTicket($user['id'], $today);
    } else {
        $ticketCode = $mealTicket['ticket_code'] ?? null;
    }
    ?>
    <!-- Participant Tag & Barcode Section -->
    <?php if ($user['payment_status'] === 'paid'): ?>
    <div class="mt-8">
        <div class="bg-white dark:bg-gray-800 shadow-lg rounded-xl p-6 transition-all duration-300 transform hover:shadow-xl">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white transition-colors duration-300">Your Summit Pass</h2>
                    <p class="mt-1 text-gray-500 dark:text-gray-400 transition-colors duration-300">
                        Use this pass for check-in and session attendance
                    </p>
                </div>
                <div class="mt-4 md:mt-0 flex space-x-3">
                    <button onclick="printTag()" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-300">
                        <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Print Tag
                    </button>
                    <button onclick="downloadTag()" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-300">
                        <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Download Tag
                    </button>
                </div>
            </div>

            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6 flex flex-col md:flex-row items-center justify-between">
                <div class="mb-4 md:mb-0">
                    <div class="text-center md:text-left">
                        <img src="data:image/png;base64,<?= $barcodeData['barcode_image'] ?>" alt="Participant Barcode" class="max-w-xs mx-auto md:mx-0">
                        <p class="mt-2 text-sm font-mono text-gray-600 dark:text-gray-400"><?= $barcodeData['barcode_number'] ?></p>
                    </div>
                </div>
                <div class="flex-1 max-w-sm mx-auto md:mx-4">
                    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm">
                        <div class="text-center">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white"><?= htmlspecialchars($user['name']) ?></h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Participant ID: <?= str_pad($user['id'], 6, '0', STR_PAD_LEFT) ?></p>
                        </div>
                        <div class="mt-4 text-center text-sm text-gray-600 dark:text-gray-400">
                            <p>Valid for all sessions on</p>
                            <p class="font-semibold">March 25th, 2025</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Meal Ticket Section -->
    <?php if ($user['payment_status'] === 'paid'): ?>
    <div class="mt-8">
        <div class="bg-white dark:bg-gray-800 shadow-lg rounded-xl p-6 transition-all duration-300 transform hover:shadow-xl">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white transition-colors duration-300">Today's Meal Ticket</h2>
                    <p class="mt-1 text-gray-500 dark:text-gray-400 transition-colors duration-300">
                        Valid for today's meals only
                    </p>
                </div>
            </div>

            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6">
                <?php if ($ticketCode): ?>
                    <div class="text-center">
                        <div class="mb-4">
                            <img src="data:image/png;base64,<?= $barcodeHelper->generateParticipantBarcode($ticketCode)['barcode_image'] ?>" 
                                 alt="Meal Ticket Barcode" 
                                 class="max-w-xs mx-auto">
                            <p class="mt-2 text-sm font-mono text-gray-600 dark:text-gray-400"><?= $ticketCode ?></p>
                        </div>
                        <div class="mt-4 text-sm text-gray-600 dark:text-gray-400">
                            <p>Valid for <?= date('F j, Y') ?></p>
                            <p class="mt-1">Present this code to the meal service staff</p>
                        </div>
                        <button onclick="printMealTicket('<?= $ticketCode ?>')" 
                                class="mt-4 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-300">
                            <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                            </svg>
                            Print Meal Ticket
                        </button>
                    </div>
                <?php else: ?>
                    <div class="text-center text-gray-600 dark:text-gray-400">
                        <p>No meal ticket available for today.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Print Styles -->
<style media="print">
    body * {
        visibility: hidden;
    }
    .participant-tag, .participant-tag * {
        visibility: visible;
    }
    .participant-tag {
        position: absolute;
        left: 0;
        top: 0;
    }
</style>

<!-- Tag Generation Scripts -->
<script>
function printTag() {
    const tagHtml = `<?= $barcodeHelper->generateParticipantTag($user, $barcodeData['barcode_number']) ?>`;
    const printWindow = window.open('', '', 'width=800,height=600');
    printWindow.document.write('<html><head><title>Print Tag</title></head><body>');
    printWindow.document.write(tagHtml);
    printWindow.document.write('</body></html>');
    printWindow.document.close();
    printWindow.focus();
    setTimeout(() => {
        printWindow.print();
        printWindow.close();
    }, 250);
}

function downloadTag() {
    const tagHtml = `<?= $barcodeHelper->generateParticipantTag($user, $barcodeData['barcode_number']) ?>`;
    const blob = new Blob([tagHtml], { type: 'text/html' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'summit-tag-<?= str_pad($user['id'], 6, '0', STR_PAD_LEFT) ?>.html';
    document.body.appendChild(a);
    a.click();
    window.URL.revokeObjectURL(url);
    document.body.removeChild(a);
}

function printMealTicket(ticketCode) {
    const ticketHtml = `
        <div style="width: 3.5in; padding: 0.2in; font-family: Arial, sans-serif; text-align: center;">
            <h2 style="margin: 0; color: #2563eb;">Education Summit - Meal Ticket</h2>
            <p style="margin: 5px 0; color: #666;">${new Date().toLocaleDateString()}</p>
            <div style="margin: 15px 0;">
                <img src="data:image/png;base64,${document.querySelector('img[alt="Meal Ticket Barcode"]').src.split(',')[1]}" 
                     alt="Meal Ticket Barcode" style="max-width: 200px;">
                <p style="margin: 5px 0; font-family: monospace;">${ticketCode}</p>
            </div>
            <p style="margin: 5px 0; color: #666;">Valid for today's meals only</p>
            <p style="margin: 5px 0; color: #666;">Present to meal service staff</p>
        </div>
    `;

    const printWindow = window.open('', '', 'width=800,height=600');
    printWindow.document.write('<html><head><title>Print Meal Ticket</title></head><body>');
    printWindow.document.write(ticketHtml);
    printWindow.document.write('</body></html>');
    printWindow.document.close();
    printWindow.focus();
    setTimeout(() => {
        printWindow.print();
        printWindow.close();
    }, 250);
}
</script>
