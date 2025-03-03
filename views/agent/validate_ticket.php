<?php
session_start();
require_once '../../includes/handlers/meal_ticket_handler.php';
require_once '../../includes/handlers/accreditation_handler.php';

// Check if agent is logged in
if (!isset($_SESSION['agent_id'])) {
    header('Location: login.php');
    exit();
}

$db = new PDO("mysql:host=localhost;dbname=summit_db", "username", "password");
$ticketHandler = new MealTicketHandler($db);
$accHandler = new AccreditationHandler($db);

$message = '';
$messageType = '';

// Handle ticket validation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ticket_code'])) {
    $ticketCode = trim($_POST['ticket_code']);
    
    if ($ticketHandler->useMealTicket($ticketCode)) {
        $accHandler->recordValidation($ticketCode, $_SESSION['agent_id']);
        $message = "Ticket validated successfully!";
        $messageType = "success";
    } else {
        $message = "Invalid or already used ticket!";
        $messageType = "error";
    }
}

// Get agent's recent validations
$recentValidations = $accHandler->getAgentHistory($_SESSION['agent_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meal Ticket Validation - Education Summit</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white shadow-lg rounded-lg p-6">
            <h1 class="text-2xl font-bold text-gray-900 mb-6">Meal Ticket Validation</h1>

            <?php if ($message): ?>
                <div class="mb-4 p-4 rounded-md <?= $messageType === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <!-- Ticket Validation Form -->
            <form method="POST" class="mb-8">
                <div class="max-w-xl">
                    <label for="ticket_code" class="block text-sm font-medium text-gray-700">Ticket Code</label>
                    <div class="mt-1 flex rounded-md shadow-sm">
                        <input type="text" name="ticket_code" id="ticket_code" 
                               class="flex-1 min-w-0 block w-full px-3 py-2 rounded-md border border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                               placeholder="Enter ticket code" required autofocus>
                        <button type="submit" 
                                class="ml-3 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Validate
                        </button>
                    </div>
                </div>
            </form>

            <!-- Recent Validations -->
            <div>
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Recent Validations</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ticket Code</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Validated At</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($recentValidations as $validation): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <?= htmlspecialchars($validation['ticket_code']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= htmlspecialchars($validation['validated_at']) ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-focus the ticket code input
        document.getElementById('ticket_code').focus();

        // Clear message after 5 seconds
        <?php if ($message): ?>
        setTimeout(() => {
            const messageDiv = document.querySelector('.<?= $messageType === "success" ? "bg-green-100" : "bg-red-100" ?>');
            if (messageDiv) {
                messageDiv.style.display = 'none';
            }
        }, 5000);
        <?php endif; ?>
    </script>
</body>
</html>
