<?php

/**
 * Default controller if routes are not used
 *
 */
class StatsController extends AppController
{

    public function index()
    {

        error_reporting(NULL);
        $_SESSION['title'] = 'STATS';

        // Main include
        include(APP_PATH . 'libs/inc/main.php');

        $user = $_SESSION['user'];
        // Data
        if ($user == 'admin') {
            if (empty($_GET['user'])) {
                exec(VESTA_CMD . "v-list-users-stats json", $output, $return_var);
                $data = json_decode(implode('', $output), true);
                $this->data = array_reverse($data, true);
                unset($output);
            } else {
                $v_user = escapeshellarg($_GET['user']);
                exec(VESTA_CMD . "v-list-user-stats $v_user json", $output, $return_var);
                $data = json_decode(implode('', $output), true);
                $this->data = array_reverse($data, true);
                unset($output);
            }

            exec(VESTA_CMD . "v-list-sys-users json", $output, $return_var);
            $this->users = json_decode(implode('', $output), true);
            unset($output);
        } else {
            exec(VESTA_CMD . "v-list-user-stats $user json", $output, $return_var);
            $data = json_decode(implode('', $output), true);
            $this->data = array_reverse($data, true);
            unset($output);
        }

        // Render page
        render_page($user, $TAB, 'list_stats');

        // Back uri
        $_SESSION['back'] = $_SERVER['REQUEST_URI'];
    }
}
