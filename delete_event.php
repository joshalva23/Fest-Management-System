<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?back=events.php');
    exit;
}

if (isset($_GET['id'])) {
    include 'includes/db_connect.php';

    $event_id = $_GET['id'];

    try {
        // Prepare and execute the deletion query
        $stmt = $pdo->prepare("DELETE FROM events WHERE event_id = :event_id");
        $stmt->execute(['event_id' => $event_id]);

        if ($stmt->rowCount() > 0) {
            header('Location: events.php');
        } else {
            header('Location: events.php?err=' . urlencode('Cannot delete the event.'));
        }
    } catch (PDOException $e) {
        // Handle any errors
        header('Location: events.php?err=' . urlencode('Database error: ' . $e->getMessage()));
    }

    exit;
}

