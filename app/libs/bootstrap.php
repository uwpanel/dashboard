<?php
// Bootstrap of the application to customize it
// To load, change the require from bootstrap to app in public / index.php
session_start();

if (isset($_SESSION['user'])) {
    require_once CORE_PATH . 'kumbia/bootstrap.php';
    if ($_SERVER['REQUEST_URI'] == '/login') {
        header('Location: https://' . $_SERVER['HTTP_HOST']);
        require_once CORE_PATH . 'kumbia/bootstrap.php';
    }
} else {
    if (strpos($_SERVER['REQUEST_URI'], '/reset') === 0 || strpos($_SERVER['REQUEST_URI'], '/login') === 0) {
        require_once CORE_PATH . 'kumbia/bootstrap.php';
    } else if (strpos($_SERVER['REQUEST_URI'], '/reset') === 0) {
        header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    } else {
        header('Location: https://' . $_SERVER['HTTP_HOST'] . '/login');
    }
}

// KumbiaPHP starts
