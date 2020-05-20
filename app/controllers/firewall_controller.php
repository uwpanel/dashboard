<?php
class FirewallController extends AppController
{
    public function index()
    {
        // Main include
        include(APP_PATH . 'libs/inc/main.php');

        $_SESSION['title'] = "Firewall";
    }

    public function add()
    {
        error_reporting(NULL);
        ob_start();
        $_SESSION['title'] = "Adding Firewall Rule";

        // Main include
        include(APP_PATH . 'libs/inc/main.php');

        // Check user
        if ($_SESSION['user'] != 'admin' || isset($_SESSION['look'])) {
            header("Location: /");
            exit;
        }

        // Check POST request
        if (!empty($_POST['ok'])) {

            // Check token
            if ((!isset($_POST['token'])) || ($_SESSION['token'] != $_POST['token'])) {
                header('location: /login');
                exit();
            }

            // Check empty fields
            if (empty($_POST['v_action'])) $errors[] = __('action');
            if (empty($_POST['v_protocol'])) $errors[] = __('protocol');
            if (!isset($_POST['v_port'])) $errors[] = __('port');
            if (empty($_POST['v_ip'])) $errors[] = __('ip address');
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
            $this->v_action = escapeshellarg($_POST['v_action']);
            $this->v_protocol = escapeshellarg($_POST['v_protocol']);
            $v_port = str_replace(" ", ",", $_POST['v_port']);
            $v_port = preg_replace('/\,+/', ',', $v_port);
            $v_port = trim($v_port, ",");
            $this->v_port = escapeshellarg($v_port);
            $this->v_ip = escapeshellarg($_POST['v_ip']);
            $this->v_comment = escapeshellarg($_POST['v_comment']);

            // Add firewall rule
            if (empty($_SESSION['error_msg'])) {
                exec(VESTA_CMD . "v-add-firewall-rule " . $this->v_action . " " . $this->v_ip . " " . $this->v_port . " " . $this->v_protocol . " " . $this->v_comment, $output, $return_var);
                check_return_code($return_var, $output);
                unset($output);
            }

            // Flush field values on success
            if (empty($_SESSION['error_msg'])) {
                $_SESSION['ok_msg'] = __('RULE_CREATED_OK');
                unset($this->v_port);
                unset($this->v_ip);
                unset($this->v_comment);
            }
            header('Location: /server/firewall');
        }
    }

    public function edit($param_rule)
    {
        error_reporting(NULL);
        ob_start();
        $_SESSION['title'] = "Editing Firewall Rule";

        // Main include
        include(APP_PATH . 'libs/inc/main.php');

        // Check user
        if ($_SESSION['user'] != 'admin' || isset($_SESSION['look'])) {
            header("Location: /");
            exit;
        }

        // Check ip argument
        if (empty($param_rule)) {
            header("Location: /server/firewall/");
            exit;
        }

        // List rule
        $v_rule = escapeshellarg($param_rule);
        exec(VESTA_CMD . "v-list-firewall-rule " . $v_rule . " json", $output, $return_var);
        check_return_code($return_var, $output);
        $data = json_decode(implode('', $output), true);
        unset($output);

        // Parse rule
        $v_rule = $param_rule;
        $this->v_rule = $v_rule;
        $this->v_action = $data[$v_rule]['ACTION'];
        $this->v_protocol = $data[$v_rule]['PROTOCOL'];
        $this->v_port = $data[$v_rule]['PORT'];
        $this->v_ip = $data[$v_rule]['IP'];
        $this->v_comment = $data[$v_rule]['COMMENT'];
        $this->v_date = $data[$v_rule]['DATE'];
        $this->v_time = $data[$v_rule]['TIME'];
        $v_suspended = $data[$v_rule]['SUSPENDED'];
        if ($v_suspended == 'yes') {
            $this->v_status =  'suspended';
        } else {
            $this->v_status =  'active';
        }

        // Check POST request
        if (!empty($_POST['save'])) {

            // Check token
            if ((!isset($_POST['token'])) || ($_SESSION['token'] != $_POST['token'])) {
                header('location: /login');
                exit();
            }

            $v_rule = escapeshellarg($param_rule);
            $v_action = escapeshellarg($_POST['v_action']);
            $v_protocol = escapeshellarg($_POST['v_protocol']);
            $v_port = str_replace(" ", ",", $_POST['v_port']);
            $v_port = preg_replace('/\,+/', ',', $v_port);
            $v_port = trim($v_port, ",");
            $v_port = escapeshellarg($v_port);
            $v_ip = escapeshellarg($_POST['v_ip']);
            $v_comment = escapeshellarg($_POST['v_comment']);

            // Change Status
            exec(VESTA_CMD . "v-change-firewall-rule " . $v_rule . " " . $v_action . " " . $v_ip . "  " . $v_port . " " . $v_protocol . " " . $v_comment, $output, $return_var);
            check_return_code($return_var, $output);
            unset($output);

            $this->v_rule = $param_rule;
            $this->v_action = $_POST['v_action'];
            $this->v_protocol = $_POST['v_protocol'];
            $v_port = str_replace(" ", ",", $_POST['v_port']);
            $v_port = preg_replace('/\,+/', ',', $v_port);
            $this->v_port = trim($v_port, ",");
            $this->v_ip = $_POST['v_ip'];
            $this->v_comment = $_POST['v_comment'];

            // Set success message
            if (empty($_SESSION['error_msg'])) {
                $_SESSION['ok_msg'] = __('Changes has been saved.');
                header('Location: /server/firewall');
            }
        }
    }

