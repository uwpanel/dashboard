<?php

/**
 * Default controller if routes are not used
 *
 */
class IndexController extends AppController
{

    public function index()
    {
        $_SESSION['title'] = "Home";
    }
}
