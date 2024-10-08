<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?back=select_events.php');
    exit;
}

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    $err = urlencode('Select at least one event');
    header('Location: select_events.php?err=' . $err);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_SESSION['cart'])) {
    include 'includes/db_connect.php'; // Ensure this file sets up PDO for PostgreSQL
    include 'includes/_email_helper.php'; // Include the file with the function

    $participant_name = trim($_POST['participant_name']);
    $participant_email = trim($_POST['participant_email']);
    $participant_phone = trim($_POST['participant_phone']);
    $registered_by = $_POST['user_id'];

    try {
        $pdo->beginTransaction();

        // Insert participant
        $sql = "INSERT INTO participants (participant_name, participant_email, participant_phone, registered_by) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$participant_name, $participant_email, $participant_phone, $registered_by]);
        $participant_id = $pdo->lastInsertId();

        // Prepare message body
        $message_body = "Dear " . htmlspecialchars($participant_name) . ",\r\n\r\nThank you for registering for the fest. Your registration has been confirmed for";

        // Insert registrations
        foreach ($_SESSION['cart'] as $event_id => $event) {
            $sql = "INSERT INTO registrations (participant_id, event_id) VALUES (?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$participant_id, $event_id]);
            // Add event details to the message body
            $message_body .= ' ' . htmlspecialchars($event['event_name']) . ',';
        }

        // Remove trailing comma and add closing line
        $message_body = rtrim($message_body, ',') . ".\r\n\r\nBest regards,\r\nThe Team\r\nDo not respond to this!";

        $pdo->commit();
        unset($_SESSION['cart']);

        // Send confirmation email via Flask
        try {
            sendEmailConfirmationAsync(
                $participant_email,
                'Registration Confirmation',
                $message_body
            );
            echo 'Email sent successfully.';
        } catch (Exception $e) {
            echo 'Error sending email: ' . $e->getMessage();
        }

        header('Location: registrations.php');
        exit;
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error_message = 'Failed to register: ' . htmlspecialchars($e->getMessage());
    }
}

$total_amount = 0;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Checkout - Fest Management</title>
    <?php include 'includes/_links.php'; ?>
</head>

<body>
    <?php include 'includes/_navbar.php'; ?>

    <main>
        <div class="container py-5" style="position: relative;">
            <div class="row">
                <div class="col-md-4 order-md-2 mb-4">
                    <div class="shadow rounded p-4">
                        <div class="border-bottom pb-4 mb-3">
                            <h2 class="h5 mb-0">Selected events</h2>
                        </div>
                        <?php foreach ($_SESSION['cart'] as $event_id => $event) {
                            $total_amount += $event['event_fee']; ?>
                            <div class="border-bottom pb-3 mb-3">
                                <div class="d-flex align-items-start justify-content-between">
                                    <span>
                                        <h6 class="mb-0"><?php echo htmlspecialchars($event['event_name']); ?></h6>
                                        <small class="text-muted"><?php echo htmlspecialchars($event['event_type']); ?></small>
                                    </span>
                                    <span class="text-muted">&#8377;<?php echo htmlspecialchars($event['event_fee']); ?></span>
                                </div>
                            </div>
                        <?php } ?>
                        <div class="media align-items-center">
                            <span class="text-secondary">Total</span>
                            <div class="media-body text-right">
                                <span class="font-weight-semi-bold">&#8377;<?php echo htmlspecialchars($total_amount); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-8 order-md-1">
                    <form id="checkoutForm" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" novalidate>
                        <div class="mb-4">
                            <h2 class="h4">Participant details</h2>
                        </div>
                        <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($_SESSION['user_id']); ?>">
                        <div class="form-group">
                            <label for="participant_name" class="form-label">Name</label>
                            <input type="text" name="participant_name" id="participant_name" class="form-control" placeholder="Siddharth S" required>
                        </div>
                        <div class="form-group">
                            <label for="participant_email" class="form-label">Email</label>
                            <input type="email" name="participant_email" id="participant_email" class="form-control" placeholder="siddharth@gmail.com" required>
                        </div>
                        <div class="form-group">
                            <label for="participant_phone" class="form-label">Phone</label>
                            <input type="text" name="participant_phone" id="participant_phone" class="form-control" placeholder="9845739474" required>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="select_events.php"><small class="fas fa-arrow-left mr-2"></small>Return to select events</a>
                            <button type="submit" class="btn btn-primary btn-pill transition-3d-hover">Continue</button>
                        </div>
                    </form>
                </div>
            </div>

            <?php if (isset($error_message)) { ?>
                <div class="toast" style="position: absolute; top: 1.5rem; right: 0;" data-delay="2500">
                    <div class="toast-header">
                        <span class="mr-auto font-weight-semi-bold text-danger">Error</span>
                        <button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
                            <i class="fas fa-times fa-xs"></i>
                        </button>
                    </div>
                    <div class="toast-body">
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                </div>
            <?php } ?>
        </div>
    </main>

    <?php include 'includes/_scripts.php'; ?>
    <script>
        $(document).ready(function() {
            $('#checkoutForm').validate({
                rules: {
                    participant_name: {
                        required: true
                    },
                    participant_email: {
                        required: true,
                        email: true
                    },
                    participant_phone: {
                        required: true,
                        phoneIN: true
                    }
                }
            });
        });
    </script>
</body>

</html>


