<header class="bg-white dark:bg-gray-800 shadow-sm sticky top-0 z-40 backdrop-blur-md backdrop-saturate-150 bg-white/80 dark:bg-gray-800/80 border-b border-gray-200 dark:border-gray-700 transition-colors duration-300">
    <div class="container mx-auto">
        <nav class="px-4 py-3" x-data="{ open: false }">
            <div class="flex justify-between items-center">
                <div class="flex items-center">
                    <a href="<?= BASE_PATH ?>/" class="flex items-center space-x-2">
                        <span class="text-xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">NCES</span>
                        <span class="hidden sm:inline-block text-sm font-medium text-gray-600 dark:text-gray-300">2025</span>
                    </a>
                </div>

                <!-- Desktop menu -->
                <div class="hidden md:flex items-center space-x-1">
                    <a href="<?= BASE_PATH ?>/" class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-blue-600 hover:bg-gray-100 dark:text-gray-200 dark:hover:text-white dark:hover:bg-gray-700 transition-colors duration-200">Home</a>
                    
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="<?= BASE_PATH ?>/dashboard" class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-blue-600 hover:bg-gray-100 dark:text-gray-200 dark:hover:text-white dark:hover:bg-gray-700 transition-colors duration-200">Dashboard</a>
                        <a href="<?= BASE_PATH ?>/profile" class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-blue-600 hover:bg-gray-100 dark:text-gray-200 dark:hover:text-white dark:hover:bg-gray-700 transition-colors duration-200">Profile</a>
                        <form action="<?= BASE_PATH ?>/logout" method="POST" class="inline">
                            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                            <button type="submit" class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-blue-600 hover:bg-gray-100 dark:text-gray-200 dark:hover:text-white dark:hover:bg-gray-700 transition-colors duration-200">Logout</button>
                        </form>
                    <?php else: ?>
                        <a href="<?= BASE_PATH ?>/login" class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-blue-600 hover:bg-gray-100 dark:text-gray-200 dark:hover:text-white dark:hover:bg-gray-700 transition-colors duration-200">Login</a>
                        <a href="<?= BASE_PATH ?>/register" class="ml-2 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-sm transition-all duration-200">
                            Register
                        </a>
                    <?php endif; ?>
                    
                    <!-- Dark mode toggle -->
                    <button @click="darkMode = !darkMode; localStorage.theme = darkMode ? 'dark' : 'light'; document.documentElement.classList.toggle('dark')" 
                            class="p-2 rounded-full text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg x-show="!darkMode" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                        <svg x-show="darkMode" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </button>
                </div>

                <!-- Mobile menu button -->
                <div class="flex items-center md:hidden space-x-2">
                    <!-- Dark mode toggle for mobile -->
                    <button @click="darkMode = !darkMode; localStorage.theme = darkMode ? 'dark' : 'light'; document.documentElement.classList.toggle('dark')" 
                            class="p-2 rounded-full text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 focus:outline-none">
                        <svg x-show="!darkMode" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                        <svg x-show="darkMode" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </button>
                    
                    <button @click="open = !open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-700 hover:text-blue-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:text-white dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500 transition-colors duration-200">
                        <span class="sr-only">Open main menu</span>
                        <svg class="h-6 w-6" x-show="!open" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <svg class="h-6 w-6" x-show="open" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display: none;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Mobile menu -->
            <div x-show="open" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 transform -translate-y-2"
                 x-transition:enter-end="opacity-100 transform translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 transform translate-y-0"
                 x-transition:leave-end="opacity-0 transform -translate-y-2"
                 class="md:hidden mt-2" 
                 style="display: none;">
                <div class="px-2 pt-2 pb-3 space-y-1 rounded-md bg-white dark:bg-gray-800 shadow-lg border border-gray-200 dark:border-gray-700">
                    <a href="<?= BASE_PATH ?>/" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-blue-600 hover:bg-gray-100 dark:text-gray-200 dark:hover:text-white dark:hover:bg-gray-700 transition-colors duration-200">Home</a>
                    
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="<?= BASE_PATH ?>/dashboard" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-blue-600 hover:bg-gray-100 dark:text-gray-200 dark:hover:text-white dark:hover:bg-gray-700 transition-colors duration-200">Dashboard</a>
                        <a href="<?= BASE_PATH ?>/profile" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-blue-600 hover:bg-gray-100 dark:text-gray-200 dark:hover:text-white dark:hover:bg-gray-700 transition-colors duration-200">Profile</a>
                        <form action="<?= BASE_PATH ?>/logout" method="POST">
                            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                            <button type="submit" class="block w-full text-left px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-blue-600 hover:bg-gray-100 dark:text-gray-200 dark:hover:text-white dark:hover:bg-gray-700 transition-colors duration-200">Logout</button>
                        </form>
                    <?php else: ?>
                        <a href="<?= BASE_PATH ?>/login" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-blue-600 hover:bg-gray-100 dark:text-gray-200 dark:hover:text-white dark:hover:bg-gray-700 transition-colors duration-200">Login</a>
                        <a href="<?= BASE_PATH ?>/register" class="block px-3 py-2 rounded-md text-base font-medium text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 transition-colors duration-200">Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </div>
</header>
