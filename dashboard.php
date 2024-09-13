<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

function time_elapsed_string($datetime, $full = false)
{
    date_default_timezone_set('Asia/Kolkata');
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    // Calculate weeks and days separately
    $weeks = intdiv($diff->days, 7);
    $days = $diff->days % 7;

    // Prepare the time difference string
    $string = [
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    ];

    // Replace weeks and days manually
    $result = [];
    foreach ($string as $key => $value) {
        if ($key == 'w') {
            // Add weeks if present
            if ($weeks > 0) {
                $result[] = $weeks . ' week' . ($weeks > 1 ? 's' : '');
            }
        } elseif ($key == 'd') {
            // Add days if present
            if ($days > 0) {
                $result[] = $days . ' day' . ($days > 1 ? 's' : '');
            }
        } else {
            if ($diff->$key) {
                $result[] = $diff->$key . ' ' . $value . ($diff->$key > 1 ? 's' : '');
            }
        }
    }

    // Determine output based on whether to show full or partial string
    if (!$full) {
        $result = array_slice($result, 0, 1);
    }
    
    return $result ? implode(', ', $result) . ' ago' : 'just now';
}


include 'includes/db_connect.php';
include 'includes/_defaults.php';

$user_id = $_SESSION['user_id'];

// Initialize variables
$top_events = [];
$recent_logs = [];
$my_participant_count = 0;
$my_total_contribution = 0;
$percentage_reached = 0;
$participant_count = 0;

