<?php
require_once '../core/MealManager.php';
require_once '../auth/Auth.php';

// Ensure user is logged in and has validation team role
if (!Auth::isValidationTeam()) {
    header('HTTP/1.1 403 Forbidden');
    exit('Unauthorized access');
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $mealManager = MealManager::getInstance();
        
        switch ($_GET['action'] ?? '') {
            case 'stats':
                $stats = $mealManager->getMealStats();
                $response = ['morning' => 0, 'evening' => 0];
                foreach ($stats as $stat) {
                    $response[$stat['meal_type']] = $stat['total_served'];
                }
                echo json_encode($response);
                break;

            case 'recent':
                $validations = $mealManager->getRecentValidations(10); // Get last 10 validations
                echo json_encode(['validations' => $validations]);
                break;

            case 'team_stats':
                $teamStats = $mealManager->getTeamStats();
                echo json_encode(['team_members' => $teamStats]);
                break;

            default:
                throw new Exception('Invalid action');
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!isset($_POST['identifier']) || !isset($_POST['meal_type']) || !isset($_POST['validator_id'])) {
            throw new Exception('Missing required fields');
        }

        // Validate meal time
        $hour = (int)date('H');
        $mealType = $_POST['meal_type'];
        
        if ($mealType === 'morning' && ($hour < 6 || $hour >= 10)) {
            throw new Exception('Morning meal validation is only available from 06:00 to 10:00');
        }
        
        if ($mealType === 'evening' && ($hour < 17 || $hour >= 21)) {
            throw new Exception('Evening meal validation is only available from 17:00 to 21:00');
        }

        $mealManager = MealManager::getInstance();
        
        // Validate attendee
        $user = $mealManager->validateAttendee($_POST['identifier']);
        if (!$user) {
            throw new Exception('Invalid participant');
        }
        
        // Record meal with validator information
        $mealManager->recordMeal(
            $user['id'], 
            $_POST['meal_type'],
            $_POST['validator_id']
        );
        
        echo json_encode([
            'success' => true,
            'message' => 'Meal validated successfully',
            'participant' => [
                'name' => $user['name'],
                'id' => $user['id']
            ]
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
    exit;
}
