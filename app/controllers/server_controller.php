<?php

class ServerController extends AppController
{
    public function index()
    {
        error_reporting(NULL);
        $_SESSION['title'] = 'SERVER';

        // Main include
        include(APP_PATH . 'libs/inc/main.php');

        // Check user
        if ($_SESSION['user'] != 'admin') {
            header("Location: /user");
            exit;
        }

        // Data
        exec(VESTA_CMD . "v-list-sys-info json", $output, $return_var);
        $this->sys = json_decode(implode('', $output), true);
        unset($output);
        exec(VESTA_CMD . "v-list-sys-services json", $output, $return_var);
        $this->data = json_decode(implode('', $output), true);
        unset($output);

        // Back uri
        $_SESSION['back'] = $_SERVER['REQUEST_URI'];
    }

    public function info($param_type)
    {
        error_reporting(NULL);
        $_SESSION['title'] = 'SERVER INFO';

        // Main include
        include(APP_PATH . 'libs/inc/main.php');

        // Check user
        if ($_SESSION['user'] != 'admin') {
            header("Location: /user");
            exit;
        }

        if (isset($param_type)) {

            // CPU info
            if ($param_type == 'cpu') {
                $TAB = 'CPU';
                include($_SERVER['DOCUMENT_ROOT'] . '/templates/admin/list_server_info.html');
                exec(VESTA_CMD . 'v-list-sys-cpu-status', $output, $return_var);
                $this->data = $output;
            }

            // Memory info
            if ($param_type == 'mem') {
                $TAB = 'MEMORY';
                include($_SERVER['DOCUMENT_ROOT'] . '/templates/admin/list_server_info.html');
                exec(VESTA_CMD . 'v-list-sys-memory-status', $output, $return_var);
                $this->data = $output;
            }

            // Disk info
            if ($param_type == 'disk') {
                $TAB = 'DISK';
                include($_SERVER['DOCUMENT_ROOT'] . '/templates/admin/list_server_info.html');
                exec(VESTA_CMD . 'v-list-sys-disk-status', $output, $return_var);
                $this->data = $output;
            }

            // Network info
            if ($param_type == 'net') {
                $TAB = 'NETWORK';
                include($_SERVER['DOCUMENT_ROOT'] . '/templates/admin/list_server_info.html');
                exec(VESTA_CMD . 'v-list-sys-network-status', $output, $return_var);
                $this->data = $output;
            }

            // Web info
            if ($param_type == 'web') {
                $TAB = 'WEB';
                include($_SERVER['DOCUMENT_ROOT'] . '/templates/admin/list_server_info.html');
                exec(VESTA_CMD . 'v-list-sys-web-status', $output, $return_var);
                $this->data = $output;
            }


            // DNS info
            if ($param_type == 'dns') {
                $TAB = 'DNS';
                include($_SERVER['DOCUMENT_ROOT'] . '/templates/admin/list_server_info.html');
                exec(VESTA_CMD . 'v-list-sys-dns-status', $output, $return_var);
                $this->data = $output;
            }

            // Mail info
            if ($param_type == 'mail') {
                $TAB = 'MAIL';
                include($_SERVER['DOCUMENT_ROOT'] . '/templates/admin/list_server_info.html');
                exec(VESTA_CMD . 'v-list-sys-mail-status', $output, $return_var);
                if ($return_var == 0) {
                    $this->data = $output;
                }
            }

            // DB info
            if ($param_type == 'db') {
                $TAB = 'DB';
                include($_SERVER['DOCUMENT_ROOT'] . '/templates/admin/list_server_info.html');
                exec(VESTA_CMD . 'v-list-sys-db-status', $output, $return_var);
                if ($return_var == 0) {
                    $this->data = $output;
                }
            }
        }
    }

