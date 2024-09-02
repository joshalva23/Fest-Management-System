<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?back=users.php');
    exit;
}

include_once 'includes/db_connect.php'; // Ensure this file contains the PDO connection setup
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Users - Fest Management</title>
    <?php include 'includes/_links.php'; ?>
</head>

<body>
    <?php include 'includes/_navbar.php'; ?>

    <main>
        <?php include 'includes/_dash_head.php'; ?>

        <div class="container py-5">
            <div class="mb-4">
                <h1 class="h3">Users</h1> <!-- Added title -->
            </div>
            <div class="row">
                <?php
                try {
                    // Fetch users and their details
                    $query = '
                        SELECT users.full_name, users.email, users.contribution, COUNT(participants.participant_id) AS participant_count
                        FROM users
                        LEFT JOIN participants ON users.email = participants.registered_by
                        GROUP BY users.full_name, users.email, users.contribution
                        ORDER BY users.full_name
                    ';
                    $stmt = $pdo->prepare($query);
                    $stmt->execute();

                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                        <div class="col-lg-4">
                            <div class="card text-center shadow">
                                <div class="card-body">
                                    <div class="mb-4">
                                        <span class="btn btn-icon btn-primary rounded-circle mb-2">
                                            <span class="btn-icon__inner">
                                                <?php
                                                // Ensure split_name function is defined elsewhere and works as expected
                                                $names = split_name($row['full_name']);
                                                echo htmlspecialchars($names[0][0] . $names[1][0]);
                                                ?>
                                            </span>
                                        </span>
                                        <h2 class="h6 mb-0"><?php echo htmlspecialchars($row['full_name']); ?></h2>
                                    </div>

                                    <div class="d-flex justify-content-around">
                                        <div class="bg-light rounded p-3">
                                            <span class="d-block small font-weight-semi-bold">Participants</span>
                                            <span class="lead"><?php echo htmlspecialchars($row['participant_count']); ?></span>
                                        </div>
                                        <div class="bg-light rounded p-3">
                                            <span class="d-block small font-weight-semi-bold">Contribution</span>
                                            <span class="lead"><?php echo isset($row['contribution']) ? '&#8377;' . htmlspecialchars($row['contribution']) : '-'; ?></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="card-footer bg-white py-3">
                                    <a class="btn btn-sm btn-outline-primary transition-3d-hover" href="mailto:<?php echo htmlspecialchars($row['email']); ?>">
                                        <span class="far fa-envelope mr-2"></span>
                                        Send a Message
                                    </a>
                                </div>
                            </div>
                        </div>
                <?php }
                } catch (PDOException $e) {
                    // Handle potential errors
                    echo '<p class="text-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
                }
                ?>
            </div>
        </div>
    </main>

    <?php include 'includes/_scripts.php'; ?>
</body>

</html>
