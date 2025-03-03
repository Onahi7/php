<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? SITE_NAME ?></title>
    
    <!-- Inline critical CSS -->
    <style>
        /* Critical CSS for above-the-fold content */
        :root {
            --primary: #2563eb;
            --bg-primary: #ffffff;
            --text-primary: #1e293b;
        }
        
        body {
            margin: 0;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            line-height: 1.5;
            color: var(--text-primary);
            background: var(--bg-primary);
            overflow-x: hidden;
        }
        
        .header {
            position: sticky;
            top: 0;
            background: var(--bg-primary);
            box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1);
            z-index: 40;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }
        
        .container {
            width: 100%;
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }
    </style>
    
    <!-- Preload fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    </noscript>
    
    <!-- Defer non-critical CSS -->
    <link rel="preload" href="<?= BASE_PATH ?>/assets/css/styles.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript>
        <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/css/styles.css">
    </noscript>
    
    <!-- Defer Tailwind -->
    <link rel="preload" href="https://cdn.tailwindcss.com" as="script">
    <script src="https://cdn.tailwindcss.com" defer></script>
    
    <!-- Defer Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Theme configuration -->
    <script>
        // Check for saved theme preference or respect OS preference
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
        
        // Define Tailwind configuration
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                            950: '#172554',
                        }
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.5s ease-out',
                        'slide-up': 'slideUp 0.5s ease-out',
                        'slide-down': 'slideDown 0.5s ease-out',
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0' },
                            '100%': { opacity: '1' },
                        },
                        slideUp: {
                            '0%': { transform: 'translateY(20px)', opacity: '0' },
                            '100%': { transform: 'translateY(0)', opacity: '1' },
                        },
                        slideDown: {
                            '0%': { transform: 'translateY(-20px)', opacity: '0' },
                            '100%': { transform: 'translateY(0)', opacity: '1' },
                        },
                    },
                }
            }
        }
    </script>
</head>
<body class="min-h-screen bg-gray-50 dark:bg-gray-900 font-sans antialiased transition-colors duration-300" 
      x-data="{ mobileMenuOpen: false, darkMode: localStorage.getItem('theme') === 'dark' }"
      :class="{ 'overflow-hidden': mobileMenuOpen }">
    <?php require_once __DIR__ . '/../components/header.php'; ?>
    
    <main class="container mx-auto px-4 py-8 animate-fade-in">
        <?php if (isset($_SESSION['flash'])): ?>
            <div x-data="{ show: true }" 
                 x-show="show" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform -translate-y-2"
                 x-transition:enter-end="opacity-100 transform translate-y-0"
                 x-transition:leave="transition ease-in duration-300"
                 x-transition:leave-start="opacity-100 transform translate-y-0"
                 x-transition:leave-end="opacity-0 transform -translate-y-2"
                 x-init="setTimeout(() => show = false, 5000)"
                 class="mb-6 rounded-lg p-4 shadow-md border-l-4 flex items-center justify-between
                        <?= $_SESSION['flash']['type'] === 'success' 
                            ? 'bg-green-50 border-green-500 text-green-700 dark:bg-green-900/30 dark:text-green-300' 
                            : 'bg-red-50 border-red-500 text-red-700 dark:bg-red-900/30 dark:text-red-300' ?>">
                <div class="flex items-center">
                    <?php if ($_SESSION['flash']['type'] === 'success'): ?>
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                    <?php else: ?>
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                    <?php endif; ?>
                    <?= $_SESSION['flash']['message'] ?>
                </div>
                <button @click="show = false" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 focus:outline-none">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <?php unset($_SESSION['flash']); ?>
        <?php endif; ?>

        <?php require_once $viewPath; ?>
    </main>

    <?php require_once __DIR__ . '/../components/footer.php'; ?>

    <!-- Defer main JavaScript -->
    <script type="module" src="<?= BASE_PATH ?>/assets/js/main.js" defer></script>
</body>
</html>
