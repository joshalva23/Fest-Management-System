<?php
session_start();

if (!isset($_SESSION['user_id']) && !isset($_SESSION['organiser_id'])) {
    header('Location: index.php');
    exit;
}

include 'includes/db_connect.php';

$participant_id = isset($_GET['id']) ? $_GET['id'] : null;
$row = null; // Initialize $row
if ($participant_id) {
    try {
        // Query to join participants, events, and registrations
        $stmt = $pdo->prepare('
            SELECT p.participant_name, e.event_name, p.participant_phone 
            FROM participants p
            JOIN registrations r ON p.participant_id = r.participant_id
            JOIN events e ON r.event_id = e.event_id
            WHERE p.participant_id = :participant_id
        ');
        $stmt->execute(['participant_id' => $participant_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error_message = 'Database error: ' . htmlspecialchars($e->getMessage());
    }
} else {
    $error_message = 'Participant ID is missing.';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Participant Profile</title>
    <?php include 'includes/_links.php'; ?>
    <style>
        .profile-container {
            max-width: 300px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #f9f9f9;
            text-align: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .photo-placeholder {
            width: 150px; /* Adjust size as needed */
            height: 150px; /* Adjust size as needed */
            background-color: #e0e0e0; /* Light grey background */
            border: 2px dashed #007bff; /* Dashed border with color */
            border-radius: 8px; /* Rounded corners */
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            color: #007bff; /* Text color matching the border */
            font-weight: bold;
            font-size: 1rem;
            position: relative;
        }

        .photo-placeholder::after {
            content: 'Stick Photo Here';
            position: absolute;
            text-align: center;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        .participant-details {
            margin-top: 20px;
            text-align: left;
            display: inline-block;
            width: 100%;
        }

        .participant-details .detail {
            display: flex;
            justify-content: space-between;
            padding: 10px 20px;
            border-bottom: 1px solid #ddd;
        }

        .participant-details .detail:last-child {
            border-bottom: none;
        }

        @media (max-width: 768px) {
            .participant-details {
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/_navbar.php'; ?>

    <main class="bg-light">
        <div class="container py-5">
            <div class="profile-container">
                <?php if ($row): ?>
                    <!-- Photo Placeholder -->
                    <div class="photo-placeholder">
                        <!-- Placeholder for participant photo -->
                    </div>

                    <!-- Participant Details -->
                    <div class="participant-details">
                    <div style="text-align:center">
                        <h5>Participant Profile</h5>
                    </div>
                        <div class="detail">
                            <span><strong>Name:</strong></span>
                            <span><?php echo htmlspecialchars($row['participant_name']); ?></span>
                        </div>
                        <div class="detail">
                            <span><strong>Event:</strong></span>
                            <span><?php echo htmlspecialchars($row['event_name']); ?></span>
                        </div>
                        <div class="detail">
                            <span><strong>Phone:</strong></span>
                            <span><?php echo htmlspecialchars($row['participant_phone']); ?></span>
                        </div>
                    </div>
                <?php else: ?>
                    <p class="text-danger"><?php echo htmlspecialchars($error_message); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php include 'includes/_scripts.php'; ?>
</body>
</html>


