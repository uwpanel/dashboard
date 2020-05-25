<?php

class WeblogController extends AppController
{
    public function index($param_domain,$param_type = NULL)
    {
        $_SESSION['title'] = 'Web-logs';

        // Init
        error_reporting(NULL);

        include(APP_PATH . 'libs/inc/main.php');

        $v_domain = escapeshellarg($param_domain);

        if ($param_type == 'access') $type = 'access';
        if ($param_type == 'error') $type = 'error';

        exec(VESTA_CMD . "v-list-web-domain-" . $type . "log $user " . $v_domain, $output, $return_var);
        $this->data = $output;

        if ($return_var == 0) {
            // foreach ($output as $file) {
            //     echo htmlentities($file) . "\n";
            // }
        }
        echo "</pre>\n</body>\n</html>\n";
    }
}
