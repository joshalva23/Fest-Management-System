<?php
session_start();

// Check if the user is already logged in as admin
if (isset($_SESSION['admin_id'])) {
    header('Location: admin_dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include 'includes/db_connect.php'; // Ensure this file sets up PDO for PostgreSQL

    $email = strtolower(trim($_POST['email']));
    $password = trim($_POST['password']);

    // Prepare and execute the query to get admin details and email
    $sql = "
        SELECT a.admin_id, u.pass, u.email
        FROM admin a
        JOIN users u ON a.user_id = u.user_id
        WHERE u.email = ?
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email]);

    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Verify password
        if (password_verify($password, $row['pass'])) {
            $_SESSION['admin_id'] = $row['admin_id'];
            $_SESSION['is_admin'] = true;
            $_SESSION['user_id'] = $row['email'];
            header('Location: admin_dashboard.php');
            exit;
        } else {
            $error_message = 'Invalid email or password.';
        }
    } else {
        $error_message = 'Invalid email or password.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Admin Login - Fest Management</title>
    <?php include 'includes/_links.php'; ?>
</head>

<body>
    <?php include 'includes/_navbar.php'; ?>

    <main>
        <div class="container py-5" style="position: relative;">
            <form id="adminLoginForm" class="w-lg-50 w-md-75 mx-md-auto" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" novalidate>
                <div class="mb-5">
                    <h2 class="h3 text-primary font-weight-normal">
                        Admin Login
                    </h2>
                    <p class="text-muted">Please enter your credentials to log in.</p>
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

                <div class="form-group">
                    <label for="email" class="form-label">Email address</label>
                    <input type="email" name="email" id="email" class="form-control" placeholder="Email address" required>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" name="password" id="password" class="form-control" placeholder="********" required>
                </div>

                <div class="row align-items-center">
                    <div class="col-12 text-right">
                        <button type="submit" class="btn btn-primary py-2">Log In</button>
                    </div>
                </div>
            </form>
        </div>
    </main>

    <?php include 'includes/_scripts.php'; ?>
    <script>
        $(document).ready(function() {
            $('#adminLoginForm').validate({
                rules: {
                    email: {
                        required: true,
                        email: true
                    },
                    password: {
                        required: true
                    }
                },
                messages: {
                    email: {
                        email: 'Please specify a valid email address.'
                    }
                }
            });
        });
    </script>
</body>

</html>


