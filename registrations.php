<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?back=registrations.php');
    exit;
}

include 'includes/db_connect.php'; // This file contains the PDO connection setup



try {
    // Fetch registrations for participants registered by the logged-in user
    $query = 'SELECT * FROM registrations
              NATURAL JOIN participants
              NATURAL JOIN events
              WHERE participants.registered_by = :user_email';
    $stmt = $pdo->prepare($query);
    $stmt->execute(['user_email' => $_SESSION['user_id']]);
    $registrations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = 'Database error: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Registrations - Fest Management</title>
    <?php include 'includes/_links.php'; ?>
</head>

<body>
    <?php include 'includes/_navbar.php'; ?>

    <main>
        <?php include 'includes/_dash_head.php'; ?>

        <div class="container py-5" style="position: relative;">
            <h1 class="h3 font-weight-normal mb-4">
                Registrations
            </h1>
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Event</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (isset($registrations) && !empty($registrations)) {
                        foreach ($registrations as $row) { ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['participant_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['event_name']); ?></td>
                                <td>
                                    <a href="view_participant.php?id=<?php echo htmlspecialchars($row['participant_id']); ?>" class="">
                                        <i class="far fa-eye"></i> View
                                    </a>
                                    <a href="tel:<?php echo htmlspecialchars($row['participant_phone']); ?>" class="ml-2">
                                        <i class="fas fa-phone-alt"></i> Call
                                    </a>
                                </td>
                            </tr>
                    <?php }
                    } else {
                        echo '<tr><td colspan="3">No registrations found.</td></tr>';
                    }
                    ?>
                </tbody>
            </table>

            <?php if( isset($error_message)) : ?>
                <div class="alert alert-danger mt-3" role="alert">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <?php include 'includes/_error_toast.php'; ?>
        </div>
    </main>

    <?php include 'includes/_scripts.php'; ?>
</body>

</html>


