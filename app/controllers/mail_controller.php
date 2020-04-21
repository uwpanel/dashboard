<?php

/**
 * Default controller if routes are not used
 *
 */

class MailController extends AppController
{
    public function index()
    {
        error_reporting(NULL);
        $_SESSION['title'] = 'MAIL';

        // Main include
        include(APP_PATH . 'libs/inc/main.php');

        // Data & Render page
        // if (empty($_GET['domain'])) {
        exec(VESTA_CMD . "v-list-mail-domains $user json", $output, $return_var);

        $data = json_decode(implode('', $output), true);
        $this->data = array_reverse($data, true);
        unset($output);

        // render_page($user, $TAB, 'list_mail');
        // } else {
        //     exec(VESTA_CMD . "v-list-mail-accounts " . $user . " " . escapeshellarg($_GET['domain']) . " json", $output, $return_var);
        //     $data = json_decode(implode('', $output), true);
        //     $this->data = array_reverse($data, true);
        //     unset($output);

        //     render_page($user, $TAB, 'list_mail_acc');
        // }

        // Back uri
        $_SESSION['back'] = $_SERVER['REQUEST_URI'];
    }
    public function add()
    {
        // Main include
        include(APP_PATH . 'libs/inc/main.php');
    }

    public function addmailacc($domain)
    {
        // Main include
        include(APP_PATH . 'libs/inc/main.php');
        $this->domain = $domain;
    }

    public function addmaildomain()
    {
        // Main include
        include(APP_PATH . 'libs/inc/main.php');

        // Check POST request for mail domain
        if (!empty($_POST['ok'])) {


            // Check token
            if ((!isset($_POST['token'])) || ($_SESSION['token'] != $_POST['token'])) {
                header('location: /login/');
                exit();
            }

            // Check empty fields
            if (empty($_POST['v_domain'])) $errors[] = __('domain');
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

            // Check antispam option
            if (!empty($_POST['v_antispam'])) {
                $v_antispam = 'yes';
            } else {
                $v_antispam = 'no';
            }

            // Check antivirus option
            if (!empty($_POST['v_antivirus'])) {
                $v_antivirus = 'yes';
            } else {
                $v_antivirus = 'no';
            }

            // Check dkim option
            if (!empty($_POST['v_dkim'])) {
                $v_dkim = 'yes';
            } else {
                $v_dkim = 'no';
            }

            // Set domain name to lowercase and remove www prefix
            $v_domain = preg_replace("/^www./i", "", $_POST['v_domain']);
            $v_domain = escapeshellarg($v_domain);
            $v_domain = strtolower($v_domain);

            // Add mail domain
            if (empty($_SESSION['error_msg'])) {
                exec(VESTA_CMD . "v-add-mail-domain " . $user . " " . $v_domain . " " . $v_antispam . " " . $v_antivirus . " " . $v_dkim, $output, $return_var);
                check_return_code($return_var, $output);
                unset($output);
            }

            // Flush field values on success
            if (empty($_SESSION['error_msg'])) {
                $_SESSION['ok_msg'] = __('MAIL_DOMAIN_CREATED_OK', htmlentities($_POST['v_domain']), htmlentities($_POST['v_domain']));
                unset($v_domain);
            }
        }

        // Flush session messages
        unset($_SESSION['error_msg']);
        unset($_SESSION['ok_msg']);

        header("Location: /mail");
    }

    public function listmailacc($domain)
    {
        $this->domain = $domain;
        // Main include
        include(APP_PATH . 'libs/inc/main.php');

        exec(VESTA_CMD . "v-list-mail-accounts " . $user . " " . escapeshellarg($domain) . " json", $output, $return_var);
        $data = json_decode(implode('', $output), true);
        $this->data = array_reverse($data, true);
        unset($output);
    }

