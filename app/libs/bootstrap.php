<?php
// Bootstrap of the application to customize it
// To load, change the require from bootstrap to app in public / index.php
// $_SESSION['username'] = "asd";

if (isset($_SESSION['username'])) {
    require_once CORE_PATH . 'kumbia/bootstrap.php';
    if ($_SERVER['REQUEST_URI'] == '/login') {
        header('Location: http://' . $_SERVER['HTTP_HOST']);
    }
} else {
    if ($_SERVER['REQUEST_URI'] !== '/login') {
        header('Location: http://' . $_SERVER['HTTP_HOST'] . '/login');
    } else {
        require_once CORE_PATH . 'kumbia/bootstrap.php';
    }
}

// KumbiaPHP starts
