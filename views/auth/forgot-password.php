<div class="min-h-[80vh] flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <h2 class="mt-6 text-center text-3xl font-bold tracking-tight text-gray-900">
            Reset your password
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
            Enter your email address and we'll send you a link to reset your password.
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
            <?php if (isset($success)): ?>
                <div class="rounded-md bg-green-50 p-4">
                    <div class="flex">
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-green-800">
                                Check your email
                            </h3>
                            <div class="mt-2 text-sm text-green-700">
                                <p>If an account exists for that email, we've sent password reset instructions.</p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <form class="space-y-6" action="<?= BASE_PATH ?>/forgot-password" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken() ?>">

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">
                            Email address
                        </label>
                        <div class="mt-1">
                            <input id="email" name="email" type="email" autocomplete="email" required
                                   class="block w-full appearance-none rounded-md border border-gray-300 px-3 py-2 placeholder-gray-400 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-blue-500 sm:text-sm">
                        </div>
                    </div>

                    <div>
                        <button type="submit"
                                class="flex w-full justify-center rounded-md border border-transparent bg-blue-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            Send reset link
                        </button>
                    </div>
                </form>
            <?php endif; ?>

            <div class="mt-6">
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="bg-white px-2 text-gray-500">
                            Or
                        </span>
                    </div>
                </div>

                <div class="mt-6 text-center">
                    <a href="<?= BASE_PATH ?>/login" class="font-medium text-blue-600 hover:text-blue-500">
                        Return to login
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

