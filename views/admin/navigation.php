<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<nav class="bg-gray-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="flex-shrink-0 flex items-center">
                    <img class="h-8 w-auto" src="<?= BASE_PATH ?>/assets/images/logo.png" alt="Summit Logo">
                </div>
                <div class="hidden md:ml-6 md:flex md:items-center md:space-x-4">
                    <!-- Dashboard -->
                    <a href="<?= BASE_PATH ?>/admin/dashboard.php" 
                       class="<?= $currentPage === 'dashboard.php' ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' ?> px-3 py-2 rounded-md text-sm font-medium">
                        Dashboard
                    </a>

                    <!-- Registration Management -->
                    <div class="relative group">
                        <button class="<?= in_array($currentPage, ['registrations.php', 'verify-registrations.php']) ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' ?> px-3 py-2 rounded-md text-sm font-medium">
                            Registrations
                        </button>
                        <div class="absolute z-10 left-0 w-48 mt-2 origin-top-right bg-white rounded-md shadow-lg hidden group-hover:block">
                            <div class="py-1">
                                <a href="<?= BASE_PATH ?>/admin/registrations.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">View All</a>
                                <a href="<?= BASE_PATH ?>/admin/verify-registrations.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Verify Registrations</a>
                                <a href="<?= BASE_PATH ?>/admin/registration-settings.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Settings</a>
                            </div>
                        </div>
                    </div>

                    <!-- Meal Management -->
                    <div class="relative group">
                        <button class="<?= in_array($currentPage, ['validation-dashboard.php', 'meal-stats.php']) ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' ?> px-3 py-2 rounded-md text-sm font-medium">
                            Meals
                        </button>
                        <div class="absolute z-10 left-0 w-48 mt-2 origin-top-right bg-white rounded-md shadow-lg hidden group-hover:block">
                            <div class="py-1">
                                <a href="<?= BASE_PATH ?>/admin/validation-dashboard.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Validation Dashboard</a>
                                <a href="<?= BASE_PATH ?>/admin/meal-stats.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Meal Statistics</a>
                                <a href="<?= BASE_PATH ?>/admin/validation-team.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Validation Team</a>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Management -->
                    <div class="relative group">
                        <button class="<?= in_array($currentPage, ['payments.php', 'payment-settings.php']) ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' ?> px-3 py-2 rounded-md text-sm font-medium">
                            Payments
                        </button>
                        <div class="absolute z-10 left-0 w-48 mt-2 origin-top-right bg-white rounded-md shadow-lg hidden group-hover:block">
                            <div class="py-1">
                                <a href="<?= BASE_PATH ?>/admin/payments.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">View Payments</a>
                                <a href="<?= BASE_PATH ?>/admin/payment-settings.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Payment Settings</a>
                                <a href="<?= BASE_PATH ?>/admin/invoices.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Invoices</a>
                            </div>
                        </div>
                    </div>

                    <!-- Reports -->
                    <div class="relative group">
                        <button class="<?= in_array($currentPage, ['reports.php', 'custom-reports.php']) ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' ?> px-3 py-2 rounded-md text-sm font-medium">
                            Reports
                        </button>
                        <div class="absolute z-10 left-0 w-48 mt-2 origin-top-right bg-white rounded-md shadow-lg hidden group-hover:block">
                            <div class="py-1">
                                <a href="<?= BASE_PATH ?>/admin/reports.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Generate Reports</a>
                                <a href="<?= BASE_PATH ?>/admin/custom-reports.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Custom Reports</a>
                                <a href="<?= BASE_PATH ?>/admin/scheduled-reports.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Scheduled Reports</a>
                            </div>
                        </div>
                    </div>

                    <!-- Settings -->
                    <div class="relative group">
                        <button class="<?= in_array($currentPage, ['settings.php', 'users.php']) ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' ?> px-3 py-2 rounded-md text-sm font-medium">
                            Settings
                        </button>
                        <div class="absolute z-10 left-0 w-48 mt-2 origin-top-right bg-white rounded-md shadow-lg hidden group-hover:block">
                            <div class="py-1">
                                <a href="<?= BASE_PATH ?>/admin/users.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">User Management</a>
                                <a href="<?= BASE_PATH ?>/admin/roles.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Roles & Permissions</a>
                                <a href="<?= BASE_PATH ?>/admin/settings.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">System Settings</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Profile dropdown -->
            <div class="ml-3 relative group">
                <div>
                    <button class="bg-gray-800 flex text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-800 focus:ring-white">
                        <span class="sr-only">Open user menu</span>
                        <img class="h-8 w-8 rounded-full" src="<?= BASE_PATH ?>/assets/images/default-avatar.png" alt="">
                    </button>
                </div>
                <div class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 hidden group-hover:block">
                    <a href="<?= BASE_PATH ?>/admin/profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Your Profile</a>
                    <a href="<?= BASE_PATH ?>/admin/activity-logs.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Activity Log</a>
                    <a href="<?= BASE_PATH ?>/logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Sign out</a>
                </div>
            </div>
        </div>
    </div>
</nav>