    public function suspend($param_rule, $param_token)
    {
        // Init
        error_reporting(NULL);
        ob_start();
        session_start();
        // Main include
        include(APP_PATH . 'libs/inc/main.php');

        // Check token
        if ((!isset($param_token)) || ($_SESSION['token'] != $param_token)) {
            header('location: /login');
            exit();
        }

        // Check user
        if ($_SESSION['user'] != 'admin' || isset($_SESSION['look'])) {
            header("Location: /");
            exit;
        }

        if (!empty($param_rule)) {
            $v_rule = escapeshellarg($param_rule);
            exec(VESTA_CMD . "v-suspend-firewall-rule " . $v_rule, $output, $return_var);
        }
        check_return_code($return_var, $output);
        unset($output);

        header("Location: /server/firewall/");
        exit;
    }

    public function unsuspend($param_rule, $param_token)
    {
        // Init
        error_reporting(NULL);
        ob_start();
        session_start();
        // Main include
        include(APP_PATH . 'libs/inc/main.php');

        // Check token
        if ((!isset($param_token)) || ($_SESSION['token'] != $param_token)) {
            header('location: /login');
            exit();
        }

        // Check user
        if ($_SESSION['user'] != 'admin' || isset($_SESSION['look'])) {
            header("Location: /");
            exit;
        }

        if (!empty($param_rule)) {
            $v_rule = escapeshellarg($param_rule);
            exec(VESTA_CMD . "v-unsuspend-firewall-rule " . $v_rule, $output, $return_var);
        }
        check_return_code($return_var, $output);
        unset($output);

        header("Location: /server/firewall/");
        exit;
    }

    public function delete($param_rule, $param_token)
    {
        // Init
        error_reporting(NULL);
        ob_start();
        session_start();

        // Main include
        include(APP_PATH . 'libs/inc/main.php');

        // Check user
        if ($_SESSION['user'] != 'admin' || isset($_SESSION['look'])) {
            header("Location: /");
            exit;
        }

        // Check token
        if ((!isset($param_token)) || ($_SESSION['token'] != $param_token)) {
            header('location: /login');
            exit();
        }

        if (!empty($param_rule)) {
            $v_rule = escapeshellarg($param_rule);
            exec(VESTA_CMD . "v-delete-firewall-rule " . $v_rule, $output, $return_var);
        }
        check_return_code($return_var, $output);
        unset($output);

        header("Location: /server/firewall");
        exit;
    }
}
