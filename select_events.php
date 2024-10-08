<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?back=select_events.php');
    exit;
}

include 'includes/db_connect.php'; // This file contains the PDO connection setup

if (isset($_GET['err'])) {
    $error_message = htmlspecialchars($_GET['err']);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Select Events - Fest Management</title>
    <?php include 'includes/_links.php'; ?>
</head>

<body>
    <?php include 'includes/_navbar.php'; ?>
    <div class="container py-5" style="position: relative;">
        <h1 class="h3 d-flex align-items-center justify-content-between font-weight-normal mb-4">
            <span>Select Events</span>
            <a href="checkout.php" class="btn btn-success float-right">
                Proceed <i class="fas fa-arrow-right"></i>
            </a>
        </h1>
        <div class="row">
            <?php
            try {
                $query = 'SELECT * FROM events ORDER BY event_name';
                $stmt = $pdo->query($query);
                $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if ($events) {
                    foreach ($events as $row) { ?>
                        <div class="col-lg-4 col-md-6 col-12 mb-4">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <a href="view_event.php?id=<?php echo htmlspecialchars($row['event_id']); ?>" class="text-decoration-none text-dark">
                                            <?php echo htmlspecialchars($row['event_name']); ?>
                                        </a>
                                    </h5>
                                    <h6 class="card-subtitle mb-2 text-muted">&#8377; <?php echo htmlspecialchars($row['event_fee']); ?></h6>
                                    <p class="card-text text-truncate"><?php echo htmlspecialchars($row['event_desc']); ?></p>
                                    <form action="update_cart.php" method="post">
                                        <input type="hidden" name="event_id" value="<?php echo htmlspecialchars($row['event_id']); ?>">
                                        <?php if (isset($_SESSION['cart'][$row['event_id']])) { ?>
                                            <input type="hidden" name="type" value="remove">
                                            <button type="submit" class="btn btn-danger btn-sm float-right">
                                                <i class="fas fa-times"></i> Remove
                                            </button>
                                        <?php } else { ?>
                                            <input type="hidden" name="type" value="add">
                                            <button type="submit" class="btn btn-primary btn-sm float-right">
                                                <i class="fas fa-check"></i> Select
                                            </button>
                                        <?php } ?>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    echo '<p class="text-muted">No events available.</p>';
                }
            } catch (PDOException $e) {
                $error_message = 'Database error: ' . $e->getMessage();
            }
            ?>
        </div>

        <?php if (isset($error_message)) : ?>
            <div class="alert alert-danger mt-3" role="alert">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <?php include 'includes/_error_toast.php'; ?>
    </div>

    <?php include 'includes/_scripts.php'; ?>
</body>

</html>


