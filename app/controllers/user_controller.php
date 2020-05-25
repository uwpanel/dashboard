<?php

class UserController extends AppController
{

    public function index()
    {
        error_reporting(NULL);
        $_SESSION['title'] = 'User';

        // Main include
        include(APP_PATH . 'libs/inc/main.php');
        // Data
        if ($user == 'admin' ) {
            exec(VESTA_CMD . "v-list-users json", $output, $return_var);
        } else {
            exec(VESTA_CMD . "v-list-user " . $user . " json", $output, $return_var);
        }
        $data = json_decode(implode('', $output), true);
        $this->data = array_reverse($data, true);
    }
    public function add()
    {
        error_reporting(NULL);
        ob_start();
        $_SESSION['title'] = 'Adding User';
        // Main include
        include(APP_PATH . 'libs/inc/main.php');

        // Check user
        if ($_SESSION['user'] != 'admin') {
            header("Location: /user");
            exit;
        }
        // List hosting packages
        exec(VESTA_CMD . "v-list-user-packages json", $output, $return_var);
        check_error($return_var);
        $this->data = json_decode(implode('', $output), true);
        unset($output);

        // List languages
        exec(VESTA_CMD . "v-list-sys-languages json", $output, $return_var);
        $this->languages = json_decode(implode('', $output), true);
        unset($output);

        // Check POST request
        if (!empty($_POST['ok'])) {

            // Check token
            if ((!isset($_POST['token'])) || ($_SESSION['token'] != $_POST['token'])) {
                header('location: /login/');
                exit();
            }

            // Check empty fields
            if (empty($_POST['v_username'])) $errors[] = __('user');
            if (empty($_POST['v_password'])) $errors[] = __('password');
            if (empty($_POST['v_package'])) $errrors[] = __('package');
            if (empty($_POST['v_email'])) $errors[] = __('email');
            if (empty($_POST['v_fname'])) $errors[] = __('first name');
            if (empty($_POST['v_lname'])) $errors[] = __('last name');

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

            // Validate email
            if ((empty($_SESSION['error_msg'])) && (!filter_var($_POST['v_email'], FILTER_VALIDATE_EMAIL))) {
                $_SESSION['error_msg'] = __('Please enter valid email address.');
            }

            // Check password length
            if (empty($_SESSION['error_msg'])) {
                $pw_len = strlen($_POST['v_password']);
                if ($pw_len < 6) $_SESSION['error_msg'] = __('Password is too short.', $error_msg);
            }

            // Protect input
            $v_username = escapeshellarg($_POST['v_username']);
            $v_email = escapeshellarg($_POST['v_email']);
            $v_package = escapeshellarg($_POST['v_package']);
            $v_language = escapeshellarg($_POST['v_language']);
            $v_fname = escapeshellarg($_POST['v_fname']);
            $v_lname = escapeshellarg($_POST['v_lname']);
            $v_notify = $_POST['v_notify'];


            // Add user
            if (empty($_SESSION['error_msg'])) {
                echo "<pre>", print_r($_POST);
                echo "<pre>", print_r($_SESSION['token']);
                // echo $_SESSION['error_msg'];
                $v_password = tempnam("/tmp", "vst");
                $fp = fopen($v_password, "w");
                fwrite($fp, $_POST['v_password'] . "\n");
                fclose($fp);
                exec(VESTA_CMD . "v-add-user " . $v_username . " " . $v_password . " " . $v_email . " " . $v_package . " " . $v_fname . " " . $v_lname, $output, $return_var);
                // echo print_r($output), $return_var;
                // die();
                check_return_code($return_var, $output);
                unset($output);
                unlink($v_password);
                $v_password = escapeshellarg($_POST['v_password']);
            }

            // Set language
            if (empty($_SESSION['error_msg'])) {
                exec(VESTA_CMD . "v-change-user-language " . $v_username . " " . $v_language, $output, $return_var);
                check_return_code($return_var, $output);
                unset($output);
            }

            // Send email to the new user
            if ((empty($_SESSION['error_msg'])) && (!empty($v_notify))) {
                $to = $_POST['v_notify'];
                $subject = _translate($_POST['v_language'], "Welcome to Vesta Control Panel");
                $hostname = exec('hostname');
                unset($output);
                $from = _translate($_POST['v_language'], 'MAIL_FROM', $hostname);
                if (!empty($_POST['v_fname'])) {
                    $mailtext = _translate($_POST['v_language'], 'GREETINGS_GORDON_FREEMAN', $_POST['v_fname'], $_POST['v_lname']);
                } else {
                    $mailtext = _translate($_POST['v_language'], 'GREETINGS');
                }
                $mailtext .= _translate($_POST['v_language'], 'ACCOUNT_READY', $_SERVER['HTTP_HOST'], $_POST['v_username'], $_POST['v_password']);
                send_email($to, $subject, $mailtext, $from);
            }

            // Flush field values on success
            if (empty($_SESSION['error_msg'])) {
                $_SESSION['ok_msg'] = __('USER_CREATED_OK', htmlentities($_POST['v_username']), htmlentities($_POST['v_username']));
                $_SESSION['ok_msg'] .= " / <a href=/login/?loginas=" . htmlentities($_POST['v_username']) . ">" . __('login as') . " " . htmlentities($_POST['v_username']) . "</a>";
                unset($v_username);
                unset($v_password);
                unset($v_email);
                unset($v_fname);
                unset($v_lname);
                unset($v_notify);

                header("Location: /user");
            }
        }
    }

