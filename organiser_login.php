<?php
session_start();

if (isset($_SESSION['organiser_id'])) {
    header('Location: organiser_dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include 'includes/db_connect.php'; // This will include the PDO connection setup

    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Prepare the query
    $query = 'SELECT organiser_id, organiser_password, organiser_name FROM organisers WHERE organiser_email = :email';
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Verify password
        if (password_verify($password, $row['organiser_password'])) {
            $_SESSION['organiser_id'] = $row['organiser_id'];
            $_SESSION['organiser_name'] = $row['organiser_name'];

            if (isset($_GET['back'])) {
                header('Location: ' . htmlspecialchars($_GET['back']));
            } else {
                header('Location: organiser_dashboard.php');
            }
            exit;
        } else {
            $error_message = 'Invalid email/password';
        }
    } else {
        $error_message = 'Invalid email/password';
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Organiser Login - Fest Management</title>
    <?php include 'includes/_links.php'; ?>
</head>

<body>
    <?php include 'includes/_navbar.php'; ?>

    <main class="bg-light">
        <div class="container py-5">
            <form class="w-lg-50 w-md-75 mx-md-auto" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?><?php echo isset($_GET['back']) ? '?back=' . htmlspecialchars($_GET['back']) : ''; ?>" method="post">
                <div class="mb-5">
                    <h2 class="h3 text-primary font-weight-normal">
                        Welcome, <span class="font-weight-semi-bold">Organiser</span>
                    </h2>
                    <p class="text-muted">Login to continue.</p>
                </div>

                <?php
                if (isset($error_message)) {
                    echo '<div class="alert alert-danger" role="alert">' . htmlspecialchars($error_message) . '</div>';
                }
                ?>

                <div class="form-group">
                    <label for="email" class="form-label">Email address</label>
                    <input type="email" name="email" id="email" class="form-control" placeholder="Email address" required autofocus>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" name="password" id="password" class="form-control" placeholder="********" required>
                </div>

                <div class="row align-items-center">
                    <div class="col-6">
                        <span class="small text-muted">Don't have an account?</span>
                        <a class="small" href="#">Please contact a register</a>
                    </div>

                    <div class="col-6 text-right">
                        <button type="submit" class="btn btn-primary py-2">Log in</button>
                    </div>
                </div>
            </form>
        </div>
    </main>

    <?php include 'includes/_scripts.php'; ?>
</body>

</html>


