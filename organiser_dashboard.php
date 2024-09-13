<?php
session_start();

if (!isset($_SESSION['organiser_id'])) {
    header('Location: organiser_login.php');
    exit;
}

include 'includes/db_connect.php';

$organiser_id = $_SESSION['organiser_id'];

// Initialize variables
$events = [];
$selected_event_id = null;
$participants = [];

try {
    // Fetch events assigned to the logged-in organiser
    $query = '
        SELECT e.event_id, e.event_name
        FROM events e
        WHERE e.organiser_id = :organiser_id
    ';
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':organiser_id', $organiser_id);
    $stmt->execute();
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Handle event selection
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['event_id'])) {
        $selected_event_id = intval($_POST['event_id']);

        // Fetch participants for the selected event
        $query = '
            SELECT p.participant_id, p.participant_name, p.participant_email, p.participant_phone
            FROM participants p
            JOIN registrations r ON p.participant_id = r.participant_id
            WHERE r.event_id = :event_id
        ';
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':event_id', $selected_event_id);
        $stmt->execute();
        $participants = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    $error_message = 'Database error: ' . htmlspecialchars($e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Organiser Dashboard - Fest Management</title>
    <?php include 'includes/_links.php'; ?>
</head>

<body>
    <?php include 'includes/_navbar.php'; ?>

    <main class="bg-light">
        <div class="container py-5">
            <h2 class="h3 text-primary font-weight-normal mb-4">My Events</h2>

            <?php
            if (isset($error_message)) {
                echo '<div class="alert alert-danger" role="alert">' . htmlspecialchars($error_message) . '</div>';
            }
            ?>

            <!-- Event Selection Form -->
            <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                <div class="form-group">
                    <label for="event_id" class="form-label">Select Event</label>
                    <select name="event_id" id="event_id" class="form-control" required>
                        <option value="" disabled selected>Select an event</option>
                        <?php foreach ($events as $event) : ?>
                            <option value="<?php echo htmlspecialchars($event['event_id']); ?>" <?php echo ($selected_event_id == $event['event_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($event['event_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Show Participants</button>
            </form>

            <?php if ($selected_event_id && !empty($participants)) : ?>
                <div class="card mt-4">
                    <div class="card-body">
                        <h5 class="card-title">Participants for Selected Event</h5>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($participants as $participant) : ?>
                                        <tr>
                                            <td><a href="view_participant.php?id=<?php echo htmlspecialchars($participant['participant_id']); ?>">
                                                <?php echo htmlspecialchars($participant['participant_name']); ?></a></td>
                                            <td><a href="mailto:<?php echo htmlspecialchars($participant['participant_email']); ?>">
                                                <?php echo htmlspecialchars($participant['participant_email']); ?></a></td>
                                            <td><a href="tel:<?php echo htmlspecialchars($participant['participant_phone']); ?>">
                                                <?php echo htmlspecialchars($participant['participant_phone']); ?></a></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php elseif ($selected_event_id && empty($participants)) : ?>
                <p class="text-muted mt-3">No participants registered for this event.</p>
            <?php endif; ?>
        </div>
    </main>

    <?php include 'includes/_scripts.php'; ?>
</body>

</html>


