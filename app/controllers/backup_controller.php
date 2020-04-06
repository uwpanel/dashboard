<?php

/**
 * Default controller if routes are not used
 *
 */
class BackupController extends AppController
{
    public function index()
    {
        error_reporting(NULL);
        $_SESSION['title'] = 'BACKUP';

        // Main include
        include(APP_PATH . 'libs/inc/main.php');

        // Data & Render page
        if (empty($_GET['backup'])) {
            exec(VESTA_CMD . "v-list-user-backups $user json", $output, $return_var);
            $data = json_decode(implode('', $output), true);
            $this->data = array_reverse($data, true);
            unset($output);

            render_page($user, $TAB, 'list_backup');
        } else {
            exec(VESTA_CMD . "v-list-user-backup $user " . escapeshellarg($_GET['backup']) . " json", $output, $return_var);
            $data = json_decode(implode('', $output), true);
            $this->data = array_reverse($data, true);
            unset($output);

            render_page($user, $TAB, 'list_backup_detail');
        }

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
        
    }
}
