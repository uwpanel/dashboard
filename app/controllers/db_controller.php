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

        error_reporting(NULL);
        ob_start();
        $TAB = 'DB';

        // Main include
        include(APP_PATH . 'libs/inc/main.php');

        // Check POST request
        if (!empty($_POST['ok'])) {

            // echo "<pre>", print_r($_POST);
            // die();

            // Check token
            if ((!isset($_POST['token'])) || ($_SESSION['token'] != $_POST['token'])) {
                header('location: /login/');
                exit();
            }

            // Check empty fields
            if (empty($_POST['v_database'])) $errors[] = __('database');
            if (empty($_POST['v_dbuser'])) $errors[] = __('username');
            if (empty($_POST['v_password'])) $errors[] = __('password');
            if (empty($_POST['v_type'])) $errors[] = __('type');
            if (empty($_POST['v_host'])) $errors[] = __('host');
            if (empty($_POST['v_charset'])) $errors[] = __('charset');
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
            if ((!empty($_POST['v_db_email'])) && (empty($_SESSION['error_msg']))) {
                if (!filter_var($_POST['v_db_email'], FILTER_VALIDATE_EMAIL)) {
                    $_SESSION['error_msg'] = __('Please enter valid email address.');
                }
            }

            // Check password length
            if (empty($_SESSION['error_msg'])) {
                $pw_len = strlen($_POST['v_password']);
                if ($pw_len < 6) $_SESSION['error_msg'] = __('Password is too short.', $error_msg);
            }

            // Protect input
            $v_database = escapeshellarg($_POST['v_database']);
            $v_dbuser = escapeshellarg($_POST['v_dbuser']);
            $v_type = $_POST['v_type'];
            $v_charset = $_POST['v_charset'];
            $v_host = $_POST['v_host'];
            $v_db_email = $_POST['v_db_email'];

            // Add database
            if (empty($_SESSION['error_msg'])) {
                $v_type = escapeshellarg($_POST['v_type']);
                $v_charset = escapeshellarg($_POST['v_charset']);
                $v_host = escapeshellarg($_POST['v_host']);
                $v_password = tempnam("/tmp", "vst");
                $fp = fopen($v_password, "w");
                fwrite($fp, $_POST['v_password'] . "\n");
                fclose($fp);
                exec(VESTA_CMD . "v-add-database " . $user . " " . $v_database . " " . $v_dbuser . " " . $v_password . " " . $v_type . " " . $v_host . " " . $v_charset, $output, $return_var);
                check_return_code($return_var, $output);
                unset($output);
                unlink($v_password);
                $v_password = escapeshellarg($_POST['v_password']);
                $v_type = $_POST['v_type'];
                $v_host = $_POST['v_host'];
                $v_charset = $_POST['v_charset'];
            }

            // Get database manager url
            if (empty($_SESSION['error_msg'])) {
                list($http_host, $port) = explode(':', $_SERVER["HTTP_HOST"] . ":");
                if ($_POST['v_host'] != 'localhost') $http_host = $_POST['v_host'];
                if ($_POST['v_type'] == 'mysql') $db_admin = "phpMyAdmin";
                if ($_POST['v_type'] == 'mysql') $db_admin_link = "http://" . $http_host . "/phpmyadmin/";
                if (($_POST['v_type'] == 'mysql') && (!empty($_SESSION['DB_PMA_URL']))) $db_admin_link = $_SESSION['DB_PMA_URL'];
                if ($_POST['v_type'] == 'pgsql') $db_admin = "phpPgAdmin";
                if ($_POST['v_type'] == 'pgsql') $db_admin_link = "http://" . $http_host . "/phppgadmin/";
                if (($_POST['v_type'] == 'pgsql') && (!empty($_SESSION['DB_PGA_URL']))) $db_admin_link = $_SESSION['DB_PGA_URL'];
            }

            // Email login credentials
            if ((!empty($v_db_email)) && (empty($_SESSION['error_msg']))) {
                $to = $v_db_email;
                $subject = __("Database Credentials");
                $hostname = exec('hostname');
                $from = __('MAIL_FROM', $hostname);
                $mailtext = __('DATABASE_READY', $user . "_" . $_POST['v_database'], $user . "_" . $_POST['v_dbuser'], $_POST['v_password'], $db_admin_link);
                send_email($to, $subject, $mailtext, $from);
            }

            // Flush field values on success
            if (empty($_SESSION['error_msg'])) {
                $_SESSION['ok_msg'] = __('DATABASE_CREATED_OK', htmlentities($user) . "_" . htmlentities($_POST['v_database']), htmlentities($user) . "_" . htmlentities($_POST['v_database']));
                $_SESSION['ok_msg'] .= " / <a href=" . $db_admin_link . " target='_blank'>" . __('open %s', $db_admin) . "</a>";
                unset($v_database);
                unset($v_dbuser);
                unset($v_password);
                unset($v_type);
                unset($v_charset);
            }

            header('Location: /db');
        }

        // Get user email
        $v_db_email = $panel[$user]['CONTACT'];

        // List avaiable database types
        $this->db_types = explode(',', $_SESSION['DB_SYSTEM']);

        // List available database servers
        exec(VESTA_CMD . "v-list-database-hosts json", $output, $return_var);
        $db_hosts_tmp1 = json_decode(implode('', $output), true);
        $db_hosts_tmp2 = array_map(function ($host) {
            return $host['HOST'];
        }, $db_hosts_tmp1);
        $this->db_hosts = array_values(array_unique($db_hosts_tmp2));
        unset($output);
        unset($db_hosts_tmp1);
        unset($db_hosts_tmp2);

        render_page($user, $TAB, 'add_db');

        // Flush session messages
        unset($_SESSION['error_msg']);
        unset($_SESSION['ok_msg']);
    }

    public function edit($param_user, $param_db)
    {
        error_reporting(NULL);
        ob_start();

        // Main include
        include(APP_PATH . 'libs/inc/main.php');

        // Check database id
        if (empty($param_db)) {
            header("Location: /list/db/");
            exit;
        }

        // Edit as someone else?
        if (($_SESSION['user'] == 'admin') && (!empty($param_user))) {
            $user = escapeshellarg($param_user);
        }

        // List datbase
        $v_database = escapeshellarg($param_db);
        exec(VESTA_CMD . "v-list-database " . $user . " " . $v_database . " json", $output, $return_var);
        check_return_code($return_var, $output);
        $data = json_decode(implode('', $output), true);
        unset($output);

        // Parse database
        $v_username = $user;
        $v_database = $param_db;
        $this->v_dbuser = $data[$v_database]['DBUSER'];
        $v_password = "";
        $this->v_host = $data[$v_database]['HOST'];
        $this->v_type = $data[$v_database]['TYPE'];
        $this->v_charset = $data[$v_database]['CHARSET'];
        $v_date = $data[$v_database]['DATE'];
        $v_time = $data[$v_database]['TIME'];
        $this->v_suspended = $data[$v_database]['SUSPENDED'];
        if ($v_suspended == 'yes') {
            $v_status =  'suspended';
        } else {
            $v_status =  'active';
        }

        $this->v_database = escapeshellarg($param_db);

        // Check POST request
        if (!empty($_POST['save'])) {
            $v_username = $user;

            // Check token
            if ((!isset($_POST['token'])) || ($_SESSION['token'] != $_POST['token'])) {
                header('location: /login/');
                exit();
            }

            // Change database user
            if (($v_dbuser != $_POST['v_dbuser']) && (empty($_SESSION['error_msg']))) {
                $v_dbuser = preg_replace("/^" . $user . "_/", "", $_POST['v_dbuser']);
                $v_dbuser = escapeshellarg($v_dbuser);
                exec(VESTA_CMD . "v-change-database-user " . $v_username . " " . $v_database . " " . $v_dbuser, $output, $return_var);
                check_return_code($return_var, $output);
                unset($output);
                $v_dbuser = $user . "_" . preg_replace("/^" . $user . "_/", "", $_POST['v_dbuser']);
            }

            // Change database password
            if ((!empty($_POST['v_password'])) && (empty($_SESSION['error_msg']))) {
                $v_password = tempnam("/tmp", "vst");
                $fp = fopen($v_password, "w");
                fwrite($fp, $_POST['v_password'] . "\n");
                fclose($fp);
                exec(VESTA_CMD . "v-change-database-password " . $v_username . " " . $v_database . " " . $v_password, $output, $return_var);
                check_return_code($return_var, $output);
                unset($output);
                unlink($v_password);
                $v_password = escapeshellarg($_POST['v_password']);
            }

            // Set success message
            if (empty($_SESSION['error_msg'])) {
                $_SESSION['ok_msg'] = __('Changes has been saved.');
            }
        }

        // Render page
        render_page($user, $TAB, 'edit_db');

        // Flush session messages
        unset($_SESSION['error_msg']);
        unset($_SESSION['ok_msg']);
    }

    public function suspend($param_user, $param_db, $param_token)
    {
        // Main include
        include(APP_PATH . 'libs/inc/main.php');

        // Check token
        if ((!isset($param_token)) || ($_SESSION['token'] != $param_token)) {
            header('location: /login/');
            exit();
        }

        // Check user
        if ($_SESSION['user'] != 'admin') {
            header("Location: /list/user");
            exit;
        }

        if (!empty($param_user)) {
            $user = $param_user;
        }

        if (!empty($param_db)) {
            $v_username = escapeshellarg($user);
            $v_database = escapeshellarg($param_db);
            exec(VESTA_CMD . "v-suspend-database " . $v_username . " " . $v_database, $output, $return_var);
        }

        check_return_code($return_var, $output);
        unset($output);

        $back = $_SESSION['back'];
        if (!empty($back)) {
            header("Location: " . $back);
            exit;
        }

        header("Location: /list/db/");
        exit;
    }

    public function unsuspend($param_user, $param_db, $param_token)
    {
        // Main include
        include(APP_PATH . 'libs/inc/main.php');

        // Check token
        if ((!isset($param_token)) || ($_SESSION['token'] != $param_token)) {
            header('location: /login/');
            exit();
        }

        // Check user
        if ($_SESSION['user'] != 'admin') {
            header("Location: /list/user");
            exit;
        }

        if (!empty($param_user)) {
            $user = $param_user;
        }

        if (!empty($param_db)) {
            $v_username = escapeshellarg($user);
            $v_database = escapeshellarg($param_db);
            exec(VESTA_CMD . "v-unsuspend-database " . $v_username . " " . $v_database, $output, $return_var);
        }

        check_return_code($return_var, $output);
        unset($output);

        $back = $_SESSION['back'];
        if (!empty($back)) {
            header("Location: " . $back);
            exit;
        }

        header("Location: /list/db/");
        exit;
    }

    public function delete($param_user, $param_db, $param_token)
    {
        error_reporting(NULL);
        ob_start();
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

        if (!empty($param_db)) {
            $v_username = escapeshellarg($user);
            $v_database = escapeshellarg($param_db);
            exec(VESTA_CMD . "v-delete-database " . $v_username . " " . $v_database, $output, $return_var);
        }

        check_return_code($return_var, $output);
        unset($output);

        $back = $_SESSION['back'];
        if (!empty($back)) {
            header("Location: " . $back);
            exit;
        }
    }
}
