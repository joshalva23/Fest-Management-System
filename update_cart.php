<?php
session_start();

include_once 'includes/db_connect.php'; // Ensure this file contains the PDO connection setup

if (isset($_POST['type']) && isset($_POST['event_id'])) {
    $event_id = $_POST['event_id'];

    try {
        if ($_POST['type'] == 'add') {
            // Prepare and execute query to get event details
            $query = "SELECT event_name, event_fee, event_type FROM events WHERE event_id = :event_id";
            $stmt = $pdo->prepare($query);
            $stmt->execute([':event_id' => $event_id]);
            
            if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $_SESSION['cart'][$event_id]['event_name'] = $row['event_name'];
                $_SESSION['cart'][$event_id]['event_fee'] = $row['event_fee'];
                $_SESSION['cart'][$event_id]['event_type'] = $row['event_type'];
            }

            header('Location: select_events.php');
            exit;
        }

        if ($_POST['type'] == 'remove') {
            if (isset($_SESSION['cart'][$event_id])) {
                unset($_SESSION['cart'][$event_id]);
            }

            header('Location: select_events.php');
            exit;
        }
    } catch (PDOException $e) {
        // Handle potential errors
        $error_message = 'Database error: ' . $e->getMessage();
        // Optionally, redirect to an error page or log the error
        // header('Location: error_page.php?error=' . urlencode($error_message));
    }
}


