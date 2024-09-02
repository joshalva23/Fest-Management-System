<?php
session_start();

include_once 'includes/db_connect.php'; // Ensure this file contains the PDO connection setup

$error_message = '';

if (isset($_GET['id'])) {
    $event_id = $_GET['id'];

    try {
        // Prepare and execute the query
        $query = 'SELECT * FROM events WHERE event_id = :event_id';
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':event_id', $event_id, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $event_name = htmlspecialchars($row['event_name']);
            // You can add more fields as needed, e.g., event_desc, event_date, etc.
        } else {
            $error_message = 'No such event found';
        }
    } catch (PDOException $e) {
        $error_message = 'Something went wrong: ' . htmlspecialchars($e->getMessage());
    }
} else {
    $error_message = 'No event ID specified';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Events - Fest Management</title>
    <?php include 'includes/_links.php'; ?>
</head>

<body>
    <?php include 'includes/_navbar.php'; ?>

    <main>
        <div class="container py-5" style="position: relative;">
            <?php if ($error_message): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php else: ?>
                <h1 class="h3 font-weight-normal mb-4"><?php echo $event_name; ?></h1>
                <div class="row">
                    <div class="col">
                        <!-- Add more event details here as needed -->
                    </div>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="edit_event.php?id=<?php echo htmlspecialchars($event_id); ?>" class="btn btn-warning btn-sm">
                            <i class="far fa-edit"></i> Edit
                        </a>
                        <a href="delete_event.php?id=<?php echo htmlspecialchars($event_id); ?>" class="btn btn-danger btn-sm">
                            <i class="far fa-trash-alt"></i> Delete
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php include 'includes/_error_toast.php'; ?>
        </div>
    </main>

    <?php include 'includes/_scripts.php'; ?>
</body>

</html>