    public function addmailaccount()
    {
        // Main include
        include(APP_PATH . 'libs/inc/main.php');

        // echo "<pre>",print_r($_POST);
        // die();

        // Check POST request for mail account
        if (!empty($_POST['ok_acc'])) {

            // Check token
            if ((!isset($_POST['token'])) || ($_SESSION['token'] != $_POST['token'])) {
                header('location: /login/');
                exit();
            }

            // Check empty fields
            if (empty($_POST['v_domain'])) $errors[] = __('domain');
            if (empty($_POST['v_account'])) $errors[] = __('account');
            if (empty($_POST['v_password'])) $errors[] = __('password');
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
            if ((!empty($_POST['v_send_email'])) && (empty($_SESSION['error_msg']))) {
                if (!filter_var($_POST['v_send_email'], FILTER_VALIDATE_EMAIL)) {
                    $_SESSION['error_msg'] = __('Please enter valid email address.');
                }
            }

            // Protect input
            $v_domain = escapeshellarg($_POST['v_domain']);
            $v_domain = strtolower($v_domain);
            $v_account = escapeshellarg($_POST['v_account']);
            $v_quota = escapeshellarg($_POST['v_quota']);
            $v_send_email = $_POST['v_send_email'];
            $v_credentials = $_POST['v_credentials'];
            $v_aliases = $_POST['v_aliases'];
            $v_fwd = $_POST['v_fwd'];
            if (empty($_POST['v_quota'])) $v_quota = 0;
            if ((!empty($_POST['v_quota'])) || (!empty($_POST['v_aliases'])) || (!empty($_POST['v_fwd']))) $v_adv = 'yes';

            // Add Mail Account
            if (empty($_SESSION['error_msg'])) {
                $v_password = tempnam("/tmp", "vst");
                $fp = fopen($v_password, "w");
                fwrite($fp, $_POST['v_password'] . "\n");
                fclose($fp);
                exec(VESTA_CMD . "v-add-mail-account " . $user . " " . $v_domain . " " . $v_account . " " . $v_password . " " . $v_quota, $output, $return_var);
                check_return_code($return_var, $output);
                unset($output);
                unlink($v_password);
                $v_password = escapeshellarg($_POST['v_password']);
            }

            // Add Aliases
            if ((!empty($_POST['v_aliases'])) && (empty($_SESSION['error_msg']))) {
                $valiases = preg_replace("/\n/", " ", $_POST['v_aliases']);
                $valiases = preg_replace("/,/", " ", $valiases);
                $valiases = preg_replace('/\s+/', ' ', $valiases);
                $valiases = trim($valiases);
                $aliases = explode(" ", $valiases);
                foreach ($aliases as $alias) {
                    $alias = escapeshellarg($alias);
                    if (empty($_SESSION['error_msg'])) {
                        exec(VESTA_CMD . "v-add-mail-account-alias " . $user . " " . $v_domain . " " . $v_account . " " . $alias, $output, $return_var);
                        check_return_code($return_var, $output);
                        unset($output);
                    }
                }
            }

            // Add Forwarders
            if ((!empty($_POST['v_fwd'])) && (empty($_SESSION['error_msg']))) {
                $vfwd = preg_replace("/\n/", " ", $_POST['v_fwd']);
                $vfwd = preg_replace("/,/", " ", $vfwd);
                $vfwd = preg_replace('/\s+/', ' ', $vfwd);
                $vfwd = trim($vfwd);
                $fwd = explode(" ", $vfwd);
                foreach ($fwd as $forward) {
                    $forward = escapeshellarg($forward);
                    if (empty($_SESSION['error_msg'])) {
                        exec(VESTA_CMD . "v-add-mail-account-forward " . $user . " " . $v_domain . " " . $v_account . " " . $forward, $output, $return_var);
                        check_return_code($return_var, $output);
                        unset($output);
                    }
                }
            }

            // Add fwd_only flag
            if ((!empty($_POST['v_fwd_only'])) && (empty($_SESSION['error_msg']))) {
                exec(VESTA_CMD . "v-add-mail-account-fwd-only " . $user . " " . $v_domain . " " . $v_account, $output, $return_var);
                check_return_code($return_var, $output);
                unset($output);
            }

            // Get webmail url
            if (empty($_SESSION['error_msg'])) {
                list($http_host, $port) = explode(':', $_SERVER["HTTP_HOST"] . ":");
                $webmail = "http://" . $http_host . "/webmail/";
                if (!empty($_SESSION['MAIL_URL'])) $webmail = $_SESSION['MAIL_URL'];
            }

            // Email login credentials
            if ((!empty($v_send_email)) && (empty($_SESSION['error_msg']))) {
                $to = $v_send_email;
                $subject = __("Email Credentials");
                $hostname = exec('hostname');
                $from = __('MAIL_FROM', $hostname);
                $mailtext = $v_credentials;
                send_email($to, $subject, $mailtext, $from);
            }

            // Flush field values on success
            if (empty($_SESSION['error_msg'])) {
                $_SESSION['ok_msg'] = __('MAIL_ACCOUNT_CREATED_OK', htmlentities(strtolower($_POST['v_account'])), htmlentities($_POST[v_domain]), htmlentities(strtolower($_POST['v_account'])), htmlentities($_POST[v_domain]));
                $_SESSION['ok_msg'] .= " / <a href=" . $webmail . " target='_blank'>" . __('open webmail') . "</a>";
                unset($v_account);
                unset($v_password);
                unset($v_password);
                unset($v_aliases);
                unset($v_fwd);
                unset($v_quota);
            }
        }

        // Flush session messages
        unset($_SESSION['error_msg']);
        unset($_SESSION['ok_msg']);
    }