    public function edit($param_type = NULL)
    {
        error_reporting(NULL);
        $_SESSION['title'] = 'SERVER';

        // Main include
        include(APP_PATH . 'libs/inc/main.php');

        // Check user
        if ($_SESSION['user'] != 'admin') {
            header("Location: /user");
            exit;
        }

        // Get server hostname
        $v_hostname = exec('hostname');

        // List available timezones and get current one
        $v_timezones = list_timezones();
        exec(VESTA_CMD . "v-get-sys-timezone", $output, $return_var);
        $v_timezone = $output[0];
        unset($output);
        if ($v_timezone == 'Etc/UTC') $v_timezone = 'UTC';
        if ($v_timezone == 'Pacific/Honolulu') $v_timezone = 'HAST';
        if ($v_timezone == 'US/Aleutian') $v_timezone = 'HADT';
        if ($v_timezone == 'Etc/GMT+9') $v_timezone = 'AKST';
        if ($v_timezone == 'America/Anchorage') $v_timezone = 'AKDT';
        if ($v_timezone == 'America/Dawson_Creek') $v_timezone = 'PST';
        if ($v_timezone == 'PST8PDT') $v_timezone = 'PDT';
        if ($v_timezone == 'MST7MDT') $v_timezone = 'MDT';
        if ($v_timezone == 'Canada/Saskatchewan') $v_timezone = 'CST';
        if ($v_timezone == 'CST6CDT') $v_timezone = 'CDT';
        if ($v_timezone == 'EST5EDT') $v_timezone = 'EDT';
        if ($v_timezone == 'America/Puerto_Rico') $v_timezone = 'AST';
        if ($v_timezone == 'America/Halifax') $v_timezone = 'ADT';

        // List supported languages
        exec(VESTA_CMD . "v-list-sys-languages json", $output, $return_var);
        $this->languages = json_decode(implode('', $output), true);
        unset($output);

        // List dns cluster hosts
        exec(VESTA_CMD . "v-list-remote-dns-hosts json", $output, $return_var);
        $this->dns_cluster = json_decode(implode('', $output), true);
        unset($output);
        foreach ($this->dns_cluster as $key => $value) {
            $this->v_dns_cluster = 'yes';
        }

        // List Database hosts
        exec(VESTA_CMD . "v-list-database-hosts json", $output, $return_var);
        $db_hosts = json_decode(implode('', $output), true);
        unset($output);
        $v_mysql_hosts = array_values(array_filter($db_hosts, function ($host) {
            return $host['TYPE'] === 'mysql';
        }));
        $v_mysql = count($v_mysql_hosts) ? 'yes' : 'no';
        $v_pgsql_hosts = array_values(array_filter($db_hosts, function ($host) {
            return $host['TYPE'] === 'pgsql';
        }));
        $v_pgsql = count($v_pgsql_hosts) ? 'yes' : 'no';
        unset($db_hosts);

        // List backup settings
        $v_backup_dir = "/backup";
        if (!empty($_SESSION['BACKUP'])) $v_backup_dir = $_SESSION['BACKUP'];
        $v_backup_gzip = '5';
        if (!empty($_SESSION['BACKUP_GZIP'])) $v_backup_gzip = $_SESSION['BACKUP_GZIP'];
        $backup_types = explode(",", $_SESSION['BACKUP_SYSTEM']);
        foreach ($backup_types as $backup_type) {
            if ($backup_type == 'local') {
                $v_backup = 'yes';
            } else {
                exec(VESTA_CMD . "v-list-backup-host " . $backup_type . " json", $output, $return_var);
                $v_remote_backup = json_decode(implode('', $output), true);
                unset($output);
                $v_backup_host = $v_remote_backup[$backup_type]['HOST'];
                $v_backup_type = $v_remote_backup[$backup_type]['TYPE'];
                $v_backup_username = $v_remote_backup[$backup_type]['USERNAME'];
                $v_backup_password = "";
                $v_backup_port = $v_remote_backup[$backup_type]['PORT'];
                $v_backup_bpath = $v_remote_backup[$backup_type]['BPATH'];
            }
        }

        // List ssl web domains
        exec(VESTA_CMD . "v-search-ssl-certificates json", $output, $return_var);
        $this->v_ssl_domains = json_decode(implode('', $output), true);
        //$v_vesta_certificate
        unset($output);

        // List ssl certificate info
        exec(VESTA_CMD . "v-list-sys-vesta-ssl json", $output, $return_var);
        $v_sys_ssl_str = json_decode(implode('', $output), true);
        unset($output);
        $v_sys_ssl_crt = $v_sys_ssl_str['VESTA']['CRT'];
        $v_sys_ssl_key = $v_sys_ssl_str['VESTA']['KEY'];
        $v_sys_ssl_ca = $v_sys_ssl_str['VESTA']['CA'];
        $v_sys_ssl_subject = $v_sys_ssl_str['VESTA']['SUBJECT'];
        $v_sys_ssl_aliases = $v_sys_ssl_str['VESTA']['ALIASES'];
        $v_sys_ssl_not_before = $v_sys_ssl_str['VESTA']['NOT_BEFORE'];
        $v_sys_ssl_not_after = $v_sys_ssl_str['VESTA']['NOT_AFTER'];
        $v_sys_ssl_signature = $v_sys_ssl_str['VESTA']['SIGNATURE'];
        $v_sys_ssl_pub_key = $v_sys_ssl_str['VESTA']['PUB_KEY'];
        $v_sys_ssl_issuer = $v_sys_ssl_str['VESTA']['ISSUER'];

        // List mail ssl certificate info
        if (!empty($_SESSION['VESTA_CERTIFICATE'])); {
            exec(VESTA_CMD . "v-list-sys-mail-ssl json", $output, $return_var);
            $v_mail_ssl_str = json_decode(implode('', $output), true);
            unset($output);
            $v_mail_ssl_crt = $v_mail_ssl_str['MAIL']['CRT'];
            $v_mail_ssl_key = $v_mail_ssl_str['MAIL']['KEY'];
            $v_mail_ssl_ca = $v_mail_ssl_str['MAIL']['CA'];
            $v_mail_ssl_subject = $v_mail_ssl_str['MAIL']['SUBJECT'];
            $v_mail_ssl_aliases = $v_mail_ssl_str['MAIL']['ALIASES'];
            $v_mail_ssl_not_before = $v_mail_ssl_str['MAIL']['NOT_BEFORE'];
            $v_mail_ssl_not_after = $v_mail_ssl_str['MAIL']['NOT_AFTER'];
            $v_mail_ssl_signature = $v_mail_ssl_str['MAIL']['SIGNATURE'];
            $v_mail_ssl_pub_key = $v_mail_ssl_str['MAIL']['PUB_KEY'];
            $v_mail_ssl_issuer = $v_mail_ssl_str['MAIL']['ISSUER'];
        }

        // Check POST request
        if (!empty($_POST['save'])) {

            // Check token
            if ((!isset($_POST['token'])) || ($_SESSION['token'] != $_POST['token'])) {
                header('location: /login/');
                exit();
            }

            // Change hostname
            if ((!empty($_POST['v_hostname'])) && ($v_hostname != $_POST['v_hostname'])) {
                exec(VESTA_CMD . "v-change-sys-hostname " . escapeshellarg($_POST['v_hostname']), $output, $return_var);
                check_return_code($return_var, $output);
                unset($output);
                $v_hostname = $_POST['v_hostname'];
            }

            // Change timezone
            if (empty($_SESSION['error_msg'])) {
                if (!empty($_POST['v_timezone'])) {
                    $v_tz = $_POST['v_timezone'];
                    if ($v_tz == 'UTC') $v_tz = 'Etc/UTC';
                    if ($v_tz == 'HAST') $v_tz = 'Pacific/Honolulu';
                    if ($v_tz == 'HADT') $v_tz = 'US/Aleutian';
                    if ($v_tz == 'AKST') $v_tz = 'Etc/GMT+9';
                    if ($v_tz == 'AKDT') $v_tz = 'America/Anchorage';
                    if ($v_tz == 'PST') $v_tz = 'America/Dawson_Creek';
                    if ($v_tz == 'PDT') $v_tz = 'PST8PDT';
                    if ($v_tz == 'MDT') $v_tz = 'MST7MDT';
                    if ($v_tz == 'CST') $v_tz = 'Canada/Saskatchewan';
                    if ($v_tz == 'CDT') $v_tz = 'CST6CDT';
                    if ($v_tz == 'EDT') $v_tz = 'EST5EDT';
                    if ($v_tz == 'AST') $v_tz = 'America/Puerto_Rico';
                    if ($v_tz == 'ADT') $v_tz = 'America/Halifax';

                    if ($v_timezone != $v_tz) {
                        exec(VESTA_CMD . "v-change-sys-timezone " . escapeshellarg($v_tz), $output, $return_var);
                        check_return_code($return_var, $output);
                        $v_timezone = $v_tz;
                        unset($output);
                    }
                }
            }

            // Change default language
            if (empty($_SESSION['error_msg'])) {
                if ((!empty($_POST['v_language'])) && ($_SESSION['LANGUAGE'] != $_POST['v_language'])) {
                    exec(VESTA_CMD . "v-change-sys-language " . escapeshellarg($_POST['v_language']), $output, $return_var);
                    check_return_code($return_var, $output);
                    unset($output);
                    if (empty($_SESSION['error_msg'])) $_SESSION['LANGUAGE'] = $_POST['v_language'];
                }
            }

            // Set disk_quota support
            if (empty($_SESSION['error_msg'])) {
                if ((!empty($_POST['v_quota'])) && ($_SESSION['DISK_QUOTA'] != $_POST['v_quota'])) {
                    if ($_POST['v_quota'] == 'yes') {
                        exec(VESTA_CMD . "v-add-sys-quota", $output, $return_var);
                        check_return_code($return_var, $output);
                        unset($output);
                        if (empty($_SESSION['error_msg'])) $_SESSION['DISK_QUOTA'] = 'yes';
                    } else {
                        exec(VESTA_CMD . "v-delete-sys-quota", $output, $return_var);
                        check_return_code($return_var, $output);
                        unset($output);
                        if (empty($_SESSION['error_msg'])) $_SESSION['DISK_QUOTA'] = 'no';
                    }
                }
            }

            // Set firewall support
            if (empty($_SESSION['error_msg'])) {
                if ($_SESSION['FIREWALL_SYSTEM'] == 'iptables') $v_firewall = 'yes';
                if ($_SESSION['FIREWALL_SYSTEM'] != 'iptables') $v_firewall = 'no';
                if ((!empty($_POST['v_firewall'])) && ($v_firewall != $_POST['v_firewall'])) {
                    if ($_POST['v_firewall'] == 'yes') {
                        exec(VESTA_CMD . "v-add-sys-firewall", $output, $return_var);
                        check_return_code($return_var, $output);
                        unset($output);
                        if (empty($_SESSION['error_msg'])) $_SESSION['FIREWALL_SYSTEM'] = 'iptables';
                    } else {
                        exec(VESTA_CMD . "v-delete-sys-firewall", $output, $return_var);
                        check_return_code($return_var, $output);
                        unset($output);
                        if (empty($_SESSION['error_msg'])) $_SESSION['FIREWALL_SYSTEM'] = '';
                    }
                }
            }

            // Update mysql pasword
            if (empty($_SESSION['error_msg'])) {
                if (!empty($_POST['v_mysql_password'])) {
                    exec(VESTA_CMD . "v-change-database-host-password mysql localhost root " . escapeshellarg($_POST['v_mysql_password']), $output, $return_var);
                    check_return_code($return_var, $output);
                    unset($output);
                    $v_db_adv = 'yes';
                }
            }


            // Delete Mail Domain SSL certificate
            if ((!isset($_POST['v_mail_ssl_domain_checkbox'])) && (!empty($_SESSION['MAIL_CERTIFICATE'])) && (empty($_SESSION['error_msg']))) {
                unset($_SESSION['MAIL_CERTIFICATE']);
                exec(VESTA_CMD . "v-delete-sys-mail-ssl", $output, $return_var);
                check_return_code($return_var, $output);
                unset($output);
            }

            // Updating Mail Domain SSL certificate
            if ((isset($_POST['v_mail_ssl_domain_checkbox'])) && (isset($_POST['v_mail_ssl_domain'])) && (empty($_SESSION['error_msg']))) {
                if ((!empty($_POST['v_mail_ssl_domain'])) && ($_POST['v_mail_ssl_domain'] != $_SESSION['MAIL_CERTIFICATE'])) {
                    $v_mail_ssl_str = explode(":", $_POST['v_mail_ssl_domain']);
                    $v_mail_ssl_user = escapeshellarg($v_mail_ssl_str[0]);
                    $v_mail_ssl_domain = escapeshellarg($v_mail_ssl_str[1]);
                    exec(VESTA_CMD . "v-add-sys-mail-ssl " . $v_mail_ssl_user . " " . $v_mail_ssl_domain, $output, $return_var);
                    check_return_code($return_var, $output);
                    unset($output);
                    unset($v_mail_ssl_str);

                    if (empty($_SESSION['error_msg'])) {
                        $_SESSION['MAIL_CERTIFICATE'] = $_POST['v_mail_ssl_domain'];

                        // List SSL certificate info
                        exec(VESTA_CMD . "v-list-sys-mail-ssl json", $output, $return_var);
                        $v_mail_ssl_str = json_decode(implode('', $output), true);
                        unset($output);
                        $v_mail_ssl_crt = $v_mail_ssl_str['MAIL']['CRT'];
                        $v_mail_ssl_key = $v_mail_ssl_str['MAIL']['KEY'];
                        $v_mail_ssl_ca = $v_mail_ssl_str['MAIL']['CA'];
                        $v_mail_ssl_subject = $v_mail_ssl_str['MAIL']['SUBJECT'];
                        $v_mail_ssl_aliases = $v_mail_ssl_str['MAIL']['ALIASES'];
                        $v_mail_ssl_not_before = $v_mail_ssl_str['MAIL']['NOT_BEFORE'];
                        $v_mail_ssl_not_after = $v_mail_ssl_str['MAIL']['NOT_AFTER'];
                        $v_mail_ssl_signature = $v_mail_ssl_str['MAIL']['SIGNATURE'];
                        $v_mail_ssl_pub_key = $v_mail_ssl_str['MAIL']['PUB_KEY'];
                        $v_mail_ssl_issuer = $v_mail_ssl_str['MAIL']['ISSUER'];
                    }
                }
            }

            // Update webmail url
            if (empty($_SESSION['error_msg'])) {
                if ($_POST['v_mail_url'] != $_SESSION['MAIL_URL']) {
                    exec(VESTA_CMD . "v-change-sys-config-value MAIL_URL " . escapeshellarg($_POST['v_mail_url']), $output, $return_var);
                    check_return_code($return_var, $output);
                    unset($output);
                    $v_mail_adv = 'yes';
                }
            }

            // Update phpMyAdmin url
            if (empty($_SESSION['error_msg'])) {
                if ($_POST['v_mysql_url'] != $_SESSION['DB_PMA_URL']) {
                    exec(VESTA_CMD . "v-change-sys-config-value DB_PMA_URL " . escapeshellarg($_POST['v_mysql_url']), $output, $return_var);
                    check_return_code($return_var, $output);
                    unset($output);
                    $v_db_adv = 'yes';
                }
            }

            // Update phpPgAdmin url
            if (empty($_SESSION['error_msg'])) {
                if ($_POST['v_pgsql_url'] != $_SESSION['DB_PGA_URL']) {
                    exec(VESTA_CMD . "v-change-sys-config-value DB_PGA_URL " . escapeshellarg($_POST['v_pgsql_url']), $output, $return_var);
                    check_return_code($return_var, $output);
                    unset($output);
                    $v_db_adv = 'yes';
                }
            }

            // Disable local backup
            if (empty($_SESSION['error_msg'])) {
                if (($_POST['v_backup'] == 'no') && ($v_backup == 'yes')) {
                    exec(VESTA_CMD . "v-delete-backup-host local", $output, $return_var);
                    check_return_code($return_var, $output);
                    unset($output);
                    if (empty($_SESSION['error_msg'])) $v_backup = 'no';
                    $v_backup_adv = 'yes';
                }
            }

            // Enable local backups
            if (empty($_SESSION['error_msg'])) {
                if (($_POST['v_backup'] == 'yes') && ($v_backup != 'yes')) {
                    exec(VESTA_CMD . "v-add-backup-host local", $output, $return_var);
                    check_return_code($return_var, $output);
                    unset($output);
                    if (empty($_SESSION['error_msg'])) $v_backup = 'yes';
                    $v_backup_adv = 'yes';
                }
            }

            // Change backup gzip level
            if (empty($_SESSION['error_msg'])) {
                if ($_POST['v_backup_gzip'] != $v_backup_gzip) {
                    exec(VESTA_CMD . "v-change-sys-config-value BACKUP_GZIP " . escapeshellarg($_POST['v_backup_gzip']), $output, $return_var);
                    check_return_code($return_var, $output);
                    unset($output);
                    if (empty($_SESSION['error_msg'])) $v_backup_gzip = $_POST['v_backup_gzip'];
                    $v_backup_adv = 'yes';
                }
            }

            // Change backup path
            if (empty($_SESSION['error_msg'])) {
                if ($_POST['v_backup_dir'] != $v_backup_dir) {
                    exec(VESTA_CMD . "v-change-sys-config-value BACKUP " . escapeshellarg($_POST['v_backup_dir']), $output, $return_var);
                    check_return_code($return_var, $output);
                    unset($output);
                    if (empty($_SESSION['error_msg'])) $v_backup_dir = $_POST['v_backup_dir'];
                    $v_backup_adv = 'yes';
                }
            }

            // Add remote backup host
            if (empty($_SESSION['error_msg'])) {
                if ((!empty($_POST['v_backup_host'])) && (empty($v_backup_host))) {
                    $v_backup_host = escapeshellarg($_POST['v_backup_host']);
                    $v_backup_type = escapeshellarg($_POST['v_backup_type']);
                    $v_backup_username = escapeshellarg($_POST['v_backup_username']);
                    $v_backup_password = escapeshellarg($_POST['v_backup_password']);
                    $v_backup_bpath = escapeshellarg($_POST['v_backup_bpath']);
                    exec(VESTA_CMD . "v-add-backup-host " . $v_backup_type . " " . $v_backup_host . " " . $v_backup_username . " " . $v_backup_password . " " . $v_backup_bpath, $output, $return_var);
                    check_return_code($return_var, $output);
                    unset($output);
                    if (empty($_SESSION['error_msg'])) $v_backup_host = $_POST['v_backup_host'];
                    if (empty($_SESSION['error_msg'])) $v_backup_type = $_POST['v_backup_type'];
                    if (empty($_SESSION['error_msg'])) $v_backup_username = $_POST['v_backup_username'];
                    if (empty($_SESSION['error_msg'])) $v_backup_password = $_POST['v_backup_password'];
                    if (empty($_SESSION['error_msg'])) $v_backup_bpath = $_POST['v_backup_bpath'];
                    $v_backup_new = 'yes';
                    $v_backup_adv = 'yes';
                    $v_backup_remote_adv = 'yes';
                }
            }

            // Change remote backup host type
            if (empty($_SESSION['error_msg'])) {
                if ((!empty($_POST['v_backup_host'])) && ($_POST['v_backup_type'] != $v_backup_type)) {
                    exec(VESTA_CMD . "v-delete-backup-host " . $v_backup_type, $output, $return_var);
                    unset($output);

                    $v_backup_host = escapeshellarg($_POST['v_backup_host']);
                    $v_backup_type = escapeshellarg($_POST['v_backup_type']);
                    $v_backup_username = escapeshellarg($_POST['v_backup_username']);
                    $v_backup_password = escapeshellarg($_POST['v_backup_password']);
                    $v_backup_bpath = escapeshellarg($_POST['v_backup_bpath']);
                    exec(VESTA_CMD . "v-add-backup-host " . $v_backup_type . " " . $v_backup_host . " " . $v_backup_username . " " . $v_backup_password . " " . $v_backup_bpath, $output, $return_var);
                    check_return_code($return_var, $output);
                    unset($output);
                    if (empty($_SESSION['error_msg'])) $v_backup_host = $_POST['v_backup_host'];
                    if (empty($_SESSION['error_msg'])) $v_backup_type = $_POST['v_backup_type'];
                    if (empty($_SESSION['error_msg'])) $v_backup_username = $_POST['v_backup_username'];
                    if (empty($_SESSION['error_msg'])) $v_backup_password = $_POST['v_backup_password'];
                    if (empty($_SESSION['error_msg'])) $v_backup_bpath = $_POST['v_backup_bpath'];
                    $v_backup_adv = 'yes';
                    $v_backup_remote_adv = 'yes';
                }
            }

            // Change remote backup host
            if (empty($_SESSION['error_msg'])) {
                if ((!empty($_POST['v_backup_host'])) && ($_POST['v_backup_type'] == $v_backup_type) && (!isset($v_backup_new))) {
                    if (($_POST['v_backup_host'] != $v_backup_host) || ($_POST['v_backup_username'] != $v_backup_username) || ($_POST['v_backup_password'] != $v_backup_password) || ($_POST['v_backup_bpath'] != $v_backup_bpath)) {
                        $v_backup_host = escapeshellarg($_POST['v_backup_host']);
                        $v_backup_type = escapeshellarg($_POST['v_backup_type']);
                        $v_backup_username = escapeshellarg($_POST['v_backup_username']);
                        $v_backup_password = escapeshellarg($_POST['v_backup_password']);
                        $v_backup_bpath = escapeshellarg($_POST['v_backup_bpath']);
                        exec(VESTA_CMD . "v-add-backup-host " . $v_backup_type . " " . $v_backup_host . " " . $v_backup_username . " " . $v_backup_password . " " . $v_backup_bpath, $output, $return_var);
                        check_return_code($return_var, $output);
                        unset($output);
                        if (empty($_SESSION['error_msg'])) $v_backup_host = $_POST['v_backup_host'];
                        if (empty($_SESSION['error_msg'])) $v_backup_type = $_POST['v_backup_type'];
                        if (empty($_SESSION['error_msg'])) $v_backup_username = $_POST['v_backup_username'];
                        if (empty($_SESSION['error_msg'])) $v_backup_password = $_POST['v_backup_password'];
                        if (empty($_SESSION['error_msg'])) $v_backup_bpath = $_POST['v_backup_bpath'];
                        $v_backup_adv = 'yes';
                        $v_backup_remote_adv = 'yes';
                    }
                }
            }

            // Delete remote backup host
            if (empty($_SESSION['error_msg'])) {
                if ((empty($_POST['v_backup_host'])) && (!empty($v_backup_host))) {
                    exec(VESTA_CMD . "v-delete-backup-host " . $v_backup_type, $output, $return_var);
                    check_return_code($return_var, $output);
                    unset($output);
                    if (empty($_SESSION['error_msg'])) $v_backup_host = '';
                    if (empty($_SESSION['error_msg'])) $v_backup_type = '';
                    if (empty($_SESSION['error_msg'])) $v_backup_username = '';
                    if (empty($_SESSION['error_msg'])) $v_backup_password = '';
                    if (empty($_SESSION['error_msg'])) $v_backup_bpath = '';
                    $v_backup_adv = '';
                    $v_backup_remote_adv = '';
                }
            }



            // Delete WEB Domain SSL certificate
            if ((!isset($_POST['v_web_ssl_domain_checkbox'])) && (!empty($_SESSION['VESTA_CERTIFICATE'])) && (empty($_SESSION['error_msg']))) {
                unset($_SESSION['VESTA_CERTIFICATE']);
                exec(VESTA_CMD . "v-delete-sys-vesta-ssl", $output, $return_var);
                check_return_code($return_var, $output);
                unset($output);
            }

            // Updating WEB Domain SSL certificate
            if ((isset($_POST['v_web_ssl_domain_checkbox'])) && (isset($_POST['v_web_ssl_domain'])) && (empty($_SESSION['error_msg']))) {

                if ((!empty($_POST['v_web_ssl_domain'])) && ($_POST['v_web_ssl_domain'] != $_SESSION['VESTA_CERTIFICATE'])) {
                    $v_web_ssl_str = explode(":", $_POST['v_web_ssl_domain']);
                    $v_web_ssl_user = escapeshellarg($v_web_ssl_str[0]);
                    $v_web_ssl_domain = escapeshellarg($v_web_ssl_str[1]);
                    exec(VESTA_CMD . "v-add-sys-vesta-ssl " . $v_web_ssl_user . " " . $v_web_ssl_domain, $output, $return_var);
                    check_return_code($return_var, $output);
                    unset($output);

                    if (empty($_SESSION['error_msg'])) {
                        $_SESSION['VESTA_CERTIFICATE'] = $_POST['v_web_ssl_domain'];

                        // List SSL certificate info
                        exec(VESTA_CMD . "v-list-sys-vesta-ssl json", $output, $return_var);
                        $v_sys_ssl_str = json_decode(implode('', $output), true);
                        unset($output);
                        $v_sys_ssl_crt = $v_sys_ssl_str['VESTA']['CRT'];
                        $v_sys_ssl_key = $v_sys_ssl_str['VESTA']['KEY'];
                        $v_sys_ssl_ca = $v_sys_ssl_str['VESTA']['CA'];
                        $v_sys_ssl_subject = $v_sys_ssl_str['VESTA']['SUBJECT'];
                        $v_sys_ssl_aliases = $v_sys_ssl_str['VESTA']['ALIASES'];
                        $v_sys_ssl_not_before = $v_sys_ssl_str['VESTA']['NOT_BEFORE'];
                        $v_sys_ssl_not_after = $v_sys_ssl_str['VESTA']['NOT_AFTER'];
                        $v_sys_ssl_signature = $v_sys_ssl_str['VESTA']['SIGNATURE'];
                        $v_sys_ssl_pub_key = $v_sys_ssl_str['VESTA']['PUB_KEY'];
                        $v_sys_ssl_issuer = $v_sys_ssl_str['VESTA']['ISSUER'];
                    }
                }
            }


            // Update SSL certificate
            if ((!empty($_POST['v_sys_ssl_crt'])) && (empty($_POST['v_web_ssl_domain'])) && (empty($_SESSION['error_msg']))) {
                if (($v_sys_ssl_crt != str_replace("\r\n", "\n",  $_POST['v_sys_ssl_crt'])) || ($v_sys_ssl_key != str_replace("\r\n", "\n",  $_POST['v_sys_ssl_key']))) {
                    exec('mktemp -d', $mktemp_output, $return_var);
                    $tmpdir = $mktemp_output[0];

                    // Certificate
                    if (!empty($_POST['v_sys_ssl_crt'])) {
                        $fp = fopen($tmpdir . "/certificate.crt", 'w');
                        fwrite($fp, str_replace("\r\n", "\n",  $_POST['v_sys_ssl_crt']));
                        fwrite($fp, "\n");
                        fclose($fp);
                    }

                    // Key
                    if (!empty($_POST['v_sys_ssl_key'])) {
                        $fp = fopen($tmpdir . "/certificate.key", 'w');
                        fwrite($fp, str_replace("\r\n", "\n", $_POST['v_sys_ssl_key']));
                        fwrite($fp, "\n");
                        fclose($fp);
                    }

                    exec(VESTA_CMD . "v-change-sys-vesta-ssl " . $tmpdir, $output, $return_var);
                    check_return_code($return_var, $output);
                    unset($output);

                    if (empty($_SESSION['error_msg'])) {
                        // List ssl certificate info
                        exec(VESTA_CMD . "v-list-sys-vesta-ssl json", $output, $return_var);
                        $v_sys_ssl_str = json_decode(implode('', $output), true);
                        unset($output);
                        $v_sys_ssl_crt = $v_sys_ssl_str['VESTA']['CRT'];
                        $v_sys_ssl_key = $v_sys_ssl_str['VESTA']['KEY'];
                        $v_sys_ssl_ca = $v_sys_ssl_str['VESTA']['CA'];
                        $v_sys_ssl_subject = $v_sys_ssl_str['VESTA']['SUBJECT'];
                        $v_sys_ssl_aliases = $v_sys_ssl_str['VESTA']['ALIASES'];
                        $v_sys_ssl_not_before = $v_sys_ssl_str['VESTA']['NOT_BEFORE'];
                        $v_sys_ssl_not_after = $v_sys_ssl_str['VESTA']['NOT_AFTER'];
                        $v_sys_ssl_signature = $v_sys_ssl_str['VESTA']['SIGNATURE'];
                        $v_sys_ssl_pub_key = $v_sys_ssl_str['VESTA']['PUB_KEY'];
                        $v_sys_ssl_issuer = $v_sys_ssl_str['VESTA']['ISSUER'];
                    }
                }
            }

            // Flush field values on success
            if (empty($_SESSION['error_msg'])) {
                $_SESSION['ok_msg'] = __('Changes has been saved.');
            }

            // activating sftp licence
            if (empty($_SESSION['error_msg'])) {
                if ($_SESSION['SFTPJAIL_KEY'] != $_POST['v_sftp_licence'] && $_POST['v_sftp'] == 'yes') {
                    $module = 'sftpjail';
                    $licence_key = escapeshellarg($_POST['v_sftp_licence']);
                    exec(VESTA_CMD . "v-activate-vesta-license " . $module . " " . $licence_key, $output, $return_var);
                    check_return_code($return_var, $output);
                    unset($output);
                    if (empty($_SESSION['error_msg'])) {
                        $_SESSION['ok_msg'] = __('Licence Activated');
                        $_SESSION['SFTPJAIL_KEY'] = $_POST['v_sftp_licence'];
                    }
                }
            }

            // cancel sftp licence
            if (empty($_SESSION['error_msg'])) {
                if ($_POST['v_sftp'] == 'cancel' && $_SESSION['SFTPJAIL_KEY']) {
                    $module = 'sftpjail';
                    $licence_key = escapeshellarg($_SESSION['SFTPJAIL_KEY']);
                    exec(VESTA_CMD . "v-deactivate-vesta-license " . $module . " " . $licence_key, $output, $return_var);
                    check_return_code($return_var, $output);
                    unset($output);
                    if (empty($_SESSION['error_msg'])) {
                        $_SESSION['ok_msg'] = __('Licence Deactivated');
                        unset($_SESSION['SFTPJAIL_KEY']);
                    }
                }
            }

            // activating filemanager licence
            if (empty($_SESSION['error_msg'])) {
                if ($_SESSION['FILEMANAGER_KEY'] != $_POST['v_filemanager_licence'] && $_POST['v_filemanager'] == 'yes') {
                    $module = 'filemanager';
                    $licence_key = escapeshellarg($_POST['v_filemanager_licence']);
                    exec(VESTA_CMD . "v-activate-vesta-license " . $module . " " . $licence_key, $output, $return_var);
                    check_return_code($return_var, $output);
                    unset($output);
                    if (empty($_SESSION['error_msg'])) {
                        $_SESSION['ok_msg'] = __('Licence Activated');
                        $_SESSION['FILEMANAGER_KEY'] = $_POST['v_filemanager_licence'];
                    }
                }
            }

            // cancel filemanager licence
            if (empty($_SESSION['error_msg'])) {
                if ($_POST['v_filemanager'] == 'cancel' && $_SESSION['FILEMANAGER_KEY']) {
                    $module = 'filemanager';
                    $licence_key = escapeshellarg($_SESSION['FILEMANAGER_KEY']);
                    exec(VESTA_CMD . "v-deactivate-vesta-license " . $module . " " . $licence_key, $output, $return_var);
                    check_return_code($return_var, $output);
                    unset($output);
                    if (empty($_SESSION['error_msg'])) {
                        $_SESSION['ok_msg'] = __('Licence Deactivated');
                        unset($_SESSION['FILEMANAGER_KEY']);
                    }
                }
            }

            // activating softaculous
            if (empty($_SESSION['error_msg'])) {
                if ($_SESSION['SOFTACULOUS'] != $_POST['v_softaculous'] && $_POST['v_softaculous'] == 'yes') {
                    exec(VESTA_CMD . "v-add-vesta-softaculous WEB", $output, $return_var);
                    check_return_code($return_var, $output);
                    unset($output);
                    if (empty($_SESSION['error_msg'])) {
                        $_SESSION['ok_msg'] = __('Softaculous Activated');
                        $_SESSION['SOFTACULOUS'] = 'yes';
                    }
                }
            }

            // disable softaculous
            if (empty($_SESSION['error_msg'])) {
                if ($_SESSION['SOFTACULOUS'] != $_POST['v_softaculous'] && $_POST['v_softaculous'] == 'no') {
                    exec(VESTA_CMD . "v-delete-vesta-softaculous", $output, $return_var);
                    check_return_code($return_var, $output);
                    unset($output);
                    if (empty($_SESSION['error_msg'])) {
                        $_SESSION['ok_msg'] = __('Softaculous Disabled');
                        $_SESSION['SOFTACULOUS'] = '';
                    }
                }
            }
        }

        // Check system configuration
        exec(VESTA_CMD . "v-list-sys-config json", $output, $return_var);
        $data = json_decode(implode('', $output), true);
        unset($output);

        $sys_arr = $data['config'];
        foreach ($sys_arr as $key => $value) {
            $_SESSION[$key] = $value;
        }


        // Render page
        render_page($user, $TAB, 'edit_server');

        // Flush session messages
        unset($_SESSION['error_msg']);
        unset($_SESSION['ok_msg']);

        if ($param_type == "apache2") {
            error_reporting(NULL);
            $TAB = 'SERVER';

            // Main include
            include(APP_PATH . 'libs/inc/main.php');

            // Check user
            if ($_SESSION['user'] != 'admin') {
                header("Location: /list/user");
                exit;
            }

            // Check POST request
            if (!empty($_POST['save'])) {

                // Check token
                if ((!isset($_POST['token'])) || ($_SESSION['token'] != $_POST['token'])) {
                    header('location: /login/');
                    exit();
                }

                // Set restart flag
                $v_restart = 'yes';
                if (empty($_POST['v_restart'])) $v_restart = 'no';

                // Update config
                if (!empty($_POST['v_config'])) {
                    exec('mktemp', $mktemp_output, $return_var);
                    $new_conf = $mktemp_output[0];
                    $fp = fopen($new_conf, 'w');
                    fwrite($fp, str_replace("\r\n", "\n",  $_POST['v_config']));
                    fclose($fp);
                    exec(VESTA_CMD . "v-change-sys-service-config " . $new_conf . " apache2 " . $v_restart, $output, $return_var);
                    check_return_code($return_var, $output);
                    unset($output);
                    unlink($new_conf);
                }

                // Set success message
                if (empty($_SESSION['error_msg'])) {
                    $_SESSION['ok_msg'] = __('Changes has been saved.');
                }
            }

            $v_config_path = '/etc/apache2/apache2.conf';
            $v_service_name = strtoupper('apache2');

            // Read config
            $v_config = shell_exec(VESTA_CMD . "v-open-fs-config " . $v_config_path);

            // Render page
            render_page($user, $TAB, 'edit_server_httpd');

            // Flush session messages
            unset($_SESSION['error_msg']);
            unset($_SESSION['ok_msg']);
        }

        if ($param_type == "bind9") {
            error_reporting(NULL);
            $TAB = 'SERVER';

            // Main include
            include(APP_PATH . 'libs/inc/main.php');

            // Check user
            if ($_SESSION['user'] != 'admin') {
                header("Location: /list/user");
                exit;
            }

            // Check POST request
            if (!empty($_POST['save'])) {

                // Check token
                if ((!isset($_POST['token'])) || ($_SESSION['token'] != $_POST['token'])) {
                    header('location: /login/');
                    exit();
                }

                // Set restart flag
                $v_restart = 'yes';
                if (empty($_POST['v_restart'])) $v_restart = 'no';

                // Update options
                if (!empty($_POST['v_options'])) {
                    exec('mktemp', $mktemp_output, $return_var);
                    $new_conf = $mktemp_output[0];
                    $fp = fopen($new_conf, 'w');
                    fwrite($fp, str_replace("\r\n", "\n",  $_POST['v_options']));
                    fclose($fp);
                    exec(VESTA_CMD . "v-change-sys-service-config " . $new_conf . " bind9-opt " . $v_restart, $output, $return_var);
                    check_return_code($return_var, $output);
                    unset($output);
                    unlink($new_conf);
                }

                // Update config
                if ((empty($_SESSION['error_msg'])) && (!empty($_POST['v_config']))) {
                    exec('mktemp', $mktemp_output, $return_var);
                    $new_conf = $mktemp_output[0];
                    $fp = fopen($new_conf, 'w');
                    fwrite($fp, str_replace("\r\n", "\n",  $_POST['v_config']));
                    fclose($fp);
                    exec(VESTA_CMD . "v-change-sys-service-config " . $new_conf . " bind9 " . $v_restart, $output, $return_var);
                    check_return_code($return_var, $output);
                    unset($output);
                    unlink($new_conf);
                }

                // Set success message
                if (empty($_SESSION['error_msg'])) {
                    $_SESSION['ok_msg'] = __('Changes has been saved.');
                }
            }

            $v_options_path = '/etc/bind/named.conf.options';
            $v_config_path = '/etc/bind/named.conf';
            $v_service_name = strtoupper('bind9');

            // Read config
            $v_options = shell_exec(VESTA_CMD . "v-open-fs-config " . $v_options_path);
            $v_config = shell_exec(VESTA_CMD . "v-open-fs-config " . $v_config_path);

            // Render page
            render_page($user, $TAB, 'edit_server_bind9');

            // Flush session messages
            unset($_SESSION['error_msg']);
            unset($_SESSION['ok_msg']);
        }

        if ($param_type == "clamd") {
            error_reporting(NULL);
            $TAB = 'SERVER';

            // Main include
            include(APP_PATH . 'libs/inc/main.php');

            // Check user
            if ($_SESSION['user'] != 'admin') {
                header("Location: /list/user");
                exit;
            }

            // Check POST request
            if (!empty($_POST['save'])) {

                // Check token
                if ((!isset($_POST['token'])) || ($_SESSION['token'] != $_POST['token'])) {
                    header('location: /login/');
                    exit();
                }

                // Set restart flag
                $v_restart = 'yes';
                if (empty($_POST['v_restart'])) $v_restart = 'no';

                // Update config
                if (!empty($_POST['v_config'])) {
                    exec('mktemp', $mktemp_output, $return_var);
                    $new_conf = $mktemp_output[0];
                    $fp = fopen($new_conf, 'w');
                    fwrite($fp, str_replace("\r\n", "\n",  $_POST['v_config']));
                    fclose($fp);
                    exec(VESTA_CMD . "v-change-sys-service-config " . $new_conf . " clamd " . $v_restart, $output, $return_var);
                    check_return_code($return_var, $output);
                    unset($output);
                    unlink($new_conf);
                }

                // Set success message
                if (empty($_SESSION['error_msg'])) {
                    $_SESSION['ok_msg'] = __('Changes has been saved.');
                }
            }

            $v_config_path = shell_exec(VESTA_CMD . 'v-list-sys-clamd-config plain');
            $v_service_name = strtoupper('clamav');

            // Read config
            $v_config = shell_exec(VESTA_CMD . "v-open-fs-config " . $v_config_path);

            // Render page
            render_page($user, $TAB, 'edit_server_service');

            // Flush session messages
            unset($_SESSION['error_msg']);
            unset($_SESSION['ok_msg']);
        }

        if ($param_type == "cron") {
            error_reporting(NULL);
            $TAB = 'SERVER';

            // Main include
            include(APP_PATH . 'libs/inc/main.php');

            // Check user
            if ($_SESSION['user'] != 'admin') {
                header("Location: /list/user");
                exit;
            }

            // Check POST request
            if (!empty($_POST['save'])) {

                // Check token
                if ((!isset($_POST['token'])) || ($_SESSION['token'] != $_POST['token'])) {
                    header('location: /login/');
                    exit();
                }

                // Set restart flag
                $v_restart = 'yes';
                if (empty($_POST['v_restart'])) $v_restart = 'no';

                // Update config
                if (!empty($_POST['v_config'])) {
                    exec('mktemp', $mktemp_output, $return_var);
                    $new_conf = $mktemp_output[0];
                    $fp = fopen($new_conf, 'w');
                    fwrite($fp, str_replace("\r\n", "\n",  $_POST['v_config']));
                    fclose($fp);
                    exec(VESTA_CMD . "v-change-sys-service-config " . $new_conf . " cron " . $v_restart, $output, $return_var);
                    check_return_code($return_var, $output);
                    unset($output);
                    unlink($new_conf);
                }

                // Set success message
                if (empty($_SESSION['error_msg'])) {
                    $_SESSION['ok_msg'] = __('Changes has been saved.');
                }
            }

            $v_config_path = '/etc/crontab';
            $v_service_name = strtoupper('cron');

            // Read config
            $v_config = shell_exec(VESTA_CMD . "v-open-fs-config " . $v_config_path);

            // Render page
            render_page($user, $TAB, 'edit_server_service');

            // Flush session messages
            unset($_SESSION['error_msg']);
            unset($_SESSION['ok_msg']);
        }

        if ($param_type == "crond") {
            error_reporting(NULL);
            $TAB = 'SERVER';

            // Main include
            include(APP_PATH . 'libs/inc/main.php');

            // Check user
            if ($_SESSION['user'] != 'admin') {
                header("Location: /list/user");
                exit;
            }

            // Check POST request
            if (!empty($_POST['save'])) {

                // Check token
                if ((!isset($_POST['token'])) || ($_SESSION['token'] != $_POST['token'])) {
                    header('location: /login/');
                    exit();
                }

                // Set restart flag
                $v_restart = 'yes';
                if (empty($_POST['v_restart'])) $v_restart = 'no';

                // Update config
                if (!empty($_POST['v_config'])) {
                    exec('mktemp', $mktemp_output, $return_var);
                    $new_conf = $mktemp_output[0];
                    $fp = fopen($new_conf, 'w');
                    fwrite($fp, str_replace("\r\n", "\n",  $_POST['v_config']));
                    fclose($fp);
                    exec(VESTA_CMD . "v-change-sys-service-config " . $new_conf . " crond " . $v_restart, $output, $return_var);
                    check_return_code($return_var, $output);
                    unset($output);
                    unlink($new_conf);
                }

                // Set success message
                if (empty($_SESSION['error_msg'])) {
                    $_SESSION['ok_msg'] = __('Changes has been saved.');
                }
            }

            $v_config_path = '/etc/crontab';
            $v_service_name = strtoupper('cron');

            // Read config
            $v_config = shell_exec(VESTA_CMD . "v-open-fs-config " . $v_config_path);

            // Render page
            render_page($user, $TAB, 'edit_server_service');

            // Flush session messages
            unset($_SESSION['error_msg']);
            unset($_SESSION['ok_msg']);
        }

        if ($param_type == "dovecot") {
            error_reporting(NULL);
            $TAB = 'SERVER';

            // Main include
            include(APP_PATH . 'libs/inc/main.php');

            // Check user
            if ($_SESSION['user'] != 'admin') {
                header("Location: /list/user");
                exit;
            }

            // Check POST request
            if (!empty($_POST['save'])) {

                // Check token
                if ((!isset($_POST['token'])) || ($_SESSION['token'] != $_POST['token'])) {
                    header('location: /login/');
                    exit();
                }

                // Set restart flag
                $v_restart = 'yes';
                if (empty($_POST['v_restart'])) $v_restart = 'no';

                // Update config
                if (!empty($_POST['v_config'])) {
                    exec('mktemp', $mktemp_output, $return_var);
                    $new_conf = $mktemp_output[0];
                    $fp = fopen($new_conf, 'w');
                    fwrite($fp, str_replace("\r\n", "\n",  $_POST['v_config']));
                    fclose($fp);
                    exec(VESTA_CMD . "v-change-sys-service-config " . $new_conf . " dovecot " . $v_restart, $output, $return_var);
                    check_return_code($return_var, $output);
                    unset($output);
                    unlink($new_conf);
                }

                // Update config1
                if ((empty($_SESSION['error_msg'])) && (!empty($_POST['v_config1']))) {
                    exec('mktemp', $mktemp_output, $return_var);
                    $new_conf = $mktemp_output[0];
                    $fp = fopen($new_conf, 'w');
                    fwrite($fp, str_replace("\r\n", "\n",  $_POST['v_config1']));
                    fclose($fp);
                    exec(VESTA_CMD . "v-change-sys-service-config " . $new_conf . " dovecot-1 " . $v_restart, $output, $return_var);
                    check_return_code($return_var, $output);
                    unset($output);
                    unlink($new_conf);
                }

                // Update config2
                if ((empty($_SESSION['error_msg'])) && (!empty($_POST['v_config2']))) {
                    exec('mktemp', $mktemp_output, $return_var);
                    $new_conf = $mktemp_output[0];
                    $fp = fopen($new_conf, 'w');
                    fwrite($fp, str_replace("\r\n", "\n",  $_POST['v_config2']));
                    fclose($fp);
                    exec(VESTA_CMD . "v-change-sys-service-config " . $new_conf . " dovecot-2 " . $v_restart, $output, $return_var);
                    check_return_code($return_var, $output);
                    unset($output);
                    unlink($new_conf);
                }

                // Update config3
                if ((empty($_SESSION['error_msg'])) && (!empty($_POST['v_config3']))) {
                    exec('mktemp', $mktemp_output, $return_var);
                    $new_conf = $mktemp_output[0];
                    $fp = fopen($new_conf, 'w');
                    fwrite($fp, str_replace("\r\n", "\n",  $_POST['v_config3']));
                    fclose($fp);
                    exec(VESTA_CMD . "v-change-sys-service-config " . $new_conf . " dovecot-3 " . $v_restart, $output, $return_var);
                    check_return_code($return_var, $output);
                    unset($output);
                    unlink($new_conf);
                }

                // Update config4
                if ((empty($_SESSION['error_msg'])) && (!empty($_POST['v_config4']))) {
                    exec('mktemp', $mktemp_output, $return_var);
                    $new_conf = $mktemp_output[0];
                    $fp = fopen($new_conf, 'w');
                    fwrite($fp, str_replace("\r\n", "\n",  $_POST['v_config4']));
                    fclose($fp);
                    exec(VESTA_CMD . "v-change-sys-service-config " . $new_conf . " dovecot-4 " . $v_restart, $output, $return_var);
                    check_return_code($return_var, $output);
                    unset($output);
                    unlink($new_conf);
                }

                // Update config5
                if ((empty($_SESSION['error_msg'])) && (!empty($_POST['v_config5']))) {
                    exec('mktemp', $mktemp_output, $return_var);
                    $new_conf = $mktemp_output[0];
                    $fp = fopen($new_conf, 'w');
                    fwrite($fp, str_replace("\r\n", "\n",  $_POST['v_config5']));
                    fclose($fp);
                    exec(VESTA_CMD . "v-change-sys-service-config " . $new_conf . " dovecot-5 " . $v_restart, $output, $return_var);
                    check_return_code($return_var, $output);
                    unset($output);
                    unlink($new_conf);
                }

                // Update config6
                if ((empty($_SESSION['error_msg'])) && (!empty($_POST['v_config6']))) {
                    exec('mktemp', $mktemp_output, $return_var);
                    $new_conf = $mktemp_output[0];
                    $fp = fopen($new_conf, 'w');
                    fwrite($fp, str_replace("\r\n", "\n",  $_POST['v_config6']));
                    fclose($fp);
                    exec(VESTA_CMD . "v-change-sys-service-config " . $new_conf . " dovecot-6 " . $v_restart, $output, $return_var);
                    check_return_code($return_var, $output);
                    unset($output);
                    unlink($new_conf);
                }

                // Update config7
                if ((empty($_SESSION['error_msg'])) && (!empty($_POST['v_config7']))) {
                    exec('mktemp', $mktemp_output, $return_var);
                    $new_conf = $mktemp_output[0];
                    $fp = fopen($new_conf, 'w');
                    fwrite($fp, str_replace("\r\n", "\n",  $_POST['v_config7']));
                    fclose($fp);
                    exec(VESTA_CMD . "v-change-sys-service-config " . $new_conf . " dovecot-7 " . $v_restart, $output, $return_var);
                    check_return_code($return_var, $output);
                    unset($output);
                    unlink($new_conf);
                }

                // Update config8
                if ((empty($_SESSION['error_msg'])) && (!empty($_POST['v_config8']))) {
                    exec('mktemp', $mktemp_output, $return_var);
                    $new_conf = $mktemp_output[0];
                    $fp = fopen($new_conf, 'w');
                    fwrite($fp, str_replace("\r\n", "\n",  $_POST['v_config8']));
                    fclose($fp);
                    exec(VESTA_CMD . "v-change-sys-service-config " . $new_conf . " dovecot-8 " . $v_restart, $output, $return_var);
                    check_return_code($return_var, $output);
                    unset($output);
                    unlink($new_conf);
                }

                // Set success message
                if (empty($_SESSION['error_msg'])) {
                    $_SESSION['ok_msg'] = __('Changes has been saved.');
                }
            }

            // List config
            exec(VESTA_CMD . "v-list-sys-dovecot-config json", $output, $return_var);
            $data = json_decode(implode('', $output), true);
            unset($output);

            $v_config_path = $data['CONFIG']['config_path'];
            $v_config_path1 = $data['CONFIG']['config_path1'];
            $v_config_path2 = $data['CONFIG']['config_path2'];
            $v_config_path3 = $data['CONFIG']['config_path3'];
            $v_config_path4 = $data['CONFIG']['config_path4'];
            $v_config_path5 = $data['CONFIG']['config_path5'];
            $v_config_path6 = $data['CONFIG']['config_path6'];
            $v_config_path7 = $data['CONFIG']['config_path7'];
            $v_config_path8 = $data['CONFIG']['config_path8'];
            $v_service_name = strtoupper('dovecot');

            // Read config
            $v_config = shell_exec(VESTA_CMD . "v-open-fs-config " . $v_config_path);
            if (!empty($v_config_path1)) $v_config1 = shell_exec(VESTA_CMD . "v-open-fs-config " . $v_config_path1);
            if (!empty($v_config_path2)) $v_config2 = shell_exec(VESTA_CMD . "v-open-fs-config " . $v_config_path2);
            if (!empty($v_config_path3)) $v_config3 = shell_exec(VESTA_CMD . "v-open-fs-config " . $v_config_path3);
            if (!empty($v_config_path4)) $v_config4 = shell_exec(VESTA_CMD . "v-open-fs-config " . $v_config_path4);
            if (!empty($v_config_path5)) $v_config5 = shell_exec(VESTA_CMD . "v-open-fs-config " . $v_config_path5);
            if (!empty($v_config_path6)) $v_config6 = shell_exec(VESTA_CMD . "v-open-fs-config " . $v_config_path6);
            if (!empty($v_config_path7)) $v_config7 = shell_exec(VESTA_CMD . "v-open-fs-config " . $v_config_path7);
            if (!empty($v_config_path8)) $v_config8 = shell_exec(VESTA_CMD . "v-open-fs-config " . $v_config_path8);

            // Render page
            render_page($user, $TAB, 'edit_server_dovecot');

            // Flush session messages
            unset($_SESSION['error_msg']);
            unset($_SESSION['ok_msg']);
        }

        if ($param_type == "exim") {
            error_reporting(NULL);
            $TAB = 'SERVER';

            // Main include
            include(APP_PATH . 'libs/inc/main.php');

            // Check user
            if ($_SESSION['user'] != 'admin') {
                header("Location: /list/user");
                exit;
            }

            // Check POST request
            if (!empty($_POST['save'])) {

                // Check token
                if ((!isset($_POST['token'])) || ($_SESSION['token'] != $_POST['token'])) {
                    header('location: /login/');
                    exit();
                }

                // Set restart flag
                $v_restart = 'yes';
                if (empty($_POST['v_restart'])) $v_restart = 'no';

                // Update config
                if (!empty($_POST['v_config'])) {
                    exec('mktemp', $mktemp_output, $return_var);
                    $new_conf = $mktemp_output[0];
                    $fp = fopen($new_conf, 'w');
                    fwrite($fp, str_replace("\r\n", "\n",  $_POST['v_config']));
                    fclose($fp);
                    exec(VESTA_CMD . "v-change-sys-service-config " . $new_conf . " exim " . $v_restart, $output, $return_var);
                    check_return_code($return_var, $output);
                    unset($output);
                    unlink($new_conf);
                }

                // Set success message
                if (empty($_SESSION['error_msg'])) {
                    $_SESSION['ok_msg'] = __('Changes has been saved.');
                }
            }

            $v_config_path = '/etc/exim/exim.conf';
            $v_service_name = strtoupper('exim');

            // Read config
            $v_config = shell_exec(VESTA_CMD . "v-open-fs-config " . $v_config_path);

            // Render page
            render_page($user, $TAB, 'edit_server_service');

            // Flush session messages
            unset($_SESSION['error_msg']);
            unset($_SESSION['ok_msg']);
        }

        if ($param_type == "exim4") {
            error_reporting(NULL);
            $TAB = 'SERVER';

            // Main include
            include(APP_PATH . 'libs/inc/main.php');

            // Check user
            if ($_SESSION['user'] != 'admin') {
                header("Location: /list/user");
                exit;
            }

            // Check POST request
            if (!empty($_POST['save'])) {

                // Check token
                if ((!isset($_POST['token'])) || ($_SESSION['token'] != $_POST['token'])) {
                    header('location: /login/');
                    exit();
                }

                // Set restart flag
                $v_restart = 'yes';
                if (empty($_POST['v_restart'])) $v_restart = 'no';

                // Update config
                if (!empty($_POST['v_config'])) {
                    exec('mktemp', $mktemp_output, $return_var);
                    $new_conf = $mktemp_output[0];
                    $fp = fopen($new_conf, 'w');
                    fwrite($fp, str_replace("\r\n", "\n",  $_POST['v_config']));
                    fclose($fp);
                    exec(VESTA_CMD . "v-change-sys-service-config " . $new_conf . " exim4 " . $v_restart, $output, $return_var);
                    check_return_code($return_var, $output);
                    unset($output);
                    unlink($new_conf);
                }

                // Set success message
                if (empty($_SESSION['error_msg'])) {
                    $_SESSION['ok_msg'] = __('Changes has been saved.');
                }
            }

            $v_config_path = '/etc/exim4/exim4.conf.template';
            $v_service_name = strtoupper('exim');

            // Read config
            $v_config = shell_exec(VESTA_CMD . "v-open-fs-config " . $v_config_path);

            // Render page
            render_page($user, $TAB, 'edit_server_service');

            // Flush session messages
            unset($_SESSION['error_msg']);
            unset($_SESSION['ok_msg']);
        }

        if ($param_type == "fail2ban") {
            error_reporting(NULL);
            $TAB = 'SERVER';

            // Main include
            include(APP_PATH . 'libs/inc/main.php');

            // Check user
            if ($_SESSION['user'] != 'admin') {
                header("Location: /list/user");
                exit;
            }

            // Check POST request
            if (!empty($_POST['save'])) {

                // Check token
                if ((!isset($_POST['token'])) || ($_SESSION['token'] != $_POST['token'])) {
                    header('location: /login/');
                    exit();
                }

                // Set restart flag
                $v_restart = 'yes';
                if (empty($_POST['v_restart'])) $v_restart = 'no';

                // Update config
                if (!empty($_POST['v_config'])) {
                    exec('mktemp', $mktemp_output, $return_var);
                    $new_conf = $mktemp_output[0];
                    $fp = fopen($new_conf, 'w');
                    fwrite($fp, str_replace("\r\n", "\n",  $_POST['v_config']));
                    fclose($fp);
                    exec(VESTA_CMD . "v-change-sys-service-config " . $new_conf . " fail2ban " . $v_restart, $output, $return_var);
                    check_return_code($return_var, $output);
                    unset($output);
                    unlink($new_conf);
                }

                // Set success message
                if (empty($_SESSION['error_msg'])) {
                    $_SESSION['ok_msg'] = __('Changes has been saved.');
                }
            }

            $v_config_path = '/etc/fail2ban/jail.local';
            $v_service_name = strtoupper('fail2ban');

            // Read config
            $v_config = shell_exec(VESTA_CMD . "v-open-fs-config " . $v_config_path);

            // Render page
            render_page($user, $TAB, 'edit_server_service');

            // Flush session messages
            unset($_SESSION['error_msg']);
            unset($_SESSION['ok_msg']);
        }

        if ($param_type == "httpd") {
            error_reporting(NULL);
            $TAB = 'SERVER';

            // Main include
            include(APP_PATH . 'libs/inc/main.php');

            // Check user
            if ($_SESSION['user'] != 'admin') {
                header("Location: /list/user");
                exit;
            }

            // Check POST request
            if (!empty($_POST['save'])) {

                // Check token
                if ((!isset($_POST['token'])) || ($_SESSION['token'] != $_POST['token'])) {
                    header('location: /login/');
                    exit();
                }

                // Set restart flag
                $v_restart = 'yes';
                if (empty($_POST['v_restart'])) $v_restart = 'no';

                // Update config
                if (!empty($_POST['v_config'])) {
                    exec('mktemp', $mktemp_output, $return_var);
                    $new_conf = $mktemp_output[0];
                    $fp = fopen($new_conf, 'w');
                    fwrite($fp, str_replace("\r\n", "\n",  $_POST['v_config']));
                    fclose($fp);
                    exec(VESTA_CMD . "v-change-sys-service-config " . $new_conf . " httpd " . $v_restart, $output, $return_var);
                    check_return_code($return_var, $output);
                    unset($output);
                    unlink($new_conf);
                }

                // Set success message
                if (empty($_SESSION['error_msg'])) {
                    $_SESSION['ok_msg'] = __('Changes has been saved.');
                }
            }

            $v_config_path = '/etc/httpd/conf/httpd.conf';
            $v_service_name = strtoupper('httpd');

            // Read config
            $v_config = shell_exec(VESTA_CMD . "v-open-fs-config " . $v_config_path);

            // Render page
            render_page($user, $TAB, 'edit_server_httpd');

            // Flush session messages
            unset($_SESSION['error_msg']);
            unset($_SESSION['ok_msg']);
        }

        if ($param_type == "iptables") {
            error_reporting(NULL);
            $TAB = 'SERVER';

            // Main include
            include(APP_PATH . 'libs/inc/main.php');

            // Check user
            header("Location: /firewall");
            exit;
        }

        if ($param_type == "mariadb") {
            error_reporting(NULL);
            $TAB = 'SERVER';

            // Main include
            include(APP_PATH . 'libs/inc/main.php');

            // Check user
            if ($_SESSION['user'] != 'admin') {
                header("Location: /list/user");
                exit;
            }

            // Check POST request
            if (!empty($_POST['save'])) {

                // Check token
                if ((!isset($_POST['token'])) || ($_SESSION['token'] != $_POST['token'])) {
                    header('location: /login/');
                    exit();
                }

                // Set restart flag
                $v_restart = 'yes';
                if (empty($_POST['v_restart'])) $v_restart = 'no';

                // Update config
                if (!empty($_POST['v_config'])) {
                    exec('mktemp', $mktemp_output, $return_var);
                    $new_conf = $mktemp_output[0];
                    $fp = fopen($new_conf, 'w');
                    fwrite($fp, str_replace("\r\n", "\n",  $_POST['v_config']));
                    fclose($fp);
                    exec(VESTA_CMD . "v-change-sys-service-config " . $new_conf . " mariadb " . $v_restart, $output, $return_var);
                    check_return_code($return_var, $output);
                    unset($output);
                    unlink($new_conf);
                }

                // Set success message
                if (empty($_SESSION['error_msg'])) {
                    $_SESSION['ok_msg'] = __('Changes has been saved.');
                }
            }

            // List config
            exec(VESTA_CMD . "v-list-sys-mysql-config json", $output, $return_var);
            $data = json_decode(implode('', $output), true);
            unset($output);
            $v_max_user_connections = $data['CONFIG']['max_user_connections'];
            $v_max_connections = $data['CONFIG']['max_connections'];
            $v_wait_timeout = $data['CONFIG']['wait_timeout'];
            $v_interactive_timeout = $data['CONFIG']['interactive_timeout'];
            $v_max_allowed_packet = $data['CONFIG']['max_allowed_packet'];
            $v_config_path = $data['CONFIG']['config_path'];
            $v_service_name = strtoupper('mariadb');

            # Read config
            $v_config = shell_exec(VESTA_CMD . "v-open-fs-config " . $v_config_path);

            // Render page
            render_page($user, $TAB, 'edit_server_mysql');

            // Flush session messages
            unset($_SESSION['error_msg']);
            unset($_SESSION['ok_msg']);
        }

        if ($param_type == "mysql") {
            error_reporting(NULL);
            $TAB = 'SERVER';

            // Main include
            include(APP_PATH . 'libs/inc/main.php');

            // Check user
            if ($_SESSION['user'] != 'admin') {
                header("Location: /list/user");
                exit;
            }

            // Check POST request
            if (!empty($_POST['save'])) {

                // Check token
                if ((!isset($_POST['token'])) || ($_SESSION['token'] != $_POST['token'])) {
                    header('location: /login/');
                    exit();
                }

                // Set restart flag
                $v_restart = 'yes';
                if (empty($_POST['v_restart'])) $v_restart = 'no';

                // Update config
                if (!empty($_POST['v_config'])) {
                    exec('mktemp', $mktemp_output, $return_var);
                    $new_conf = $mktemp_output[0];
                    $fp = fopen($new_conf, 'w');
                    fwrite($fp, str_replace("\r\n", "\n",  $_POST['v_config']));
                    fclose($fp);
                    exec(VESTA_CMD . "v-change-sys-service-config " . $new_conf . " mysql " . $v_restart, $output, $return_var);
                    check_return_code($return_var, $output);
                    unset($output);
                    unlink($new_conf);
                }

                // Set success message
                if (empty($_SESSION['error_msg'])) {
                    $_SESSION['ok_msg'] = __('Changes has been saved.');
                }
            }

            // List config
            exec(VESTA_CMD . "v-list-sys-mysql-config json", $output, $return_var);
            $data = json_decode(implode('', $output), true);
            unset($output);
            $v_max_user_connections = $data['CONFIG']['max_user_connections'];
            $v_max_connections = $data['CONFIG']['max_connections'];
            $v_wait_timeout = $data['CONFIG']['wait_timeout'];
            $v_interactive_timeout = $data['CONFIG']['interactive_timeout'];
            $v_max_allowed_packet = $data['CONFIG']['max_allowed_packet'];
            $v_config_path = $data['CONFIG']['config_path'];
            $v_service_name = strtoupper('mysql');

            # Read config
            $v_config = shell_exec(VESTA_CMD . "v-open-fs-config " . $v_config_path);

            // Render page
            render_page($user, $TAB, 'edit_server_mysql');

            // Flush session messages
            unset($_SESSION['error_msg']);
            unset($_SESSION['ok_msg']);
        }

        if ($param_type == "mysqld") {
            error_reporting(NULL);
            $TAB = 'SERVER';

            // Main include
            include(APP_PATH . 'libs/inc/main.php');

            // Check user
            if ($_SESSION['user'] != 'admin') {
                header("Location: /list/user");
                exit;
            }

            // Check POST request
            if (!empty($_POST['save'])) {

                // Check token
                if ((!isset($_POST['token'])) || ($_SESSION['token'] != $_POST['token'])) {
                    header('location: /login/');
                    exit();
                }

                // Set restart flag
                $v_restart = 'yes';
                if (empty($_POST['v_restart'])) $v_restart = 'no';

                // Update config
                if (!empty($_POST['v_config'])) {
                    exec('mktemp', $mktemp_output, $return_var);
                    $new_conf = $mktemp_output[0];
                    $fp = fopen($new_conf, 'w');
                    fwrite($fp, str_replace("\r\n", "\n",  $_POST['v_config']));
                    fclose($fp);
                    exec(VESTA_CMD . "v-change-sys-service-config " . $new_conf . " mysqld " . $v_restart, $output, $return_var);
                    check_return_code($return_var, $output);
                    unset($output);
                    unlink($new_conf);
                }

                // Set success message
                if (empty($_SESSION['error_msg'])) {
                    $_SESSION['ok_msg'] = __('Changes has been saved.');
                }
            }

            // List config
            exec(VESTA_CMD . "v-list-sys-mysql-config json", $output, $return_var);
            $data = json_decode(implode('', $output), true);
            unset($output);
            $v_max_user_connections = $data['CONFIG']['max_user_connections'];
            $v_max_connections = $data['CONFIG']['max_connections'];
            $v_wait_timeout = $data['CONFIG']['wait_timeout'];
            $v_interactive_timeout = $data['CONFIG']['interactive_timeout'];
            $v_max_allowed_packet = $data['CONFIG']['max_allowed_packet'];
            $v_config_path = $data['CONFIG']['config_path'];
            $v_service_name = strtoupper('mysql');

            # Read config
            $v_config = shell_exec(VESTA_CMD . "v-open-fs-config " . $v_config_path);

            // Render page
            render_page($user, $TAB, 'edit_server_mysql');

            // Flush session messages
            unset($_SESSION['error_msg']);
            unset($_SESSION['ok_msg']);
        }

        if ($param_type == "named") {
            error_reporting(NULL);
            $TAB = 'SERVER';

            // Main include
            include(APP_PATH . 'libs/inc/main.php');

            // Check user
            if ($_SESSION['user'] != 'admin') {
                header("Location: /list/user");
                exit;
            }

            // Check POST request
            if (!empty($_POST['save'])) {

                // Check token
                if ((!isset($_POST['token'])) || ($_SESSION['token'] != $_POST['token'])) {
                    header('location: /login/');
                    exit();
                }

                // Set restart flag
                $v_restart = 'yes';
                if (empty($_POST['v_restart'])) $v_restart = 'no';

                // Update config
                if (!empty($_POST['v_config'])) {
                    exec('mktemp', $mktemp_output, $return_var);
                    $new_conf = $mktemp_output[0];
                    $fp = fopen($new_conf, 'w');
                    fwrite($fp, str_replace("\r\n", "\n",  $_POST['v_config']));
                    fclose($fp);
                    exec(VESTA_CMD . "v-change-sys-service-config " . $new_conf . " named " . $v_restart, $output, $return_var);
                    check_return_code($return_var, $output);
                    unset($output);
                    unlink($new_conf);
                }

                // Set success message
                if (empty($_SESSION['error_msg'])) {
                    $_SESSION['ok_msg'] = __('Changes has been saved.');
                }
            }

            $v_config_path = '/etc/named.conf';
            $v_service_name = strtoupper('named');

            // Read config
            $v_config = shell_exec(VESTA_CMD . "v-open-fs-config " . $v_config_path);

            // Render page
            render_page($user, $TAB, 'edit_server_service');

            // Flush session messages
            unset($_SESSION['error_msg']);
            unset($_SESSION['ok_msg']);
        }

        if ($param_type == "nginx") {
            error_reporting(NULL);
            $TAB = 'SERVER';

            // Main include
            include(APP_PATH . 'libs/inc/main.php');

            // Check user
            if ($_SESSION['user'] != 'admin') {
                header("Location: /list/user");
                exit;
            }

            // Check POST request
            if (!empty($_POST['save'])) {

                // Check token
                if ((!isset($_POST['token'])) || ($_SESSION['token'] != $_POST['token'])) {
                    header('location: /login/');
                    exit();
                }

                // Set restart flag
                $v_restart = 'yes';
                if (empty($_POST['v_restart'])) $v_restart = 'no';

                // Update config
                if (!empty($_POST['v_config'])) {
                    exec('mktemp', $mktemp_output, $return_var);
                    $new_conf = $mktemp_output[0];
                    $fp = fopen($new_conf, 'w');
                    fwrite($fp, str_replace("\r\n", "\n",  $_POST['v_config']));
                    fclose($fp);
                    exec(VESTA_CMD . "v-change-sys-service-config " . $new_conf . " nginx " . $v_restart, $output, $return_var);
                    check_return_code($return_var, $output);
                    unset($output);
                    unlink($new_conf);
                }

                // Set success message
                if (empty($_SESSION['error_msg'])) {
                    $_SESSION['ok_msg'] = __('Changes has been saved.');
                }
            }

            // List config
            exec(VESTA_CMD . "v-list-sys-nginx-config json", $output, $return_var);
            $data = json_decode(implode('', $output), true);
            unset($output);
            $v_worker_processes = $data['CONFIG']['worker_processes'];
            $v_worker_connections = $data['CONFIG']['worker_connections'];
            $v_send_timeout = $data['CONFIG']['send_timeout'];
            $v_proxy_connect_timeout = $data['CONFIG']['proxy_connect_timeout'];
            $v_proxy_send_timeout = $data['CONFIG']['proxy_send_timeout'];
            $v_proxy_read_timeout = $data['CONFIG']['proxy_read_timeout'];
            $v_client_max_body_size = $data['CONFIG']['client_max_body_size'];
            $v_gzip = $data['CONFIG']['gzip'];
            $v_gzip_comp_level = $data['CONFIG']['gzip_comp_level'];
            $v_charset = $data['CONFIG']['charset'];
            $v_config_path = $data['CONFIG']['config_path'];
            $v_service_name = strtoupper('nginx');

            // Read config
            $v_config = shell_exec(VESTA_CMD . "v-open-fs-config " . $v_config_path);

            // Render page
            render_page($user, $TAB, 'edit_server_nginx');

            // Flush session messages
            unset($_SESSION['error_msg']);
            unset($_SESSION['ok_msg']);
        }

        if ($param_type == "php") {
            error_reporting(NULL);
            $TAB = 'SERVER';

            // Main include
            include(APP_PATH . 'libs/inc/main.php');

            // Check user
            if ($_SESSION['user'] != 'admin') {
                header("Location: /list/user");
                exit;
            }

            // Check POST request
            if (!empty($_POST['save'])) {

                // Check token
                if ((!isset($_POST['token'])) || ($_SESSION['token'] != $_POST['token'])) {
                    header('location: /login/');
                    exit();
                }

                // Set restart flag
                $v_restart = 'yes';
                if (empty($_POST['v_restart'])) $v_restart = 'no';

                // Update config
                if (!empty($_POST['v_config'])) {
                    exec('mktemp', $mktemp_output, $return_var);
                    $new_conf = $mktemp_output[0];
                    $fp = fopen($new_conf, 'w');
                    fwrite($fp, str_replace("\r\n", "\n",  $_POST['v_config']));
                    fclose($fp);
                    exec(VESTA_CMD . "v-change-sys-service-config " . $new_conf . " php " . $v_restart, $output, $return_var);
                    check_return_code($return_var, $output);
                    unset($output);
                    unlink($new_conf);
                }

                // Set success message
                if (empty($_SESSION['error_msg'])) {
                    $_SESSION['ok_msg'] = __('Changes has been saved.');
                }
            }

            // List config
            exec(VESTA_CMD . "v-list-sys-php-config json", $output, $return_var);
            $data = json_decode(implode('', $output), true);
            unset($output);
            $v_memory_limit = $data['CONFIG']['memory_limit'];
            $v_max_execution_time = $data['CONFIG']['max_execution_time'];
            $v_max_input_time = $data['CONFIG']['max_input_time'];
            $v_upload_max_filesize = $data['CONFIG']['upload_max_filesize'];
            $v_post_max_size = $data['CONFIG']['post_max_size'];
            $v_display_errors = $data['CONFIG']['display_errors'];
            $v_error_reporting = $data['CONFIG']['error_reporting'];
            $v_config_path = $data['CONFIG']['config_path'];

            # Read config
            $v_config = shell_exec(VESTA_CMD . "v-open-fs-config " . $v_config_path);

            // Render page
            render_page($user, $TAB, 'edit_server_php');

            // Flush session messages
            unset($_SESSION['error_msg']);
            unset($_SESSION['ok_msg']);
        }

        if ($param_type == "php-fpm") {
            error_reporting(NULL);
            $TAB = 'SERVER';

            // Main include
            include(APP_PATH . 'libs/inc/main.php');

            // Check user
            if ($_SESSION['user'] != 'admin') {
                header("Location: /list/user");
                exit;
            }

            // Check POST request
            if (!empty($_POST['save'])) {

                // Check token
                if ((!isset($_POST['token'])) || ($_SESSION['token'] != $_POST['token'])) {
                    header('location: /login/');
                    exit();
                }

                // Set restart flag
                $v_restart = 'yes';
                if (empty($_POST['v_restart'])) $v_restart = 'no';

                // Update config
                if (!empty($_POST['v_config'])) {
                    exec('mktemp', $mktemp_output, $return_var);
                    $new_conf = $mktemp_output[0];
                    $fp = fopen($new_conf, 'w');
                    fwrite($fp, str_replace("\r\n", "\n",  $_POST['v_config']));
                    fclose($fp);
                    exec(VESTA_CMD . "v-change-sys-service-config " . $new_conf . " php " . $v_restart, $output, $return_var);
                    check_return_code($return_var, $output);
                    unset($output);
                    unlink($new_conf);
                }

                // Set success message
                if (empty($_SESSION['error_msg'])) {
                    $_SESSION['ok_msg'] = __('Changes has been saved.');
                }
            }

            // List config
            exec(VESTA_CMD . "v-list-sys-php-config json", $output, $return_var);
            $data = json_decode(implode('', $output), true);
            unset($output);
            $v_memory_limit = $data['CONFIG']['memory_limit'];
            $v_max_execution_time = $data['CONFIG']['max_execution_time'];
            $v_max_input_time = $data['CONFIG']['max_input_time'];
            $v_upload_max_filesize = $data['CONFIG']['upload_max_filesize'];
            $v_post_max_size = $data['CONFIG']['post_max_size'];
            $v_display_errors = $data['CONFIG']['display_errors'];
            $v_error_reporting = $data['CONFIG']['error_reporting'];
            $v_config_path = $data['CONFIG']['config_path'];

            # Read config
            $v_config = shell_exec(VESTA_CMD . "v-open-fs-config " . $v_config_path);

            // Render page
            render_page($user, $TAB, 'edit_server_php');

            // Flush session messages
            unset($_SESSION['error_msg']);
            unset($_SESSION['ok_msg']);
        }

        if ($param_type == "php5-fpm") {
            error_reporting(NULL);
            $TAB = 'SERVER';

            // Main include
            include($_SERVER['DOCUMENT_ROOT'] . "/inc/main.php");

            // Check user
            if ($_SESSION['user'] != 'admin') {
                header("Location: /list/user");
                exit;
            }

            // Check POST request
            if (!empty($_POST['save'])) {

                // Check token
                if ((!isset($_POST['token'])) || ($_SESSION['token'] != $_POST['token'])) {
                    header('location: /login/');
                    exit();
                }

                // Set restart flag
                $v_restart = 'yes';
                if (empty($_POST['v_restart'])) $v_restart = 'no';

                // Update config
                if (!empty($_POST['v_config'])) {
                    exec('mktemp', $mktemp_output, $return_var);
                    $new_conf = $mktemp_output[0];
                    $fp = fopen($new_conf, 'w');
                    fwrite($fp, str_replace("\r\n", "\n",  $_POST['v_config']));
                    fclose($fp);
                    exec(VESTA_CMD . "v-change-sys-service-config " . $new_conf . " php " . $v_restart, $output, $return_var);
                    check_return_code($return_var, $output);
                    unset($output);
                    unlink($new_conf);
                }

                // Set success message
                if (empty($_SESSION['error_msg'])) {
                    $_SESSION['ok_msg'] = __('Changes has been saved.');
                }
            }

            // List config
            exec(VESTA_CMD . "v-list-sys-php-config json", $output, $return_var);
            $data = json_decode(implode('', $output), true);
            unset($output);
            $v_memory_limit = $data['CONFIG']['memory_limit'];
            $v_max_execution_time = $data['CONFIG']['max_execution_time'];
            $v_max_input_time = $data['CONFIG']['max_input_time'];
            $v_upload_max_filesize = $data['CONFIG']['upload_max_filesize'];
            $v_post_max_size = $data['CONFIG']['post_max_size'];
            $v_display_errors = $data['CONFIG']['display_errors'];
            $v_error_reporting = $data['CONFIG']['error_reporting'];
            $v_config_path = $data['CONFIG']['config_path'];

            # Read config
            $v_config = shell_exec(VESTA_CMD . "v-open-fs-config " . $v_config_path);

            // Render page
            render_page($user, $TAB, 'edit_server_php');

            // Flush session messages
            unset($_SESSION['error_msg']);
            unset($_SESSION['ok_msg']);
        }

        if ($param_type == "postgresql") {
            error_reporting(NULL);
            $TAB = 'SERVER';

            // Main include
            include(APP_PATH . 'libs/inc/main.php');

            // Check user
            if ($_SESSION['user'] != 'admin') {
                header("Location: /list/user");
                exit;
            }

            // Check POST request
            if (!empty($_POST['save'])) {

                // Check token
                if ((!isset($_POST['token'])) || ($_SESSION['token'] != $_POST['token'])) {
                    header('location: /login/');
                    exit();
                }

                // Set restart flag
                $v_restart = 'yes';
                if (empty($_POST['v_restart'])) $v_restart = 'no';

                // Update option
                if (!empty($_POST['v_options'])) {
                    exec('mktemp', $mktemp_output, $return_var);
                    $new_conf = $mktemp_output[0];
                    $fp = fopen($new_conf, 'w');
                    fwrite($fp, str_replace("\r\n", "\n",  $_POST['v_options']));
                    fclose($fp);
                    exec(VESTA_CMD . "v-change-sys-service-config " . $new_conf . " postgresql-hba " . $v_restart, $output, $return_var);
                    check_return_code($return_var, $output);
                    unset($output);
                    unlink($new_conf);
                }

                // Update config
                if ((empty($_SESSION['error_msg'])) && (!empty($_POST['v_config']))) {
                    exec('mktemp', $mktemp_output, $return_var);
                    $new_conf = $mktemp_output[0];
                    $fp = fopen($new_conf, 'w');
                    fwrite($fp, str_replace("\r\n", "\n",  $_POST['v_config']));
                    fclose($new_conf);
                    exec(VESTA_CMD . "v-change-sys-service-config " . $new_conf . " postgresql " . $v_restart, $output, $return_var);
                    check_return_code($return_var, $output);
                    unset($output);
                    unlink($new_conf);
                }

                // Set success message
                if (empty($_SESSION['error_msg'])) {
                    $_SESSION['ok_msg'] = __('Changes has been saved.');
                }
            }

            // List config
            exec(VESTA_CMD . "v-list-sys-pgsql-config json", $output, $return_var);
            $data = json_decode(implode('', $output), true);
            unset($output);

            $v_options_path = $data['CONFIG']['pg_hba_path'];
            $v_config_path = $data['CONFIG']['config_path'];
            $v_service_name = strtoupper('postgresql');

            // Read config
            $v_options = shell_exec(VESTA_CMD . "v-open-fs-config " . $v_options_path);
            $v_config = shell_exec(VESTA_CMD . "v-open-fs-config " . $v_config_path);

            // Render page
            render_page($user, $TAB, 'edit_server_pgsql');

            // Flush session messages
            unset($_SESSION['error_msg']);
            unset($_SESSION['ok_msg']);
        }

        if ($param_type == "proftpd") {
            error_reporting(NULL);
            $TAB = 'SERVER';

            // Main include
            include(APP_PATH . 'libs/inc/main.php');

            // Check user
            if ($_SESSION['user'] != 'admin') {
                header("Location: /list/user");
                exit;
            }

            // Check POST request
            if (!empty($_POST['save'])) {

                // Check token
                if ((!isset($_POST['token'])) || ($_SESSION['token'] != $_POST['token'])) {
                    header('location: /login/');
                    exit();
                }

                // Set restart flag
                $v_restart = 'yes';
                if (empty($_POST['v_restart'])) $v_restart = 'no';

                // Update config
                if (!empty($_POST['v_config'])) {
                    exec('mktemp', $mktemp_output, $return_var);
                    $new_conf = $mktemp_output[0];
                    $fp = fopen($new_conf, 'w');
                    fwrite($fp, str_replace("\r\n", "\n",  $_POST['v_config']));
                    fclose($fp);
                    exec(VESTA_CMD . "v-change-sys-service-config " . $new_conf . " proftpd " . $v_restart, $output, $return_var);
                    check_return_code($return_var, $output);
                    unset($output);
                    unlink($new_conf);
                }

                // Set success message
                if (empty($_SESSION['error_msg'])) {
                    $_SESSION['ok_msg'] = __('Changes has been saved.');
                }
            }

            $v_config_path = shell_exec(VESTA_CMD . 'v-list-sys-proftpd-config plain');
            $v_service_name = strtoupper('proftpd');

            // Read config
            $v_config = shell_exec(VESTA_CMD . "v-open-fs-config " . $v_config_path);

            // Render page
            render_page($user, $TAB, 'edit_server_service');

            // Flush session messages
            unset($_SESSION['error_msg']);
            unset($_SESSION['ok_msg']);
        }

        if ($param_type == "spamassassin") {
            error_reporting(NULL);
            $TAB = 'SERVER';

            // Main include
            include(APP_PATH . 'libs/inc/main.php');

            // Check user
            if ($_SESSION['user'] != 'admin') {
                header("Location: /list/user");
                exit;
            }

            // Check POST request
            if (!empty($_POST['save'])) {

                // Check token
                if ((!isset($_POST['token'])) || ($_SESSION['token'] != $_POST['token'])) {
                    header('location: /login/');
                    exit();
                }

                // Set restart flag
                $v_restart = 'yes';
                if (empty($_POST['v_restart'])) $v_restart = 'no';

                // Update config
                if (!empty($_POST['v_config'])) {
                    exec('mktemp', $mktemp_output, $return_var);
                    $new_conf = $mktemp_output[0];
                    $fp = fopen($new_conf, 'w');
                    fwrite($fp, str_replace("\r\n", "\n",  $_POST['v_config']));
                    fclose($fp);
                    exec(VESTA_CMD . "v-change-sys-service-config " . $new_conf . " spamassassin " . $v_restart, $output, $return_var);
                    check_return_code($return_var, $output);
                    unset($output);
                    unlink($new_conf);
                }

                // Set success message
                if (empty($_SESSION['error_msg'])) {
                    $_SESSION['ok_msg'] = __('Changes has been saved.');
                }
            }

            $v_config_path = shell_exec(VESTA_CMD . 'v-list-sys-spamd-config plain');
            $v_service_name = strtoupper('spamassassin');

            // Read config
            $v_config = shell_exec(VESTA_CMD . "v-open-fs-config " . $v_config_path);

            // Render page
            render_page($user, $TAB, 'edit_server_service');

            // Flush session messages
            unset($_SESSION['error_msg']);
            unset($_SESSION['ok_msg']);
        }

        if ($param_type == "spamd") {
            error_reporting(NULL);
            $TAB = 'SERVER';

            // Main include
            include(APP_PATH . 'libs/inc/main.php');

            // Check user
            if ($_SESSION['user'] != 'admin') {
                header("Location: /list/user");
                exit;
            }

            // Check POST request
            if (!empty($_POST['save'])) {

                // Check token
                if ((!isset($_POST['token'])) || ($_SESSION['token'] != $_POST['token'])) {
                    header('location: /login/');
                    exit();
                }

                // Set restart flag
                $v_restart = 'yes';
                if (empty($_POST['v_restart'])) $v_restart = 'no';

                // Update config
                if (!empty($_POST['v_config'])) {
                    exec('mktemp', $mktemp_output, $return_var);
                    $new_conf = $mktemp_output[0];
                    $fp = fopen($new_conf, 'w');
                    fwrite($fp, str_replace("\r\n", "\n",  $_POST['v_config']));
                    fclose($fp);
                    exec(VESTA_CMD . "v-change-sys-service-config " . $new_conf . " spamd " . $v_restart, $output, $return_var);
                    check_return_code($return_var, $output);
                    unset($output);
                    unlink($new_conf);
                }

                // Set success message
                if (empty($_SESSION['error_msg'])) {
                    $_SESSION['ok_msg'] = __('Changes has been saved.');
                }
            }

            $v_config_path = shell_exec(VESTA_CMD . 'v-list-sys-spamd-config plain');
            $v_service_name = strtoupper('spamassassin');

            // Read config
            $v_config = shell_exec(VESTA_CMD . "v-open-fs-config " . $v_config_path);

            // Render page
            render_page($user, $TAB, 'edit_server_service');

            // Flush session messages
            unset($_SESSION['error_msg']);
            unset($_SESSION['ok_msg']);
        }

        if ($param_type == "vsftpd") {
            error_reporting(NULL);
            $TAB = 'SERVER';

            // Main include
            include(APP_PATH . 'libs/inc/main.php');

            // Check user
            if ($_SESSION['user'] != 'admin') {
                header("Location: /list/user");
                exit;
            }

            // Check POST request
            if (!empty($_POST['save'])) {

                // Check token
                if ((!isset($_POST['token'])) || ($_SESSION['token'] != $_POST['token'])) {
                    header('location: /login/');
                    exit();
                }

                // Set restart flag
                $v_restart = 'yes';
                if (empty($_POST['v_restart'])) $v_restart = 'no';

                // Update config
                if (!empty($_POST['v_config'])) {
                    exec('mktemp', $mktemp_output, $return_var);
                    $new_conf = $mktemp_output[0];
                    $fp = fopen($new_conf, 'w');
                    fwrite($fp, str_replace("\r\n", "\n",  $_POST['v_config']));
                    fclose($fp);
                    exec(VESTA_CMD . "v-change-sys-service-config " . $new_conf . " vsftpd " . $v_restart, $output, $return_var);
                    check_return_code($return_var, $output);
                    unset($output);
                    unlink($new_conf);
                }

                // Set success message
                if (empty($_SESSION['error_msg'])) {
                    $_SESSION['ok_msg'] = __('Changes has been saved.');
                }
            }

            $v_config_path = shell_exec(VESTA_CMD . 'v-list-sys-vsftpd-config plain');
            $v_service_name = strtoupper('vsftpd');

            // Read config
            $v_config = shell_exec(VESTA_CMD . "v-open-fs-config " . $v_config_path);

            // Render page
            render_page($user, $TAB, 'edit_server_service');

            // Flush session messages
            unset($_SESSION['error_msg']);
            unset($_SESSION['ok_msg']);
        }
    }
}