try {
    // Update contribution calculation
    if (isset($_GET['update']) && $_GET['update'] == 'contribution') {
        $stmt = $pdo->prepare('CALL calc_contribution()');
        $stmt->execute();
    }

    // Get participant count
    $stmt = $pdo->prepare("SELECT COUNT(*) AS count FROM participants WHERE registered_by = :user_id");
    $stmt->execute(['user_id' => $user_id]);
    $my_participant_count = $stmt->fetchColumn();

    // Get total contribution
    $stmt = $pdo->prepare("SELECT contribution FROM users WHERE email = :user_id");
    $stmt->execute(['user_id' => $user_id]);
    $my_total_contribution = $stmt->fetchColumn();

    // Get total contribution for percentage calculation
    $stmt = $pdo->query('SELECT SUM(contribution) AS contribution FROM users');
    $total_contribution = $stmt->fetchColumn();
    $percentage_reached = ($total_contribution > 0) ? ($total_contribution / $goal) * 100 : 0;

    // Get maximum participant count in any event
    $stmt = $pdo->query('SELECT COUNT(*) AS count FROM registrations GROUP BY event_id ORDER BY COUNT(participant_id) DESC LIMIT 1');
    $participant_count = $stmt->fetchColumn();

    // Get top 4 events by registration count
    $stmt = $pdo->query('SELECT event_name, COUNT(*) AS count FROM registrations NATURAL JOIN events GROUP BY event_id ORDER BY COUNT(participant_id) DESC LIMIT 4');
    $top_events = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get recent activity logs
    $stmt = $pdo->query('SELECT full_name, log_message, log_time FROM logs INNER JOIN users ON log_user = email ORDER BY log_time DESC');
    $recent_logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    <title>Dashboard - Fest Management</title>
    <?php include 'includes/_links.php'; ?>
    <!-- Include Chart.js for graphical representation -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <?php include 'includes/_navbar.php'; ?>

    <main class="bg-light">
        <?php include 'includes/_dash_head.php'; ?>

        <div class="container py-5">
            <div class="card-deck d-block d-lg-flex">
                <div class="card border-0 shadow-sm mb-4 mb-lg-0">
                    <div class="card-body px-4 py-5">
                        <div class="d-flex align-items-center">
                            <span class="rounded-circle text-primary bg-light p-4 mr-4"><i class="fas fa-users fa-2x"></i></span>
                            <span>
                                <span class="d-block h2">
                                    <?= htmlspecialchars($my_participant_count); ?>
                                </span>
                                <h2 class="h6 text-secondary font-weight-normal mb-0">
                                    My participants
                                </h2>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4 mb-lg-0">
                    <div class="card-body px-4 py-5">
                        <div class="d-flex align-items-center">
                            <span class="rounded-circle text-success bg-light p-4 mr-4"><i class="fas fa-coins fa-2x"></i></span>
                            <span>
                                <span class="d-block h2">
                                    <sup><small>&#8377;</small></sup>
                                    <?= htmlspecialchars($my_total_contribution); ?>
                                </span>
                                <h2 class="h6 text-secondary font-weight-normal mb-0">
                                    My contribution
                                </h2>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="container py-5">
            <div class="card-deck d-block d-lg-flex">
                <div class="card border-0 shadow-sm mb-4 mb-lg-0">
                    <div class="card-body p-4">
                        <h4 class="h6 mb-0">Goal Achievement</h4>
                        <hr class="mt-3 mb-4">
                        <div class="d-block d-sm-flex justify-content-between align-items-center mb-4">
                            <canvas id="goalChart" width="400" height="400"></canvas>
                            <script>
                                var ctx = document.getElementById('goalChart').getContext('2d');
                                new Chart(ctx, {
                                    type: 'doughnut',
                                    data: {
                                        labels: ['Achieved', 'Remaining'],
                                        datasets: [{
                                            data: [<?= htmlspecialchars($percentage_reached); ?>, 100 - <?= htmlspecialchars($percentage_reached); ?>],
                                            backgroundColor: ['#00c9a7', '#e0e0e0']
                                        }]
                                    },
                                    options: {
                                        responsive: true,
                                        plugins: {
                                            legend: {
                                                position: 'top',
                                            },
                                            tooltip: {
                                                callbacks: {
                                                    label: function(tooltipItem) {
                                                        var value = tooltipItem.raw;
                                                        return value.toFixed(2) + '%';
                                                    }
                                                }
                                            }
                                        }
                                    }
                                });
                            </script>
                        </div>
                        <a href="https://app.droxy.ai/guest-agent/66c2c2f8a588e54771993a67" class="btn btn-block btn-primary transition-3d-hover">Assistant</a>
                    </div>
                    <div class="card-footer bg-white p-4">
                        <div class="text-center">
                            <label class="small text-muted">Goal:</label>
                            <small class="font-weight-medium"><?= htmlspecialchars($goal); ?></small>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4 mb-lg-0">
                    <div class="card-body p-4">
                        <h4 class="h6 mb-0">Registrations</h4>
                        <hr class="mt-3 mb-4">
                        <div class="row">
                            <?php
                            try {
                                // Prepare and execute the query
                                $stmt = $pdo->prepare('
                                    SELECT event_name, COUNT(registrations.participant_id) AS count
                                    FROM registrations
                                    JOIN events ON registrations.event_id = events.event_id
                                    GROUP BY event_name
                                    ORDER BY COUNT(registrations.participant_id) DESC
                                    LIMIT 4
                                ');
                                $stmt->execute();
                                $top_events = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                // Loop through the results
                                foreach ($top_events as $event) {
                                    $percentage = $participant_count > 0 ? ($event['count'] / $participant_count * 100) : 0;
                            ?>
                                    <div class="col-3">
                                        <div class="js-vr-progress progress-vertical rounded" data-toggle="tooltip" data-placement="right" title="<?php echo htmlspecialchars($event['event_name']); ?> (<?php echo htmlspecialchars($event['count']); ?>)">
                                            <div class="js-vr-progress-bar bg-primary rounded-bottom" role="progressbar" style="height: <?php echo htmlspecialchars($percentage); ?>%" aria-valuenow="<?php echo htmlspecialchars($percentage); ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>
                            <?php
                                }
                            } catch (PDOException $e) {
                                echo 'Database error: ' . htmlspecialchars($e->getMessage());
                            }
                            ?>
                        </div>


                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h4 class="h6 mb-0">Recent Activity</h4>
                        <hr class="mt-3 mb-4">
                        
                        <div class="overflow-hidden">
                            <div class="js-scrollbar pr-3" style="max-height: 500px;">
                                <ul class="list-unstyled u-indicator-vertical-dashed">
                                    <?php
                                    try {
                                        // Prepare and execute the query using PDO
                                        $stmt = $pdo->prepare('
                                            SELECT full_name, log_message, log_time 
                                            FROM logs 
                                            INNER JOIN users ON log_user = email 
                                            ORDER BY log_time DESC
                                        ');
                                        $stmt->execute();
                                        
                                        // Fetch all results
                                        $recent_logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                        
                                        if (!empty($recent_logs)) {
                                            foreach ($recent_logs as $log) { ?>
                                                <li class="media u-indicator-vertical-dashed-item">
                                                    <span class="btn btn-xs btn-icon btn-primary rounded-circle mr-3">
                                                        <span class="btn-icon__inner"><?php echo htmlspecialchars($log['full_name'][0]); ?></span>
                                                    </span>
                                                    <div class="media-body">
                                                        <h5 class="my-1" style="font-size: 0.875rem;">
                                                            <?php echo htmlspecialchars($log['full_name']); ?>
                                                        </h5>
                                                        <p class="small mb-1"><?php echo htmlspecialchars($log['log_message']); ?></p>
                                                        <small class="d-block text-muted"><?php echo htmlspecialchars(time_elapsed_string($log['log_time'])); ?></small>
                                                    </div>
                                                </li>
                                    <?php
                                            }
                                        } else { ?>
                                            <li class="media u-indicator-vertical-dashed-item">
                                                <div class="media-body">
                                                    <p class="small mb-1">No recent activity.</p>
                                                </div>
                                            </li>
                                    <?php
                                        }
                                    } catch (PDOException $e) {
                                        // Handle potential errors
                                        echo '<li class="media u-indicator-vertical-dashed-item"><div class="media-body"><p class="small mb-1">Error fetching logs.</p></div></li>';
                                        error_log('PDO Error: ' . $e->getMessage()); // Log error message
                                    }
                                    ?>
                                </ul>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include 'includes/_scripts.php'; ?>
</body>

</html>
