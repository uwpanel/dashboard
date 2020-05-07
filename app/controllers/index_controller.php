<?php

/**
 * Default controller if routes are not used
 *
 */
class IndexController extends AppController
{

    public function index()
    {
        // Main include
        include(APP_PATH . 'libs/inc/main.php');

        $_SESSION['title'] = "Home";
    }
}
