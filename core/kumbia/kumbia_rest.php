<?php

/**
 * KumbiaPHP web & app Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.
 *
 * @category   Kumbia
 *
 * @copyright  Copyright (c) 2005 - 2019 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */
require_once __DIR__ . '/controller.php';

/**
 * Controller to handle REST requests.
 *
 * By default each action is called as the method used by the client
 * (GET, POST, PUT, DELETE, OPTIONS, HEADERS, PURGE ...)
 * You can also add more actions by placing the method name in front
 * followed by the name of the put_cancel action, post_reset ...
 *
 * @category Kumbia
 *
 * @author kumbiaPHP Team
 */
class KumbiaRest extends Controller
{
    /**
     * Input format used to interpret the data
     * Sent by the client.
     *
     * @var string MIME Type of the format
     */
    protected $_fInput;

    /**
     * Define custom parser by MIME TYPE
     * This is necessary to interpret the entries
     * It is defined as a MIME type as a key and the value must be a
     * callback that returns the interpreted data.
     */
    protected $_inputType = array(
        'application/json' => array('RestController', 'parseJSON'),
        'application/xml' => array('RestController', 'parseXML'),
        'text/xml' => array('RestController', 'parseXML'),
        'text/csv' => array('RestController', 'parseCSV'),
        'application/x-www-form-urlencoded' => array('RestController', 'parseForm'),
    );

    /**
     * Output format sent to the client.
     *
     * @var string template name to use
     */
    protected $_fOutput;

    /**
     * It allows to define the available outputs,
     * this way you can present the same output in different
     * formats to customer requirements.
     */
    protected $_outputType = array(
        'application/json' => 'json',
        'application/xml' => 'xml',
        'text/xml' => 'xml',
        'text/csv' => 'csv',
    );

    /**
     * Builder
     *
     * @param array $arg
     */
    public function __construct($arg)
    {
        parent::__construct($arg);
        $this->initREST();
    }

    /**
     * Make the request router and send the corresponding parameters
     * To action, it also captures input and output formats.
     */
    protected function initREST()
    {
        /* input format */
        $this->_fInput = self::getInputFormat();
        $this->_fOutput = self::getOutputFormat($this->_outputType);
        View::select(null, $this->_fOutput);
        $this->rewriteActionName();
    }

    /**
     * Rewrite the action.
     */
    protected function rewriteActionName()
    {
        /**
         * we rewrite the action to be executed, now it will be the method of
         * the request: get (: id), getAll, put, post, delete, etc.
         */
        $action = $this->action_name;
        $method = strtolower(Router::get('method'));
        $rewrite = "{$method}_{$action}";
        if ($this->actionExist($rewrite)) {
            $this->action_name = $rewrite;

            return;
        }
        if ($rewrite === 'get_index') {
            $this->action_name = 'getAll';

            return;
        }
        $this->action_name = $method;
        $this->parameters = ($action === 'index') ? $this->parameters : [$action] + $this->parameters;
    }

    /**
     * Check if the $name action exists.
     *
     * @param string $name action name
     *
     * @return bool
     */
    protected function actionExist($name)
    {
        if (method_exists($this, $name)) {
            return (new ReflectionMethod($this, $name))->isPublic();
        }

        return false;
    }

    /**
     * Returns the request parameters the function of the input format
     * thereof. It makes use of the parser defined in the class.
     */
    protected function param()
    {
        $input = file_get_contents('php://input');
        $format = $this->_fInput;
        /* Check if the format has a valid parser */
        if (isset($this->_inputType[$format]) && is_callable($this->_inputType[$format])) {
            $result = call_user_func($this->_inputType[$format], $input);
            if ($result) {
                return $result;
            }
        }

        return $input;
    }

    /**
     * Send an error to the client along with the message.
     *
     * @param string $text  error text
     * @param int    $error HTTP error number
     *
     * @return array error data
     */
    protected function error($text, $error = 400)
    {
        http_response_code((int) $error);

        return array('error' => $text);
    }

    /**
     * Returns the formats accepted by the customer sorted by priority
     * interpreting the HTTP_ACCEPT header.
     *
     * @return array
     */
    protected static function accept()
    {
        /* to store the values accepted by the customer */
        $aTypes = array();
        /* Eliminate spaces, convert to lowercase, and separate */
        $accept = explode(',', strtolower(str_replace(' ', '', Input::server('HTTP_ACCEPT'))));
        foreach ($accept as $a) {
            $q = 1; /* By default the priority is 1, the next one checks if it is another */
            if (strpos($a, ';q=')) {
                /* part the "mime/type;q=X" in two: "mime/type" y "X" */
                list($a, $q) = explode(';q=', $a);
            }
            $aTypes[$a] = $q;
        }
        /* sort by priority (highest to lowest)*/
        arsort($aTypes);

        return $aTypes;
    }

    /**
     * Parse JSON
     * Convert JSON format into associative array.
     *
     * @param string $input
     *
     * @return array|string
     */
    protected static function parseJSON($input)
    {
        return json_decode($input, true);
    }

    /**
     * Parse XML.
     *
     * Convert XML format into an object, it will be necessary to make it standard
     * if associative objects or arrays are returned
     *
     * @param string $input
     *
     * @return \SimpleXMLElement|null
     */
    protected static function parseXML($input)
    {
        try {
            return new SimpleXMLElement($input);
        } catch (Exception $e) {
            // Do nothing
        }
    }

    /**
     * Parse CSV.
     *
     * Convert CSV into numeric arrays,
     * each item is a line
     *
     * @param string $input
     *
     * @return array
     */
    protected static function parseCSV($input)
    {
        $temp = fopen('php://memory', 'rw');
        fwrite($temp, $input);
        fseek($temp, 0);
        $res = array();
        while (($data = fgetcsv($temp)) !== false) {
            $res[] = $data;
        }
        fclose($temp);

        return $res;
    }

    /**
     * Make the form format conversion to array.
     *
     * @param string $input
     *
     * @return array
     */
    protected static function parseForm($input)
    {
        parse_str($input, $vars);

        return $vars;
    }

    /**
     * Returns the type of input format.
     *
     * @return string
     */
    protected static function getInputFormat()
    {
        if (isset($_SERVER['CONTENT_TYPE'])) {
            $str = explode(';', $_SERVER['CONTENT_TYPE']);
            return trim($str[0]);
        }

        return '';
    }

    /**
     * Returns the name of the output format.
     *
     * @param array $validOutput Array of supported output formats
     *
     * @return string
     */
    protected function getOutputFormat(array $validOutput)
    {
        /* I'm looking for a possible output format */
        $accept = self::accept();
        foreach ($accept as $key) {
            if (array_key_exists($key, $validOutput)) {
                return $validOutput[$key];
            }
        }

        return 'json';
    }

    /**
     * Returns all headers sent by the client.
     *
     * @return array
     */
    protected static function getHeaders()
    {
        return getallheaders();
    }
}
