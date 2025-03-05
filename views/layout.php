<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>North Central Education Summit 2025</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.9.0/dist/cdn.min.js" defer></script>
</head>
<body class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100">
    <header class="bg-white dark:bg-gray-800 shadow-sm">
        <div class="container mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-2">
                    <a href="<?= BASE_PATH ?>/" class="text-xl font-bold text-blue-600 dark:text-blue-400">
                        Summit 2025
                    </a>
                </div>
                <nav class="hidden md:flex space-x-8">
                    <a href="<?= BASE_PATH ?>/" class="text-gray-700 hover:text-blue-600 dark:text-gray-300 dark:hover:text-blue-400">Home</a>
                    <?php if (is_logged_in()): ?>
                        <?php if (has_role('admin')): ?>
                            <a href="<?= BASE_PATH ?>/admin" class="text-gray-700 hover:text-blue-600 dark:text-gray-300 dark:hover:text-blue-400">Admin</a>
                        <?php else: ?>
                            <a href="<?= BASE_PATH ?>/dashboard" class="text-gray-700 hover:text-blue-600 dark:text-gray-300 dark:hover:text-blue-400">Dashboard</a>
                        <?php endif; ?>
                        <a href="<?= BASE_PATH ?>/logout" class="text-gray-700 hover:text-blue-600 dark:text-gray-300 dark:hover:text-blue-400">Logout</a>
                    <?php else: ?>
                        <a href="<?= BASE_PATH ?>/login" class="text-gray-700 hover:text-blue-600 dark:text-gray-300 dark:hover:text-blue-400">Login</a>
                    <?php endif; ?>
                </nav>
            </div>
        </div>
    </header>

    <main>
        <?php if (isset($_SESSION['flash_message'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4 container mx-auto mt-4" role="alert">
                <span class="block sm:inline"><?= $_SESSION['flash_message'] ?></span>
                <?php unset($_SESSION['flash_message']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4 container mx-auto mt-4" role="alert">
                <span class="block sm:inline"><?= $_SESSION['flash_error'] ?></span>
                <?php unset($_SESSION['flash_error']); ?>
            </div>
        <?php endif; ?>

        <?= $content ?>
    </main>

    <footer class="bg-gray-100 dark:bg-gray-800">
        <div class="container mx-auto px-4 py-8">
            <div class="text-center">
                <p class="text-gray-600 dark:text-gray-400">
                    &copy; 2025 North Central Education Summit. All rights reserved.
                </p>
            </div>
        </div>
    </footer>
</body>
</html>
