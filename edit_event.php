<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?back=edit_event.php');
    exit;
}

include_once 'includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $event_id = $_POST['event_id'];
    $event_name = $_POST['event_name'];
    $event_type = $_POST['event_type'];
    $event_fee = $_POST['event_fee'];
    $category_id = $_POST['category_id'];

    try {
        $stmt = $pdo->prepare("UPDATE events SET event_name = :event_name, event_type = :event_type, event_fee = :event_fee, category_id = :category_id WHERE event_id = :event_id");
        $stmt->execute([
            'event_name' => $event_name,
            'event_type' => $event_type,
            'event_fee' => $event_fee,
            'category_id' => $category_id,
            'event_id' => $event_id
        ]);

        if ($stmt->rowCount() > 0) {
            header('Location: events.php');
        } else {
            $error_message = 'Failed to update event.';
        }
    } catch (PDOException $e) {
        $error_message = 'Database error: ' . $e->getMessage();
    }
}

if (isset($_GET['id'])) {
    $event_id = $_GET['id'];

    try {
        $stmt = $pdo->prepare("SELECT * FROM events WHERE event_id = :event_id");
        $stmt->execute(['event_id' => $event_id]);
        $event = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($event) {
            $event_date = new DateTime($event['event_date']);
            ?>
            <!DOCTYPE html>
            <html lang="en">

            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <meta http-equiv="X-UA-Compatible" content="ie=edge">
                <title>Edit Event - Fest Management</title>
                <?php include 'includes/_links.php'; ?>
            </head>

            <body>
                <?php include 'includes/_navbar.php'; ?>

                <main>
                    <div class="bg-light py-5">
                        <div class="container" style="position: relative;">

                            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" novalidate>
                                <div class="mb-4">
                                    <h2 class="h4">Edit event</h2>
                                </div>

                                <input type="hidden" name="event_id" value="<?php echo htmlspecialchars($event['event_id']); ?>">

                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="event_name" class="form-label">Event name</label>
                                            <input type="text" name="event_name" id="event_name" class="form-control" value="<?php echo htmlspecialchars($event['event_name']); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="event_type" class="form-label">Event type</label>
                                            <select name="event_type" id="event_type" class="form-control custom-select">
                                                <option value="Individual" <?php echo $event['event_type'] == 'Individual' ? 'selected' : ''; ?>>Individual</option>
                                                <option value="Group" <?php echo $event['event_type'] == 'Group' ? 'selected' : ''; ?>>Group</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="event_month" class="form-label">Event date</label>
                                            <select name="event_month" id="event_month" class="form-control custom-select">
                                                <option value="" disabled>Select month</option>
                                                <?php
                                                for ($i = 1; $i <= 12; $i++) {
                                                    $month = str_pad($i, 2, '0', STR_PAD_LEFT);
                                                    $month_name = DateTime::createFromFormat('!m', $i)->format('F');
                                                    echo "<option value=\"$month\" " . ($event_date->format('m') == $month ? 'selected' : '') . ">$month_name</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-2 col-6">
                                        <div class="form-group">
                                            <label for="event_date" class="form-label">&nbsp;</label>
                                            <select name="event_date" id="event_date" class="form-control custom-select">
                                                <option value="" disabled>Select date</option>
                                                <?php
                                                for ($i = 1; $i <= 31; $i++) {
                                                    $day = str_pad($i, 2, '0', STR_PAD_LEFT);
                                                    echo "<option value=\"$day\" " . ($event_date->format('d') == $day ? 'selected' : '') . ">$i</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-2 col-6">
                                        <div class="form-group">
                                            <label for="event_year" class="form-label">&nbsp;</label>
                                            <select name="event_year" id="event_year" class="form-control custom-select">
                                                <option value="" disabled>Select year</option>
                                                <?php
                                                $current_year = date('Y');
                                                for ($i = $current_year; $i <= $current_year + 1; $i++) {
                                                    echo "<option value=\"$i\" " . ($event_date->format('Y') == $i ? 'selected' : '') . ">$i</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label for="event_fee" class="form-label">Event fee</label>
                                            <?php
                                            $stmt = $pdo->prepare("SELECT COUNT(*) FROM registrations WHERE event_id = :event_id");
                                            $stmt->execute(['event_id' => $event_id]);
                                            $participants_count = $stmt->fetchColumn();
                                            ?>
                                            <input type="number" name="event_fee" id="event_fee" class="form-control" min="1" value="<?php echo htmlspecialchars($event['event_fee']); ?>" <?php echo $participants_count > 0 ? 'disabled' : ''; ?>>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="category_id" class="form-label">Category</label>
                                            <select name="category_id" id="category_id" class="form-control custom-select">
                                                <option value="" disabled>Select category</option>
                                                <?php
                                                $stmt = $pdo->query("SELECT * FROM categories ORDER BY category_name");
                                                while ($category = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                                    echo "<option value=\"" . htmlspecialchars($category['category_id']) . "\" " . ($category['category_id'] == $event['category_id'] ? 'selected' : '') . ">" . htmlspecialchars($category['category_name']) . "</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="organiser_id" class="form-label">Organiser</label>
                                            <select name="organiser_id" id="organiser_id" class="form-control custom-select">
                                                <option value="" disabled>Select organiser</option>
                                                <?php
                                                $stmt = $pdo->query("SELECT * FROM organisers ORDER BY organiser_name");
                                                while ($organiser = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                                    echo "<option value=\"" . htmlspecialchars($organiser['organiser_id']) . "\" " . ($organiser['organiser_id'] == $event['organiser_id'] ? 'selected' : '') . ">" . htmlspecialchars($organiser['organiser_name']) . "</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label for="event_desc" class="form-label">Description</label>
                                            <textarea name="event_desc" id="event_desc" rows="5" class="form-control"><?php echo htmlspecialchars($event['event_desc']); ?></textarea>
                                        </div>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary btn-sm-wide transition-3d-hover mr-1">Update</button>
                                <a href="events.php" class="btn btn-secondary btn-sm-wide transition-3d-hover">Cancel</a>

                            </form>

                            <?php if (!empty($error_message)) : ?>
                                <div class="alert alert-danger mt-4" role="alert">
                                    <?php echo htmlspecialchars($error_message); ?>
                                </div>
                            <?php endif; ?>

                        </div>
                    </div>
                </main>

                <?php include 'includes/_scripts.php'; ?>
            </body>

            </html>
            <?php
        } else {
            header('Location: events.php?err=' . urlencode('No such event found.'));
            exit;
        }
    } catch (PDOException $e) {
        header('Location: events.php?err=' . urlencode('Database error: ' . $e->getMessage()));
        exit;
    }
} else {
    header('Location: events.php');
    exit;
}


