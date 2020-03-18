<?php

/**
 * Default controller if routes are not used
 *
 */
class WebController extends AppController
{
    public function index()
    {
        error_reporting(NULL);
        $TAB = 'WEB';

        // Main include
        include(APP_PATH . 'libs/inc/main.php');

        // Data
        exec(VESTA_CMD . "v-list-web-domains $user json", $output, $return_var);
        $data = json_decode(implode('', $output), true);
        $this->data = array_reverse($data, true);
        $ips = json_decode(shell_exec(VESTA_CMD . 'v-list-sys-ips json'), true);

        // Render page
        render_page($user, $TAB, 'list_web');

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