    public function edit($param_domain, $param_user, $param_account = NULL)
    {

        error_reporting(NULL);
        ob_start();
        $TAB = 'MAIL';

        // Main include
        include(APP_PATH . 'libs/inc/main.php');

        // Check domain argument
        if (empty($param_domain)) {
            header("Location: /list/mail/");
            exit;
        }

        // Edit as someone else?
        if (($_SESSION['user'] == 'admin') && (!empty($param_user))) {
            $user = escapeshellarg($param_user);
        }
        $v_username = $user;

        // List mail domain
        if ((!empty($param_domain)) && (empty($param_account))) {
            $v_domain = escapeshellarg($param_domain);
            exec(VESTA_CMD . "v-list-mail-domain " . $user . " " . $v_domain . " json", $output, $return_var);
            $data = json_decode(implode('', $output), true);
            unset($output);

            // Parse domain
            $v_domain = $param_domain;
            $v_antispam = $data[$v_domain]['ANTISPAM'];
            $v_antivirus = $data[$v_domain]['ANTIVIRUS'];
            $v_dkim = $data[$v_domain]['DKIM'];
            $v_catchall = $data[$v_domain]['CATCHALL'];
            $v_date = $data[$v_domain]['DATE'];
            $v_time = $data[$v_domain]['TIME'];
            $v_suspended = $data[$v_domain]['SUSPENDED'];
            if ($v_suspended == 'yes') {
                $v_status =  'suspended';
            } else {
                $v_status =  'active';
            }
        }

        // List mail account
        if ((!empty($param_domain)) && (!empty($param_account))) {
            $v_domain = escapeshellarg($param_domain);
            $v_account = escapeshellarg($param_account);
            exec(VESTA_CMD . "v-list-mail-account " . $user . " " . $v_domain . " " . $v_account . " json", $output, $return_var);
            $data = json_decode(implode('', $output), true);
            unset($output);

            // Parse mail account
            $v_username = $user;
            $v_domain = $param_domain;
            $v_account = $param_account;
            $v_password = "";
            $v_aliases = str_replace(',', "\n", $data[$v_account]['ALIAS']);
            $valiases = explode(",", $data[$v_account]['ALIAS']);
            $v_fwd = str_replace(',', "\n", $data[$v_account]['FWD']);
            $vfwd = explode(",", $data[$v_account]['FWD']);
            $v_fwd_only = $data[$v_account]['FWD_ONLY'];
            $v_quota = $data[$v_account]['QUOTA'];
            $v_autoreply = $data[$v_account]['AUTOREPLY'];
            $v_suspended = $data[$v_account]['SUSPENDED'];
            if ($v_suspended == 'yes') {
                $v_status =  'suspended';
            } else {
                $v_status =  'active';
            }
            $v_date = $data[$v_account]['DATE'];
            $v_time = $data[$v_account]['TIME'];

            $v_domain = escapeshellarg($param_domain);
            $v_account = escapeshellarg($param_account);

            // Parse autoreply
            if ($v_autoreply == 'yes') {
                exec(VESTA_CMD . "v-list-mail-account-autoreply " . $user . " " . $v_domain . " " . $v_account . " json", $output, $return_var);
                $autoreply_str = json_decode(implode('', $output), true);
                unset($output);
                $v_autoreply_message = $autoreply_str[$v_account]['MSG'];
                $v_autoreply_message = str_replace("\\n", "\n", $v_autoreply_message);
            }
        }


        // Check POST request for mail domain
        if ((!empty($_POST['save'])) && (!empty($param_domain)) && (empty($param_account))) {
            $v_domain = escapeshellarg($_POST['v_domain']);

            // Check token
            if ((!isset($_POST['token'])) || ($_SESSION['token'] != $_POST['token'])) {
                header('location: /login');
                exit();
            }

            // Delete antispam
            if (($v_antispam == 'yes') && (empty($_POST['v_antispam'])) && (empty($_SESSION['error_msg']))) {
                exec(VESTA_CMD . "v-delete-mail-domain-antispam " . $v_username . " " . $v_domain, $output, $return_var);
                check_return_code($return_var, $output);
                $v_antispam = 'no';
                unset($output);
            }

            // Add antispam
            if (($v_antispam == 'no') && (!empty($_POST['v_antispam'])) && (empty($_SESSION['error_msg']))) {
                exec(VESTA_CMD . "v-add-mail-domain-antispam " . $v_username . " " . $v_domain, $output, $return_var);
                check_return_code($return_var, $output);
                $v_antispam = 'yes';
                unset($output);
            }

            // Delete antivirus
            if (($v_antivirus == 'yes') && (empty($_POST['v_antivirus'])) && (empty($_SESSION['error_msg']))) {
                exec(VESTA_CMD . "v-delete-mail-domain-antivirus " . $v_username . " " . $v_domain, $output, $return_var);
                check_return_code($return_var, $output);
                $v_antivirus = 'no';
                unset($output);
            }

            // Add antivirs
            if (($v_antivirus == 'no') && (!empty($_POST['v_antivirus'])) && (empty($_SESSION['error_msg']))) {
                exec(VESTA_CMD . "v-add-mail-domain-antivirus " . $v_username . " " . $v_domain, $output, $return_var);
                check_return_code($return_var, $output);
                $v_antivirus = 'yes';
                unset($output);
            }

            // Delete DKIM
            if (($v_dkim == 'yes') && (empty($_POST['v_dkim'])) && (empty($_SESSION['error_msg']))) {
                exec(VESTA_CMD . "v-delete-mail-domain-dkim " . $v_username . " " . $v_domain, $output, $return_var);
                check_return_code($return_var, $output);
                $v_dkim = 'no';
                unset($output);
            }

            // Add DKIM
            if (($v_dkim == 'no') && (!empty($_POST['v_dkim'])) && (empty($_SESSION['error_msg']))) {
                exec(VESTA_CMD . "v-add-mail-domain-dkim " . $v_username . " " . $v_domain, $output, $return_var);
                check_return_code($return_var, $output);
                $v_dkim = 'yes';
                unset($output);
            }

            // Delete catchall
            if ((!empty($v_catchall)) && (empty($_POST['v_catchall'])) && (empty($_SESSION['error_msg']))) {
                exec(VESTA_CMD . "v-delete-mail-domain-catchall " . $v_username . " " . $v_domain, $output, $return_var);
                check_return_code($return_var, $output);
                $v_catchall = '';
                unset($output);
            }

            // Change catchall address
            if ((!empty($v_catchall)) && (!empty($_POST['v_catchall'])) && (empty($_SESSION['error_msg']))) {
                if ($v_catchall != $_POST['v_catchall']) {
                    $v_catchall = escapeshellarg($_POST['v_catchall']);
                    exec(VESTA_CMD . "v-change-mail-domain-catchall " . $v_username . " " . $v_domain . " " . $v_catchall, $output, $return_var);
                    check_return_code($return_var, $output);
                    unset($output);
                }
            }

            // Add catchall
            if ((empty($v_catchall)) && (!empty($_POST['v_catchall'])) && (empty($_SESSION['error_msg']))) {
                $v_catchall = escapeshellarg($_POST['v_catchall']);
                exec(VESTA_CMD . "v-add-mail-domain-catchall " . $v_username . " " . $v_domain . " " . $v_catchall, $output, $return_var);
                check_return_code($return_var, $output);
                unset($output);
            }

            // Set success message
            if (empty($_SESSION['error_msg'])) {
                $_SESSION['ok_msg'] = __('Changes has been saved.');
            }
        }

        // Check POST request for mail account
        if ((!empty($_POST['save'])) && (!empty($param_domain)) && (!empty($param_account))) {

            // Check token
            if ((!isset($_POST['token'])) || ($_SESSION['token'] != $_POST['token'])) {
                header('location: /login');
                exit();
            }

            // Validate email
            if ((!empty($_POST['v_send_email'])) && (empty($_SESSION['error_msg']))) {
                if (!filter_var($_POST['v_send_email'], FILTER_VALIDATE_EMAIL)) {
                    $_SESSION['error_msg'] = __('Please enter valid email address.');
                }
            }

            $v_domain = escapeshellarg($_POST['v_domain']);
            $v_account = escapeshellarg($_POST['v_account']);
            $v_send_email = $_POST['v_send_email'];
            $v_credentials = $_POST['v_credentials'];

            // Change password
            if ((!empty($_POST['v_password'])) && (empty($_SESSION['error_msg']))) {
                $v_password = tempnam("/tmp", "vst");
                $fp = fopen($v_password, "w");
                fwrite($fp, $_POST['v_password'] . "\n");
                fclose($fp);
                exec(VESTA_CMD . "v-change-mail-account-password " . $v_username . " " . $v_domain . " " . $v_account . " " . $v_password, $output, $return_var);
                check_return_code($return_var, $output);
                unset($output);
                unlink($v_password);
                $v_password = escapeshellarg($_POST['v_password']);;
            }

            // Change quota
            if (($v_quota != $_POST['v_quota']) && (empty($_SESSION['error_msg']))) {
                if (empty($_POST['v_quota'])) {
                    $v_quota = 0;
                } else {
                    $v_quota = escapeshellarg($_POST['v_quota']);
                }
                exec(VESTA_CMD . "v-change-mail-account-quota " . $v_username . " " . $v_domain . " " . $v_account . " " . $v_quota, $output, $return_var);
                check_return_code($return_var, $output);
                unset($output);
            }

            // Change account aliases
            if (empty($_SESSION['error_msg'])) {
                $waliases = preg_replace("/\n/", " ", $_POST['v_aliases']);
                $waliases = preg_replace("/,/", " ", $waliases);
                $waliases = preg_replace('/\s+/', ' ', $waliases);
                $waliases = trim($waliases);
                $aliases = explode(" ", $waliases);
                $v_aliases = str_replace(' ', "\n", $waliases);
                $result = array_diff($valiases, $aliases);
                foreach ($result as $alias) {
                    if ((empty($_SESSION['error_msg'])) && (!empty($alias))) {
                        exec(VESTA_CMD . "v-delete-mail-account-alias " . $v_username . " " . $v_domain . " " . $v_account . " " . escapeshellarg($alias), $output, $return_var);
                        check_return_code($return_var, $output);
                        unset($output);
                    }
                }
                $result = array_diff($aliases, $valiases);
                foreach ($result as $alias) {
                    if ((empty($_SESSION['error_msg'])) && (!empty($alias))) {
                        exec(VESTA_CMD . "v-add-mail-account-alias " . $v_username . " " . $v_domain . " " . $v_account . " " . escapeshellarg($alias), $output, $return_var);
                        check_return_code($return_var, $output);
                        unset($output);
                    }
                }
            }

            // Change forwarders
            if (empty($_SESSION['error_msg'])) {
                $wfwd = preg_replace("/\n/", " ", $_POST['v_fwd']);
                $wfwd = preg_replace("/,/", " ", $wfwd);
                $wfwd = preg_replace('/\s+/', ' ', $wfwd);
                $wfwd = trim($wfwd);
                $fwd = explode(" ", $wfwd);
                $v_fwd = str_replace(' ', "\n", $wfwd);
                $result = array_diff($vfwd, $fwd);
                foreach ($result as $forward) {
                    if ((empty($_SESSION['error_msg'])) && (!empty($forward))) {
                        exec(VESTA_CMD . "v-delete-mail-account-forward " . $v_username . " " . $v_domain . " " . $v_account . " " . escapeshellarg($forward), $output, $return_var);
                        check_return_code($return_var, $output);
                        unset($output);
                    }
                }
                $result = array_diff($fwd, $vfwd);
                foreach ($result as $forward) {
                    if ((empty($_SESSION['error_msg'])) && (!empty($forward))) {
                        exec(VESTA_CMD . "v-add-mail-account-forward " . $v_username . " " . $v_domain . " " . $v_account . " " . escapeshellarg($forward), $output, $return_var);
                        check_return_code($return_var, $output);
                        unset($output);
                    }
                }
            }

            // Delete FWD_ONLY flag
            if (($v_fwd_only == 'yes') && (empty($_POST['v_fwd_only'])) && (empty($_SESSION['error_msg']))) {
                exec(VESTA_CMD . "v-delete-mail-account-fwd-only " . $v_username . " " . $v_domain . " " . $v_account, $output, $return_var);
                check_return_code($return_var, $output);
                unset($output);
                $v_fwd_only = '';
            }

            // Add FWD_ONLY flag
            if (($v_fwd_only != 'yes') && (!empty($_POST['v_fwd_only'])) && (empty($_SESSION['error_msg']))) {
                exec(VESTA_CMD . "v-add-mail-account-fwd-only " . $v_username . " " . $v_domain . " " . $v_account, $output, $return_var);
                check_return_code($return_var, $output);
                unset($output);
                $v_fwd_only = 'yes';
            }

            // Delete autoreply
            if (($v_autoreply == 'yes') && (empty($_POST['v_autoreply'])) && (empty($_SESSION['error_msg']))) {
                exec(VESTA_CMD . "v-delete-mail-account-autoreply " . $v_username . " " . $v_domain . " " . $v_account, $output, $return_var);
                check_return_code($return_var, $output);
                unset($output);
                $v_autoreply = 'no';
                $v_autoreply_message = '';
            }

            // Add autoreply
            if ((!empty($_POST['v_autoreply'])) && (empty($_SESSION['error_msg']))) {
                if ($v_autoreply_message != str_replace("\r\n", "\n", $_POST['v_autoreply_message'])) {
                    $v_autoreply_message = str_replace("\r\n", "\n", $_POST['v_autoreply_message']);
                    $v_autoreply_message = escapeshellarg($v_autoreply_message);
                    exec(VESTA_CMD . "v-add-mail-account-autoreply " . $v_username . " " . $v_domain . " " . $v_account . " " . $v_autoreply_message, $output, $return_var);
                    check_return_code($return_var, $output);
                    unset($output);
                    $v_autoreply = 'yes';
                    $v_autoreply_message = $_POST['v_autoreply_message'];
                }
            }

            // Email login credentials
            if ((!empty($v_send_email)) && (empty($_SESSION['error_msg']))) {
                $to = $v_send_email;
                $subject = __("Email Credentials");
                $hostname = exec('hostname');
                $from = __('MAIL_FROM', $hostname);
                $mailtext = $v_credentials;
                send_email($to, $subject, $mailtext, $from);
            }

            // Set success message
            if (empty($_SESSION['error_msg'])) {
                $_SESSION['ok_msg'] = __('Changes has been saved.');
            }
        }


        // Render page
        if (empty($param_account)) {
            // Display body for mail domain
            render_page($user, $TAB, 'edit_mail');
        } else {
            // Display body for mail account
            render_page($user, $TAB, 'edit_mail_acc');
        }

        // Flush session messages
        unset($_SESSION['error_msg']);
        unset($_SESSION['ok_msg']);
    }

    public function editmailacc($param_domain, $param_user, $param_account)
    {
        // Main include
        include(APP_PATH . 'libs/inc/main.php');

        
    }
}
