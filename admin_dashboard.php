<?php
session_start();

//var_dump($_SESSION['user_id'],$_SESSION['admin_id'], $_SESSION['is_admin'] );
if (!isset($_SESSION['user_id']) || !isset($_SESSION['admin_id']) || $_SESSION['is_admin'] !== true) {
    header('Location: index.php');
    exit;
}

include 'includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['approve'])) {
    $user_ids = $_POST['user_ids'] ?? [];
    if (!empty($user_ids)) {

        $stmt = $db->prepare("UPDATE users SET status = 'approved' WHERE user_id = ?");
        foreach ($user_ids as $user_id) {
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
        }
        $stmt->close();
    }
}

// Fetch users who need approval
$query = "SELECT user_id, full_name, email, phone FROM users WHERE status = 'pending'";
$result = $db->query($query);

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

            <?php if ($result->num_rows > 0) { ?>
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
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
                            <?php while ($row = $result->fetch_assoc()) { ?>
                                <tr>
                                    <td><input type="checkbox" name="user_ids[]" value="<?php echo $row['user_id']; ?>"></td>
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
