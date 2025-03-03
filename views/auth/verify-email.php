<div class="min-h-[80vh] flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <h2 class="mt-6 text-center text-3xl font-bold tracking-tight text-gray-900">
            Email Verification
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
                <div class="rounded-md bg-green-50 p-4 mb-4">
                    <div class="flex">
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-green-800">
                                Success
                            </h3>
                            <div class="mt-2 text-sm text-green-700">
                                <p>Your email has been verified successfully!</p>
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
            <?php endif; ?>

            <?php if (!isset($success) && !isset($error)): ?>
                <p class="text-sm text-gray-600">
                    Verifying your email...
                </p>
            <?php endif; ?>
        </div>
    </div>
</div>

