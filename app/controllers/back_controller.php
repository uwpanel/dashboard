<?php

class BackController extends AppController
{
    public function index()
    {
        error_reporting(NULL);
        $_SESSION['title'] = 'Backup';

        // Main include
        include(APP_PATH . 'libs/inc/main.php');

        // Data & Render page

        exec(VESTA_CMD . "v-list-user-backups $user json", $output, $return_var);
        $data = json_decode(implode('', $output), true);
        $this->data = array_reverse($data, true);
        unset($output);

        // Back uri
        $_SESSION['back'] = $_SERVER['REQUEST_URI'];
    }

    public function listbackup($param_backup)
    {
        error_reporting(NULL);
        $_SESSION['title'] = 'Listing Backup';

        // Main include
        include(APP_PATH . 'libs/inc/main.php');

        // Data & Render page
        if (isset($param_backup)) {
            exec(VESTA_CMD . "v-list-user-backup $user " . escapeshellarg($param_backup) . " json", $output, $return_var);
            $data = json_decode(implode('', $output), true);
            $this->data = array_reverse($data, true);
            unset($output);


            $this->web = explode(',', $this->data[$param_backup]['WEB']);
            $this->mail = explode(',', $this->data[$param_backup]['MAIL']);
            $this->dns = explode(',', $this->data[$param_backup]['DNS']);
            $this->db = explode(',', $this->data[$param_backup]['DB']);
            $this->backup = explode(',', $this->data[$param_backup]['BACKUP']);
            $this->udir = explode(',', $this->data[$param_backup]['UDIR']);

        }
        // Back uri
        $_SESSION['back'] = $_SERVER['REQUEST_URI'];
    }



    public function add()
    {
        // Init
        error_reporting(NULL);
        ob_start();
        session_start();

        // Main include
        include(APP_PATH . 'libs/inc/main.php');

        $v_username = escapeshellarg($user);
        exec(VESTA_CMD . "v-schedule-user-backup " . $v_username, $output, $return_var);
        if ($return_var == 0) {
            $_SESSION['error_msg'] = __('BACKUP_SCHEDULED');
        } else {
            $_SESSION['error_msg'] = implode('<br>', $output);
            if (empty($_SESSION['error_msg'])) {
                $_SESSION['error_msg'] = __('Error: vesta did not return any output.');
            }

            if ($return_var == 4) {
                $_SESSION['error_msg'] = __('BACKUP_EXISTS');
            }
        }
        unset($output);
        header("Location: /back");
        exit;
    }

    public function delete($param_user, $param_backup, $param_token)
    {
        // Init
        error_reporting(NULL);
        ob_start();
        session_start();

        // Main include
        include(APP_PATH . 'libs/inc/main.php');

        if (($_SESSION['user'] == 'admin') && (!empty($param_user))) {
            $user = $param_user;
        }

        // Check token
        if ((!isset($param_token)) || ($_SESSION['token'] != $param_token)) {
            header('location: /login/');
            exit();
        }

        if (!empty($param_backup)) {
            $v_username = escapeshellarg($user);
            $v_backup = escapeshellarg($param_backup);
            exec(VESTA_CMD . "v-delete-user-backup " . $v_username . " " . $v_backup, $output, $return_var);
        }
        check_return_code($return_var, $output);
        unset($output);

        header("Location: /back");
        exit;
    }

    public function download($param_backup)
    {
        // Init
        error_reporting(NULL);
        session_start();

        // Main include
        include(APP_PATH . 'libs/inc/main.php');

        $backup = basename($param_backup);

        // Check if the backup exists
        if (!file_exists('/backup/' . $backup)) {
            exit(0);
        }

        // Data
        if ($_SESSION['user'] == 'admin') {
            header('Content-type: application/gzip');
            header("Content-Disposition: attachment; filename=\"" . $backup . "\";");
            header("X-Accel-Redirect: /backup/" . $backup);
        }

        if ((!empty($_SESSION['user'])) && ($_SESSION['user'] != 'admin')) {
            if (strpos($backup, $user . '.') === 0) {
                header('Content-type: application/gzip');
                header("Content-Disposition: attachment; filename=\"" . $backup . "\";");
                header("X-Accel-Redirect: /backup/" . $backup);
            }
        }
    }

    public function restore($param_backup, $param_type, $param_object)
    {
        // Init
        error_reporting(NULL);
        ob_start();
        session_start();

        // Main include
        include(APP_PATH . 'libs/inc/main.php');

        $backup = escapeshellarg($param_backup);

        $web = 'no';
        $dns = 'no';
        $mail = 'no';
        $db = 'no';
        $cron = 'no';
        $udir = 'no';

        if ($param_type == 'dns') $dns = escapeshellarg($param_object);
        if ($param_type == 'web') $web = escapeshellarg($param_object);
        if ($param_type == 'mail') $mail = escapeshellarg($param_object);
        if ($param_type == 'db') $db = escapeshellarg($param_object);
        if ($param_type == 'cron') $cron = 'yes';
        if ($param_type == 'udir') $udir = escapeshellarg($param_object);

        if (!empty($param_type)) {
            $restore_cmd = VESTA_CMD . "v-schedule-user-restore " . $user . " " . $backup . " " . $web . " " . $dns . " " . $mail . " " . $db . " " . $cron . " " . $udir;
        } else {
            $restore_cmd = VESTA_CMD . "v-schedule-user-restore " . $user . " " . $backup;
        }

        exec($restore_cmd, $output, $return_var);
        if ($return_var == 0) {
            $_SESSION['error_msg'] = __('RESTORE_SCHEDULED');
        } else {
            $_SESSION['error_msg'] = implode('<br>', $output);
            if (empty($_SESSION['error_msg'])) {
                $_SESSION['error_msg'] = __('Error: vesta did not return any output.');
            }
            if ($return_var == 4) {
                $_SESSION['error_msg'] = __('RESTORE_EXISTS');
            }
        }

        header("Location: /back/listbackup/" . $param_backup);
    }
}
