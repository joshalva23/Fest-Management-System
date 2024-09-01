<?php
session_start();

if (isset($_SESSION['user_id'])) {
    session_destroy();
    unset($_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['cart'], $_SESSION['admin_id'], $_SESSION['is_admin']);

    header('Location: index.php');
} else {
    header('Location: index.php');
}
