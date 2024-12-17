<?php
function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        if (basename($_SERVER['PHP_SELF']) !== 'login.php') {
            header("Location: login.php");
            exit();
        }
    }
}

function requireAdmin() {
    requireLogin();
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header("Location: unauthorized.php");
        exit();
    }
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}
?> 