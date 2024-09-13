<?php
session_start();


session_destroy();
unset($_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['cart'], $_SESSION['admin_id'], $_SESSION['is_admin']);
unset($_SESSION['organiser_id'], $_SESSION['organiser_name']);
header('Location: index.php');



