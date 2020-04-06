<?php

/**
 * Default controller if routes are not used
 *
 */
class DbController extends AppController
{
    public function index()
    {
        error_reporting(NULL);
        $_SESSION['title'] = 'DB';

        // Main include
        include(APP_PATH . 'libs/inc/main.php');

        // Data
        exec(VESTA_CMD . "v-list-databases $user json", $output, $return_var);
        $data = json_decode(implode('', $output), true);
        $this->data = array_reverse($data, true);
        unset($output);

        // Render page
        render_page($user, $TAB, 'list_db');

        // Back uri
        $_SESSION['back'] = $_SERVER['REQUEST_URI'];
    }
    public function add()
    {
        // Main include
        include(APP_PATH . 'libs/inc/main.php');
    }
    public function edit()
    {
        // Main include
        include(APP_PATH . 'libs/inc/main.php');
    }
}
