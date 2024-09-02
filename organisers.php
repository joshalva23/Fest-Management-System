<?php
session_start();

include_once 'includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?back=organisers.php');
    exit;
}

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['type']) && $_POST['type'] === 'remove' && isset($_POST['organiser_id'])) {
        $organiser_id = $_POST['organiser_id'];

        try {
            $stmt = $pdo->prepare("DELETE FROM organisers WHERE organiser_id = :organiser_id");
            $stmt->bindParam(':organiser_id', $organiser_id, PDO::PARAM_INT);
            $result = $stmt->execute();

            if (!$result) {
                $error_message = 'Organiser cannot be deleted.';
            }
        } catch (PDOException $e) {
            $error_message = 'Error: ' . htmlspecialchars($e->getMessage());
        }
    }

    if (isset($_POST['type']) && $_POST['type'] === 'add') {
        $organiser_name = $_POST['organiser_name'];
        $organiser_phone = $_POST['organiser_phone'];
        $organiser_email = strtolower(trim($_POST['organiser_email']));
        $organiser_password = trim($_POST['organiser_password']);

        // Validate the inputs
        if (empty($organiser_name) || empty($organiser_phone) || empty($organiser_email) || empty($organiser_password)) {
            $error_message = 'All fields are required.';
        } else {
            // Hash the password
            $hashed_password = password_hash($organiser_password, PASSWORD_BCRYPT);

            try {
                $stmt = $pdo->prepare("INSERT INTO organisers (organiser_name, organiser_phone, organiser_email, organiser_password) VALUES (:organiser_name, :organiser_phone, :organiser_email, :organiser_password)");
                $stmt->bindParam(':organiser_name', $organiser_name);
                $stmt->bindParam(':organiser_phone', $organiser_phone);
                $stmt->bindParam(':organiser_email', $organiser_email);
                $stmt->bindParam(':organiser_password', $hashed_password);
                $result = $stmt->execute();

                if ($result) {
                    header('Location: organisers.php');
                    exit;
                } else {
                    $error_message = 'Failed to add organiser.';
                }
            } catch (PDOException $e) {
                $error_message = 'Error: ' . htmlspecialchars($e->getMessage());
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Organisers - Fest Management</title>
    <?php include 'includes/_links.php'; ?>

    <style>
    .organizer-action {
        cursor: pointer;
        color: #007bff;
        text-decoration: underline;
    }
    .organizer-action:hover {
        color: #0056b3;
    }
    </style>

</head>

<body>
    <?php include 'includes/_navbar.php'; ?>

    <main>
        <?php include 'includes/_dash_head.php'; ?>

        <div class="container py-5" style="position: relative;">
            <h1 class="h3 d-flex align-items-center justify-content-between font-weight-normal mb-4">
                <span>Organisers</span>
                <button type="button" class="btn btn-primary btn-sm transition-3d-hover" data-toggle="modal" data-target="#newOrganiserModal">
                    <i class="fas fa-plus"></i> Add
                </button>
            </h1>
            <div class="modal fade" id="newOrganiserModal" tabindex="-1" role="dialog">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <form id="newOrganiserForm" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" novalidate>
                            <div class="modal-header">
                                <h5 class="modal-title">New organiser</h5>
                                <button type="button" class="close" data-dismiss="modal">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="type" value="add">
                                <div class="form-group">
                                    <label for="organiser_name" class="form-label">Name</label>
                                    <input type="text" name="organiser_name" id="organiser_name" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label for="organiser_phone" class="form-label">Phone</label>
                                    <input type="text" name="organiser_phone" id="organiser_phone" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label for="organiser_email" class="form-label">Email</label>
                                    <input type="email" name="organiser_email" id="organiser_email" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label for="organiser_password" class="form-label">Password</label>
                                    <input type="password" name="organiser_password" id="organiser_password" class="form-control" required>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-primary btn-sm-wide transition-3d-hover">Add</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Contact</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    try {
                        $stmt = $pdo->query('SELECT * FROM organisers');
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['organiser_name']); ?></td>
                                <td>
                                    <a href="tel:<?php echo htmlspecialchars($row['organiser_phone']); ?>"><i class="fas fa-phone-alt"></i> Call</a>
                                    &nbsp;
                                    <a href="mailto:<?php echo htmlspecialchars($row['organiser_email']); ?>"><i class="fa fa-envelope"></i> Mail</a>
                                </td>
                                <td>
                                    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" onsubmit="return confirm('Are you sure you want to delete this organiser?');">
                                        <input type="hidden" name="organiser_id" value="<?php echo htmlspecialchars($row['organiser_id']); ?>">
                                        <input type="hidden" name="type" value="remove">
                                        <button type="submit" class="bg-transparent border-0 text-danger p-0">
                                            <i class="far fa-trash-alt"></i> Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php
                        }
                    } catch (PDOException $e) {
                        $error_message = 'Error: ' . htmlspecialchars($e->getMessage());
                    }
                    ?>
                </tbody>
            </table>

            <?php if ($error_message) : ?>
                <?php include 'includes/_error_toast.php'; ?>
            <?php endif; ?>
        </div>
    </main>

    <?php include 'includes/_scripts.php'; ?>
    <script>
        $(document).ready(function() {
            $('#newOrganiserForm').validate({
                rules: {
                    organiser_name: {
                        required: true,
                    },
                    organiser_phone: {
                        required: true,
                        phoneIN: true
                    },
                    organiser_email: {
                        required: true,
                        email: true
                    },
                    organiser_password: {
                        required: true,
                        minlength: 6
                    }
                }
            });
        });
    </script>
</body>

</html>
