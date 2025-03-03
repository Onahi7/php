<div class="max-w-3xl mx-auto">
    <div class="bg-white shadow-sm rounded-lg overflow-hidden">
        <div class="p-6">
            <h1 class="text-2xl font-bold text-gray-900 mb-6">Profile Settings</h1>
            
            <form action="<?= BASE_PATH ?>/profile" method="POST" enctype="multipart/form-data" class="space-y-6">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                
                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
                        <input type="text" id="name" name="name" value="<?= htmlspecialchars($user['name']) ?>" required 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700">Phone</label>
                        <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Passport Photo</label>
                        <div class="mt-1 flex items-center">
                            <?php if (!empty($user['passport'])): ?>
                                <div class="relative">
                                    <img src="<?= BASE_PATH ?>/uploads/passports/<?= $user['passport'] ?>" 
                                         alt="Current passport" 
                                         class="h-32 w-32 object-cover rounded-full">
                                    <div class="absolute inset-0 bg-black bg-opacity-40 flex items-center justify-center rounded-full opacity-0 hover:opacity-100 transition-opacity">
                                        <span class="text-white text-sm">Change photo</span>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <input type="file" id="passport" name="passport" accept="image/*" 
                                   class="ml-5 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" 
                            class="inline-flex justify-center rounded-md border border-transparent bg-blue-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        Update Profile
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

