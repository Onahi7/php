<div class="min-h-[80vh] flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <h2 class="mt-6 text-center text-3xl font-bold tracking-tight text-gray-900">
            Reset your password
        </h2>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
            <?php if (isset($error)): ?>
                <div class="rounded-md bg-red-50 p-4 mb-4">
                    <div class="flex">
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">
                                Error
                            </h3>
                            <div class="mt-2 text-sm text-red-700">
                                <p><?= htmlspecialchars($error) ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (isset($success)): ?>
                <div class="rounded-md bg-green-50 p-4">
                    <div class="flex">
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-green-800">
                                Password reset successful
                            </h3>
                            <div class="mt-2 text-sm text-green-700">
                                <p>Your password has been reset successfully.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-6">
                    <a href="<?= BASE_PATH ?>/login" 
                       class="flex w-full justify-center rounded-md border border-transparent bg-blue-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        Continue to Login
                    </a>
                </div>
            <?php else: ?>
                <form class="space-y-6" action="<?= BASE_PATH ?>/reset-password" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken() ?>">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">
                            New password
                        </label>
                        <div class="mt-1">
                            <input id="password" name="password" type="password" required
                                   class="block w-full appearance-none rounded-md border border-gray-300 px-3 py-2 placeholder-gray-400 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-blue-500 sm:text-sm">
                        </div>
                    </div>

                    <div>
                        <label for="password_confirm" class="block text-sm font-medium text-gray-700">
                            Confirm new password
                        </label>
                        <div class="mt-1">
                            <input id="password_confirm" name="password_confirm" type="password" required
                                   class="block w-full appearance-none rounded-md border border-gray-300 px-3 py-2 placeholder-gray-400 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-blue-500 sm:text-sm">
                        </div>
                    </div>

                    <div>
                        <button type="submit"
                                class="flex w-full justify-center rounded-md border border-transparent bg-blue-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            Reset password
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

