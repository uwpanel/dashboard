<?php

/**
 * Default controller if routes are not used
 *
 */
class LogController extends AppController
{

    public function index()
    {
        error_reporting(NULL);
        $_SESSION['title'] = 'LOG';

        // Main include
        include(APP_PATH . 'libs/inc/main.php');

        // Data
        exec(VESTA_CMD . "v-list-user-log $user json", $output, $return_var);
        check_error($return_var);
        $data = json_decode(implode('', $output), true);
        $this->data = array_reverse($data);
        unset($output);

        // Render page
        render_page($user, $TAB, 'list_log');
    }
    function translate_date($date)
    {
        $date = strtotime($date);
        return strftime("%d &nbsp;", $date) . __(strftime("%b", $date)) . strftime(" &nbsp;%Y", $date);
    }
}
