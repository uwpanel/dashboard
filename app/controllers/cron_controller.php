<?php

class CronController extends AppController
{
    public function index()
    {
        error_reporting(NULL);
        $_SESSION['title'] = 'CRON';

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

    public function add($param_user = NULL, $param_job = NULL)
    {
        // Init
        error_reporting(NULL);
        ob_start();

        // Main include
        include(APP_PATH . 'libs/inc/main.php');

        // Check POST request
        if (!empty($_POST['ok'])) {

            // Check token
            if ((!isset($_POST['token'])) || ($_SESSION['token'] != $_POST['token'])) {
                header('location: /login/');
                exit();
            }

            // Check empty fields
            if ((!isset($_POST['v_min'])) || ($_POST['v_min'] == '')) $errors[] = __('minute');
            if ((!isset($_POST['v_hour'])) || ($_POST['v_hour'] == '')) $errors[] = __('hour');
            if ((!isset($_POST['v_day'])) || ($_POST['v_day'] == '')) $errors[] = __('day');
            if ((!isset($_POST['v_month'])) || ($_POST['v_month'] == '')) $errors[] = __('month');
            if ((!isset($_POST['v_wday'])) || ($_POST['v_wday'] == '')) $errors[] = __('day of week');
            if ((!isset($_POST['v_cmd'])) || ($_POST['v_cmd'] == '')) $errors[] = __('cmd');
            if (!empty($errors[0])) {
                foreach ($errors as $i => $error) {
                    if ($i == 0) {
                        $error_msg = $error;
                    } else {
                        $error_msg = $error_msg . ", " . $error;
                    }
                }
                $_SESSION['error_msg'] = __('Field "%s" can not be blank.', $error_msg);
            }

            // Protect input
            $v_min = escapeshellarg($_POST['v_min']);
            $v_hour = escapeshellarg($_POST['v_hour']);
            $v_day = escapeshellarg($_POST['v_day']);
            $v_month = escapeshellarg($_POST['v_month']);
            $v_wday = escapeshellarg($_POST['v_wday']);
            $v_cmd = escapeshellarg($_POST['v_cmd']);

            // Add cron job
            if (empty($_SESSION['error_msg'])) {
                exec(VESTA_CMD . "v-add-cron-job " . $user . " " . $v_min . " " . $v_hour . " " . $v_day . " " . $v_month . " " . $v_wday . " " . $v_cmd, $output, $return_var);
                check_return_code($return_var, $output);
                unset($output);
            }

            // Flush field values on success
            if (empty($_SESSION['error_msg'])) {
                $_SESSION['ok_msg'] = __('CRON_CREATED_OK');
                unset($v_min);
                unset($v_hour);
                unset($v_day);
                unset($v_month);
                unset($v_wday);
                unset($v_cmd);
                unset($output);
            }
            header('Location: /cron');
        }

        // Render
        render_page($user, $TAB, 'add_cron');

        // Flush session messages
        unset($_SESSION['error_msg']);
        unset($_SESSION['ok_msg']);
    }

    public function edit($param_user, $param_job, $param_token)
    {

        error_reporting(NULL);
        ob_start();
        session_start();
        $TAB = 'CRON';

        // Main include
        include(APP_PATH . 'libs/inc/main.php');

        // Edit as someone else?
        if (($_SESSION['user'] == 'admin') && (!empty($param_user))) {
            $user = escapeshellarg($param_user);
        }

        // Check job id
        if (empty($param_job)) {
            header("Location: /cron");
            exit;
        }

        // List cron job
        $v_job = escapeshellarg($param_job);
        exec(VESTA_CMD . "v-list-cron-job " . $user . " " . $v_job . " json", $output, $return_var);
        check_return_code($return_var, $output);

        $data = json_decode(implode('', $output), true);
        unset($output);

        // Parse cron job
        $v_username = $user;
        $v_job = $param_job;
        $this->job_id = $param_job;
        $this->v_min = $data[$v_job]['MIN'];
        $this->v_hour = $data[$v_job]['HOUR'];
        $this->v_day = $data[$v_job]['DAY'];
        $this->v_month = $data[$v_job]['MONTH'];
        $this->v_wday = $data[$v_job]['WDAY'];
        $this->v_cmd = $data[$v_job]['CMD'];
        $v_date = $data[$v_job]['DATE'];
        $v_time = $data[$v_job]['TIME'];
        $v_suspended = $data[$v_job]['SUSPENDED'];
        if ($v_suspended == 'yes') {
            $v_status =  'suspended';
        } else {
            $v_status =  'active';
        }

        // Check POST request
        if (!empty($_POST['save'])) {

            // Check token
            if ((!isset($_POST['token'])) || ($_SESSION['token'] != $_POST['token'])) {
                header('location: /login/');
                exit();
            }

            $v_username = $user;
            $v_job = escapeshellarg($param_job);
            $v_min = escapeshellarg($_POST['v_min']);
            $v_hour = escapeshellarg($_POST['v_hour']);
            $v_day = escapeshellarg($_POST['v_day']);
            $v_month = escapeshellarg($_POST['v_month']);
            $v_wday = escapeshellarg($_POST['v_wday']);
            $v_cmd = escapeshellarg($_POST['v_cmd']);

            // Save changes
            exec(VESTA_CMD . "v-change-cron-job " . $v_username . " " . $v_job . " " . $v_min . " " . $v_hour . " " . $v_day . " " . $v_month . " " . $v_wday . " " . $v_cmd, $output, $return_var);
            check_return_code($return_var, $output);
            unset($output);

            $v_cmd = $_POST['v_cmd'];

            // Set success message
            if (empty($_SESSION['error_msg'])) {
                $_SESSION['ok_msg'] = __("Changes has been saved.");
            }

            header("Location: /cron");
        }

        // Render page
        render_page($user, $TAB, 'edit_cron');

        // Flush session messages
        unset($_SESSION['error_msg']);
        unset($_SESSION['ok_msg']);
    }

    public function suspend($param_user, $param_job, $param_token)
    {
        // Init
        error_reporting(NULL);
        ob_start();
        session_start();

        // Main include
        include(APP_PATH . 'libs/inc/main.php');

        // Check token
        if ((!isset($param_token)) || ($_SESSION['token'] != $param_token)) {
            header('location: /login/');
            exit();
        }

        // Check user
        if ($_SESSION['user'] != 'admin') {
            header("Location: /user");
            exit;
        }
        if (!empty($param_user)) {
            $user = $param_user;
        }
        if (!empty($param_job)) {
            $v_username = escapeshellarg($user);
            $v_job = escapeshellarg($param_job);
            exec(VESTA_CMD . "v-suspend-cron-job " . $v_username . " " . $v_job, $output, $return_var);
        }
        check_return_code($return_var, $output);
        unset($output);

        $back = $_SESSION['back'];
        if (!empty($back)) {
            header("Location: " . $back);
            exit;
        }

        header("Location: /cron");
        exit;
    }

    public function unsuspend($param_user, $param_job, $param_token)
    {
        // Init
        error_reporting(NULL);
        ob_start();
        session_start();

        // Main include
        include(APP_PATH . 'libs/inc/main.php');

        // Check token
        if ((!isset($param_token)) || ($_SESSION['token'] != $param_token)) {
            header('location: /login/');
            exit();
        }

        // Check user
        if ($_SESSION['user'] != 'admin') {
            header("Location: /user");
            exit;
        }

        if (!empty($param_user)) {
            $user = $param_user;
        }

        if (!empty($param_job)) {
            $v_username = escapeshellarg($user);
            $v_job = escapeshellarg($param_job);
            exec(VESTA_CMD . "v-unsuspend-cron-job " . $v_username . " " . $v_job, $output, $return_var);
        }
        check_return_code($return_var, $output);
        unset($output);

        $back = getenv("HTTP_REFERER");
        if (!empty($back)) {
            header("Location: " . $back);
            exit;
        }

        header("Location: /cron");
        exit;
    }

    public function delete($param_user, $param_job, $param_token)
    {
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

        if (!empty($param_job)) {
            $v_username = escapeshellarg($user);
            $v_job = escapeshellarg($param_job);
            exec(VESTA_CMD . "v-delete-cron-job " . $v_username . " " . $v_job, $output, $return_var);
        }
        check_return_code($return_var, $output);
        unset($output);

        $back = $_SESSION['back'];
        if (!empty($back)) {
            header("Location: " . $back);
            exit;
        }

        header("Location: /cron");
        exit;
    }
}