    public function edit($param_user, $param_token = NULL)
    {
        error_reporting(NULL);
        ob_start();

        // Main include
        include(APP_PATH . 'libs/inc/main.php');
        $_SESSION['title'] = 'Editing User Account';

        // Check user argument
        if (empty($param_user)) {
            header("Location: /user/");
            exit;
        }

        // Edit as someone else?
        if (($_SESSION['user'] == 'admin') && (!empty($param_user))) {
            $user = $param_user;
            $this->v_username = $param_user;
        } else {
            $user = $_SESSION['user'];
            $this->v_username = $_SESSION['user'];
        }

        // List user
        exec(VESTA_CMD . "v-list-user " . escapeshellarg($this->v_username) . " json", $output, $return_var);
        check_return_code($return_var, $output);
        $data = json_decode(implode('', $output), true);
        unset($output);

        // Parse user
        $this->v_password = "";
        $this->v_email = $data[$this->v_username]['CONTACT'];
        $this->v_package = $data[$this->v_username]['PACKAGE'];
        $this->v_language = $data[$this->v_username]['LANGUAGE'];
        $this->v_fname = $data[$this->v_username]['FNAME'];
        $this->v_lname = $data[$this->v_username]['LNAME'];
        $this->v_shell = $data[$this->v_username]['SHELL'];
        $v_ns = $data[$this->v_username]['NS'];
        $nameservers = explode(",", $v_ns);
        $this->v_ns1 = $nameservers[0];
        $this->v_ns2 = $nameservers[1];
        $this->v_ns3 = $nameservers[2];
        $this->v_ns4 = $nameservers[3];
        $this->v_ns5 = $nameservers[4];
        $this->v_ns6 = $nameservers[5];
        $this->v_ns7 = $nameservers[6];
        $this->v_ns8 = $nameservers[7];

        $v_suspended = $data[$this->v_username]['SUSPENDED'];
        if ($v_suspended == 'yes') {
            $v_status =  'suspended';
        } else {
            $v_status =  'active';
        }
        $v_time = $data[$this->v_username]['TIME'];
        $v_date = $data[$this->v_username]['DATE'];

        // List packages
        exec(VESTA_CMD . "v-list-user-packages json", $output, $return_var);
        $this->packages = json_decode(implode('', $output), true);
        unset($output);

        // List languages
        exec(VESTA_CMD . "v-list-sys-languages json", $output, $return_var);
        $this->languages = json_decode(implode('', $output), true);
        unset($output);

        // List shells
        exec(VESTA_CMD . "v-list-sys-shells json", $output, $return_var);
        $this->shells = json_decode(implode('', $output), true);
        unset($output);

        // Are you admin?

        // Check POST request
        if (!empty($_POST['save'])) {

            // Check token
            if ((!isset($_POST['token'])) || ($_SESSION['token'] != $_POST['token'])) {
                header('location: /login/');
                exit();
            }

            // Change password
            if ((!empty($_POST['v_password'])) && (empty($_SESSION['error_msg']))) {
                $v_password = tempnam("/tmp", "vst");
                $fp = fopen($v_password, "w");
                fwrite($fp, $_POST['v_password'] . "\n");
                fclose($fp);
                exec(VESTA_CMD . "v-change-user-password " . escapeshellarg($this->v_username) . " " . $v_password, $output, $return_var);
                check_return_code($return_var, $output);
                unset($output);
                unlink($v_password);
                $v_password = escapeshellarg($_POST['v_password']);
            }

            // Change package (admin only)
            if (($v_package != $_POST['v_package']) && ($_SESSION['user'] == 'admin') && (empty($_SESSION['error_msg']))) {
                $v_package = escapeshellarg($_POST['v_package']);
                exec(VESTA_CMD . "v-change-user-package " . escapeshellarg($this->v_username) . " " . $v_package, $output, $return_var);
                check_return_code($return_var, $output);
                unset($output);
            }

            // Change language
            if (($v_language != $_POST['v_language']) && (empty($_SESSION['error_msg']))) {
                $v_language = escapeshellarg($_POST['v_language']);
                exec(VESTA_CMD . "v-change-user-language " . escapeshellarg($this->v_username) . " " . $v_language, $output, $return_var);
                check_return_code($return_var, $output);
                if (empty($_SESSION['error_msg'])) {
                    if ((empty($param_user)) || ($param_user == $_SESSION['user'])) $_SESSION['language'] = $_POST['v_language'];
                }
                unset($output);
            }

            // Change shell (admin only)
            if (($v_shell != $_POST['v_shell']) && ($_SESSION['user'] == 'admin') && (empty($_SESSION['error_msg']))) {
                $v_shell = escapeshellarg($_POST['v_shell']);
                exec(VESTA_CMD . "v-change-user-shell " . escapeshellarg($this->v_username) . " " . $v_shell, $output, $return_var);
                check_return_code($return_var, $output);
                unset($output);
            }

            // Change contact email
            if (($v_email != $_POST['v_email']) && (empty($_SESSION['error_msg']))) {
                if (!filter_var($_POST['v_email'], FILTER_VALIDATE_EMAIL)) {
                    $_SESSION['error_msg'] = __('Please enter valid email address.');
                } else {
                    $v_email = escapeshellarg($_POST['v_email']);
                    exec(VESTA_CMD . "v-change-user-contact " . escapeshellarg($this->v_username) . " " . $v_email, $output, $return_var);
                    check_return_code($return_var, $output);
                    unset($output);
                }
            }

            // Change full name
            if (($v_fname != $_POST['v_fname']) || ($v_lname != $_POST['v_lname']) && (empty($_SESSION['error_msg']))) {
                $v_fname = escapeshellarg($_POST['v_fname']);
                $v_lname = escapeshellarg($_POST['v_lname']);
                exec(VESTA_CMD . "v-change-user-name " . escapeshellarg($this->v_username) . " " . $v_fname . " " . $v_lname, $output, $return_var);
                check_return_code($return_var, $output);
                unset($output);
                $v_fname = $_POST['v_fname'];
                $v_lname = $_POST['v_lname'];
            }

            // Change NameServers
            if (($v_ns1 != $_POST['v_ns1']) || ($v_ns2 != $_POST['v_ns2']) || ($v_ns3 != $_POST['v_ns3']) || ($v_ns4 != $_POST['v_ns4']) || ($v_ns5 != $_POST['v_ns5'])
                || ($v_ns6 != $_POST['v_ns6']) || ($v_ns7 != $_POST['v_ns7']) || ($v_ns8 != $_POST['v_ns8']) && (empty($_SESSION['error_msg']))
            ) {
                $v_ns1 = escapeshellarg($_POST['v_ns1']);
                $v_ns2 = escapeshellarg($_POST['v_ns2']);
                $v_ns3 = escapeshellarg($_POST['v_ns3']);
                $v_ns4 = escapeshellarg($_POST['v_ns4']);
                $v_ns5 = escapeshellarg($_POST['v_ns5']);
                $v_ns6 = escapeshellarg($_POST['v_ns6']);
                $v_ns7 = escapeshellarg($_POST['v_ns7']);
                $v_ns8 = escapeshellarg($_POST['v_ns8']);
                $ns_cmd = VESTA_CMD . "v-change-user-ns " . escapeshellarg($this->v_username) . " " . $v_ns1 . " " . $v_ns2;
                if (!empty($_POST['v_ns3'])) $ns_cmd = $ns_cmd . " " . $v_ns3;
                if (!empty($_POST['v_ns4'])) $ns_cmd = $ns_cmd . " " . $v_ns4;
                if (!empty($_POST['v_ns5'])) $ns_cmd = $ns_cmd . " " . $v_ns5;
                if (!empty($_POST['v_ns6'])) $ns_cmd = $ns_cmd . " " . $v_ns6;
                if (!empty($_POST['v_ns7'])) $ns_cmd = $ns_cmd . " " . $v_ns7;
                if (!empty($_POST['v_ns8'])) $ns_cmd = $ns_cmd . " " . $v_ns8;
                exec($ns_cmd, $output, $return_var);
                check_return_code($return_var, $output);
                unset($output);

                $v_ns1 = str_replace("'", "", $v_ns1);
                $v_ns2 = str_replace("'", "", $v_ns2);
                $v_ns3 = str_replace("'", "", $v_ns3);
                $v_ns4 = str_replace("'", "", $v_ns4);
                $v_ns5 = str_replace("'", "", $v_ns5);
                $v_ns6 = str_replace("'", "", $v_ns6);
                $v_ns7 = str_replace("'", "", $v_ns7);
                $v_ns8 = str_replace("'", "", $v_ns8);
            }

            // Set success message
            if (empty($_SESSION['error_msg'])) {
                $_SESSION['ok_msg'] = __('Changes has been saved.');
                header("Location: /user");
            }
        }

        // Flush session messages
        unset($_SESSION['error_msg']);
        unset($_SESSION['ok_msg']);
    }
    public function suspend($param_user, $param_token)
    {
        error_reporting(NULL);
        ob_start();

        // Main include
        include(APP_PATH . 'libs/inc/main.php');

        // Check token
        if ((!isset($param_token)) || ($_SESSION['token'] != $param_token)) {
            header('location: /login');
            exit();
        }

        // Check user
        if ($_SESSION['user'] != 'admin') {
            header("Location: /user");
            exit;
        }

        if (!empty($param_user)) {
            $v_username = escapeshellarg($param_user);
            exec(VESTA_CMD . "v-suspend-user " . $v_username, $output, $return_var);
        }
        check_return_code($return_var, $output);
        unset($output);

        $back = $_SESSION['back'];
        if (!empty($back)) {
            header("Location: " . $back);
            exit;
        }

        header("Location: /user");
        exit;
    }
    public function unsuspend($param_user, $param_token)
    {
        error_reporting(NULL);
        ob_start();

        // Main include
        include(APP_PATH . 'libs/inc/main.php');

        // Check token
        if ((!isset($param_token)) || ($_SESSION['token'] != $param_token)) {
            header('location: /login');
            exit();
        }

        // Check user
        if ($_SESSION['user'] != 'admin') {
            header("Location: /user");
            exit;
        }

        if (!empty($param_user)) {
            $v_username = escapeshellarg($param_user);
            exec(VESTA_CMD . "v-unsuspend-user " . $v_username, $output, $return_var);
        }
        check_return_code($return_var, $output);
        unset($output);

        $back = $_SESSION['back'];
        if (!empty($back)) {
            header("Location: " . $back);
            exit;
        }

        header("Location: /user");
        exit;
    }
    public function delete($param_user, $param_token)
    {
        error_reporting(NULL);
        ob_start();

        // Main include
        include(APP_PATH . 'libs/inc/main.php');

        // Check token
        if ((!isset($param_token)) || ($_SESSION['token'] != $param_token)) {
            header('location: /login');
            exit();
        }

        // Check user
        if ($_SESSION['user'] == 'admin') {
            if (!empty($param_user)) {
                $v_username = escapeshellarg($param_user);
                exec(VESTA_CMD . "v-delete-user " . $v_username, $output, $return_var);
            }
            check_return_code($return_var, $output);
            unset($_SESSION['look']);
            unset($output);
        }

        $back = $_SESSION['back'];
        if (!empty($back)) {
            header("Location: " . $back);
            exit;
        }

        header("Location: /user");
        exit;
    }
}
