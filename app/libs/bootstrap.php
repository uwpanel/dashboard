<?php
// Bootstrap of the application to customize it
// To load, change the require from bootstrap to app in public / index.php
session_start();

if (isset($_SESSION['user'])) {
    require_once CORE_PATH . 'kumbia/bootstrap.php';
    if ($_SERVER['REQUEST_URI'] == '/login') {
        header('Location: https://' . $_SERVER['HTTP_HOST']);
    }
} else {
    if (strpos($_SERVER['REQUEST_URI'], '/login') !== 0) {
        header('Location: https://' . $_SERVER['HTTP_HOST'] . '/login');
    } else {
        require_once CORE_PATH . 'kumbia/bootstrap.php';
    }
}

// KumbiaPHP starts
