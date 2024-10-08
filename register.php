<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include 'includes/db_connect.php'; // This file now contains PDO connection setup

    $full_name = $_POST['first_name'] . ' ' . $_POST['last_name'];
    $email = strtolower(trim($_POST['email']));
    $password = trim($_POST['password']);
    $phone = $_POST['phone'];

    // Validate email domain
    if (strlen($email) < 11 || substr($email, -12) !== '@rvce.edu.in') {
        $error_message = 'Email must end with @rvce.edu.in';
    } else {
        try {
            // Check if user already exists
            $stmt = $pdo->prepare('SELECT email FROM users WHERE email = :email');
            $stmt->execute(['email' => $email]);

            if ($stmt->rowCount() > 0) {
                $error_message = 'User already exists with the given email';
            } else {
                // Insert new user into the database
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare('INSERT INTO users (email, pass, full_name, phone) VALUES (:email, :pass, :full_name, :phone)');
                $result = $stmt->execute([
                    'email' => $email,
                    'pass' => $hashed_password,
                    'full_name' => $full_name,
                    'phone' => $phone
                ]);

                if ($result) {
                    header('Location: login.php');
                    exit;
                } else {
                    $error_message = 'Registration failed.';
                }
            }
        } catch (PDOException $e) {
            $error_message = 'Database error: ' . $e->getMessage();
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
    <title>Register - Fest Management</title>
    <?php include 'includes/_links.php'; ?>
</head>

<body>
    <?php include 'includes/_navbar.php'; ?>

    <main>
        <div class="container py-5" style="position: relative;">
            <form id="registerForm" class="w-lg-50 w-md-75 mx-md-auto" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" novalidate>
                <div class="mb-5">
                    <h2 class="h3 text-primary font-weight-normal">
                        Welcome to <span class="font-weight-semi-bold">Fest Management</span>
                    </h2>
                    <p class="text-muted">Fill out the form to get started.</p>
                </div>

                <div class="form-row">
                    <div class="form-group col-6">
                        <label for="first_name" class="form-label">First name</label>
                        <input type="text" name="first_name" id="first_name" class="form-control" placeholder="First name" autofocus>
                    </div>
                    <div class="form-group col-6">
                        <label for="last_name" class="form-label">Last name</label>
                        <input type="text" name="last_name" id="last_name" class="form-control" placeholder="Last name">
                    </div>
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">Email address</label>
                    <input type="email" name="email" id="email" class="form-control" placeholder="Email address">
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" name="password" id="password" class="form-control" placeholder="********">
                </div>

                <div class="form-group">
                    <label for="phone" class="form-label">Phone number</label>
                    <input type="text" name="phone" id="phone" class="form-control" placeholder="Phone number">
                </div>

                <div class="mb-4">
                    <div class="custom-control custom-checkbox d-flex align-items-center text-muted">
                        <input type="checkbox" class="custom-control-input" id="terms_checkbox" name="terms_checkbox">
                        <label class="custom-control-label" for="terms_checkbox">
                            <small>I confirm that the information given in this form is true, complete and accurate.</small>
                        </label>
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-6">
                        <span class="small text-muted">Already have an account?</span>
                        <a class="small" href="login.php">Log in</a>
                    </div>

                    <div class="col-6 text-right">
                        <button type="submit" class="btn btn-primary py-2">Get started</button>
                    </div>
                </div>
            </form>
        </div>
    </main>
    <?php include 'includes/_scripts.php'; ?>
    <?php include 'includes/_error_toast.php'; ?>
    <script>
        $(document).ready(function() {
            $.validator.addMethod('phoneIN', function(value, element) {
                return this.optional(element) || /^\d{10}$/.test(value);
            }, 'Please enter a valid phone number.');

            $.validator.addMethod('lettersonly', function(value, element) {
                return this.optional(element) || /^[a-zA-Z]+$/.test(value);
            }, 'Letters only please.');

            $.validator.addMethod('nowhitespace', function(value, element) {
                return this.optional(element) || /^\S+$/.test(value);
            }, 'No white space please.');

            $('#registerForm').validate({
                rules: {
                    first_name: {
                        required: true,
                        nowhitespace: true,
                        lettersonly: true
                    },
                    last_name: {
                        required: true,
                        nowhitespace: true,
                        lettersonly: true
                    },
                    email: {
                        required: true,
                        email: true
                    },
                    password: {
                        required: true,
                        minlength: 6 // Minimum length for password
                    },
                    phone: {
                        required: true,
                        phoneIN: true
                    },
                    terms_checkbox: {
                        required: true
                    }
                },
                messages: {
                    email: {
                        email: 'Please specify a valid email address.',
                        required: 'Email address is required.'
                    },
                    password: {
                        minlength: 'Password must be at least 6 characters long.',
                        required: 'Password is required.'
                    },
                    terms_checkbox: {
                        required: 'You must agree to the terms.'
                    }
                }
            });
        });
    </script>
</body>

</html>


