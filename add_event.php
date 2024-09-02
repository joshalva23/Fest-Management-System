<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?back=add_event.php');
    exit;
}

include 'includes/db_connect.php'; // This will include the PDO connection setup

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $event_name = $_POST['event_name'];
    $event_type = $_POST['event_type'];
    $event_fee = $_POST['event_fee'];
    $category_id = $_POST['category_id'];
    $organiser_id = $_POST['organiser_id'];
    $event_desc = $_POST['event_desc'];
    $event_date = $_POST['event_year'] . '-' . $_POST['event_month'] . '-' . $_POST['event_date'];

    // Prepare the SQL statement to prevent SQL injection
    $query = 'INSERT INTO events (event_name, event_type, event_fee, category_id, event_desc, event_date, organiser_id) VALUES (:event_name, :event_type, :event_fee, :category_id, :event_desc, :event_date, :organiser_id)';

    try {
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':event_name', $event_name);
        $stmt->bindParam(':event_type', $event_type);
        $stmt->bindParam(':event_fee', $event_fee, PDO::PARAM_INT);
        $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
        $stmt->bindParam(':organiser_id', $organiser_id, PDO::PARAM_INT);
        $stmt->bindParam(':event_desc', $event_desc);
        $stmt->bindParam(':event_date', $event_date);

        $stmt->execute();

        header('Location: events.php');
        exit;
    } catch (PDOException $e) {
        $error_message = 'Registration failed: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Add Event - Fest Management</title>
    <?php include 'includes/_links.php'; ?>
</head>

<body>
    <?php include 'includes/_navbar.php'; ?>

    <main>
        <?php include 'includes/_dash_head.php'; ?>

        <div class="bg-light py-5">
            <div class="container" style="position: relative;">
                <form id="newEventForm" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" novalidate>
                    <div class="mb-4">
                        <h2 class="h4">Create an event</h2>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="event_name" class="form-label">Event name</label>
                                <input type="text" name="event_name" id="event_name" class="form-control" placeholder="Event X" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="event_type" class="form-label">Event type</label>
                                <select name="event_type" id="event_type" class="form-control custom-select" required>
                                    <option value="Individual">Individual</option>
                                    <option value="Group">Group</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="event_month" class="form-label">Event date</label>
                                <select name="event_month" id="event_month" class="form-control custom-select" required>
                                    <option value="" disabled>Select month</option>
                                    <?php for ($month = 1; $month <= 12; $month++) : ?>
                                        <option value="<?php echo str_pad($month, 2, '0', STR_PAD_LEFT); ?>">
                                            <?php echo date('F', mktime(0, 0, 0, $month, 10)); ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2 col-6">
                            <div class="form-group">
                                <label for="event_date" class="form-label">&nbsp;</label>
                                <select name="event_date" id="event_date" class="form-control custom-select" required>
                                    <option value="" disabled>Select date</option>
                                    <?php for ($day = 1; $day <= 31; $day++) : ?>
                                        <option value="<?php echo str_pad($day, 2, '0', STR_PAD_LEFT); ?>"><?php echo $day; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="event_year" class="form-label">&nbsp;</label>
                                <select name="event_year" id="event_year" class="form-control custom-select" required>
                                    <option value="" disabled>Select year</option>
                                    <?php for ($year = date('Y'); $year <= date('Y') + 5; $year++) : ?>
                                        <option value="<?php echo $year; ?>" <?php echo $year == date('Y') ? 'selected' : ''; ?>>
                                            <?php echo $year; ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="event_fee" class="form-label">Event fee</label>
                                <input type="number" name="event_fee" id="event_fee" class="form-control" min="1" placeholder="100" required>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="category_id" class="form-label">Category</label>
                                <select name="category_id" id="category_id" class="form-control custom-select" required>
                                    <option value="" disabled selected>Select category</option>
                                    <?php
                                    $query = 'SELECT * FROM categories ORDER BY category_name';
                                    $stmt = $pdo->query($query);
                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                                        <option value="<?php echo $row['category_id']; ?>">
                                            <?php echo htmlspecialchars($row['category_name']); ?>
                                        </option>
                                    <?php }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="organiser_id" class="form-label">Organiser</label>
                                <select name="organiser_id" id="organiser_id" class="form-control custom-select" required>
                                    <option value="" disabled selected>Select organiser</option>
                                    <?php
                                    $query = 'SELECT * FROM organisers ORDER BY organiser_name';
                                    $stmt = $pdo->query($query);
                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                                        <option value="<?php echo $row['organiser_id']; ?>">
                                            <?php echo htmlspecialchars($row['organiser_name']); ?>
                                        </option>
                                    <?php }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="form-group">
                                <label for="event_desc" class="form-label">Description</label>
                                <textarea name="event_desc" id="event_desc" rows="5" class="form-control" required></textarea>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-sm-wide transition-3d-hover mr-1">Add</button>
                    <a href="events.php" class="btn btn-secondary btn-sm-wide transition-3d-hover">Cancel</a>
                </form>

                <?php if (isset($error_message)) : ?>
                    <div class="alert alert-danger mt-3">
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php include 'includes/_scripts.php'; ?>
    <script>
        $(document).ready(function() {
            $('#newEventForm').validate({
                rules: {
                    event_name: {
                        required: true
                    },
                    event_type: {
                        required: true
                    },
                    event_date: {
                        required: true,
                        digits: true
                    },
                    event_month: {
                        required: true
                    },
                    event_year: {
                        required: true,
                        digits: true
                    },
                    event_fee: {
                        required: true,
                        number: true,
                        min: 1
                    },
                    category_id: {
                        required: true
                    },
                    organiser_id: {
                        required: true
                    },
                    event_desc: {
                        required: true
                    }
                }
            });
        });
    </script>
</body>

</html>
