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

/**
 * Main class for exception handling.
 *
 * @category   Kumbia
 */
class KumbiaException extends Exception
{
    /**
     * Error View of the Exception.
     *
     * @var string|null
     */
    protected $view;

    /**
     * Error 404 for the following views.
     *
     * @var array
     */
    protected static $view404 = array('no_controller', 'no_action', 'num_params', 'no_view');

    /**
     * Exception template path.
     *
     * @var string
     */
    protected $template = 'views/templates/exception.phtml';

    /**
     * Class constructor;
     *
     * @param string $message message
     * @param string $view    view to be displayed
     */
    public function __construct($message, $view = 'exception')
    {
        $this->view = $view;
        parent::__construct($message);
    }

    /**
     * Handles uncaptured exceptions.
     *
     * @param Exception|KumbiaException $e
     * */
    public static function handleException($e)
    {
        self::setHeader($e);
        //EVERYTHING remove the extract, let the view ask for the ones you need
        extract(Router::get(), EXTR_OVERWRITE);
        // Register helper autoload
        spl_autoload_register('kumbia_autoload_helper', true, true);

        $Controller = Util::camelcase($controller);
        ob_start();
        if (PRODUCTION) { //EVERYTHING: add error 500.phtml
            self::cleanBuffer();
            include APP_PATH . 'views/_shared/errors/404.phtml';

            return;
        }
        if ($e instanceof self) {
            $view = $e->view;
            $tpl = $e->template;
        } else {
            $view = 'exception';
            $tpl = 'views/templates/exception.phtml';
        }
        //Fix problem with action name in REST
        $action = $e->getMessage() ?: $action;

        include CORE_PATH . "views/errors/{$view}.phtml";

        $content = ob_get_clean();
        self::cleanBuffer();
        include CORE_PATH . $tpl;
    }

    /**
     * cleanBuffer
     * Finish open buffers.
     */
    private static function cleanBuffer()
    {
        while (ob_get_level()) {
            ob_end_clean();
        }
    }

    /**
     * Add the http error header.
     *
     * @param Exception $e
     * */
    private static function setHeader($e)
    {
        if ($e instanceof self && in_array($e->view, self::$view404)) {
            http_response_code(404);

            return;
        }
        http_response_code(500);
        //EVERYTHING: move to views
    }
}
