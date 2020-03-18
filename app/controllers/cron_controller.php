<?php

/**
 * Default controller if routes are not used
 *
 */
class CronController extends AppController
{
    public function index()
    {
        error_reporting(NULL);
        $TAB = 'CRON';

        // Main include
        include(APP_PATH . 'libs/inc/main.php');

        // Data
        exec(VESTA_CMD . "v-list-cron-jobs $user json", $output, $return_var);
        $data = json_decode(implode('', $output), true);
        $this->data = array_reverse($data, true);
        unset($output);

        // Render page
        render_page($user, $TAB, 'list_cron');

        // Back uri
        $_SESSION['back'] = $_SERVER['REQUEST_URI'];
    }
    public function add()
    {
    }
    public function edit()
    {
    }
}
