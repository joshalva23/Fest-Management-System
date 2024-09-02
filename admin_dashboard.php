<?php
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['admin_id']) || $_SESSION['is_admin'] !== true) {
    header('Location: index.php');
    exit;
}

include 'includes/db_connect.php'; // Ensure this file sets up PDO for PostgreSQL

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['approve'])) {
    $user_ids = $_POST['user_ids'] ?? [];
    if (!empty($user_ids)) {
        $user_ids = array_map('intval', $user_ids); // Sanitize user IDs
        $placeholders = implode(',', array_fill(0, count($user_ids), '?'));

        // Prepare the SQL statement to update user status
        $sql = "UPDATE users SET status = 'approved' WHERE user_id IN ($placeholders)";
        $stmt = $pdo->prepare($sql);
        try {
            $stmt->execute($user_ids);
            $success_message = 'Selected users have been approved.';
        } catch (PDOException $e) {
            $error_message = 'Failed to approve users: ' . htmlspecialchars($e->getMessage());
        }
    }
}

// Fetch users who need approval
$query = "SELECT user_id, full_name, email, phone FROM users WHERE status = 'pending'";
try {
    $stmt = $pdo->query($query);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = 'Failed to fetch users: ' . htmlspecialchars($e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Admin - Approve Users</title>
    <?php include 'includes/_links.php'; ?>
</head>

<body>
    <?php include 'includes/_navbar.php'; ?>

    <main>
        <div class="container py-5">
            <h2 class="h3 text-primary">Approve Users</h2>

            <?php if (isset($success_message)) { ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
            <?php } ?>

            <?php if (isset($error_message)) { ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
            <?php } ?>

            <?php if (!empty($users)) { ?>
                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Select</th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $row) { ?>
                                <tr>
                                    <td><input type="checkbox" name="user_ids[]" value="<?php echo htmlspecialchars($row['user_id']); ?>"></td>
                                    <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td><?php echo htmlspecialchars($row['phone']); ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                    <button type="submit" name="approve" class="btn btn-primary">Approve Selected</button>
                </form>
            <?php } else { ?>
                <p class="text-muted">No users pending approval.</p>
            <?php } ?>

        </div>
    </main>

    <?php include 'includes/_scripts.php'; ?>
</body>

</html>
