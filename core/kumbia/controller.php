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
 * @package    Controller
 *
 * @copyright  Copyright (c) 2005 - 2019 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */

/**
 * Main class for Kumbia controlles
 *
 * @category   Kumbia
 * @package    Controller
 */
class Controller
{

    /**
     * Name of the current module
     *
     * @var string
     */
    public $module_name;
    /**
     * Current Controller Name
     *
     * @var string
     */
    public $controller_name;
    /**
     * Name of the current action
     *
     * @var string
     */
    public $action_name;
    /**
     * Action Parameters
     *
     * @var array
     */
    public $parameters;
    /**
     * Limit the correct amount of
     * parameters of an action
     *
     * @var bool
     */
    public $limit_params = true;
    /**
     * Name of the scaffold to use
     *
     * @var string
     */
    public $scaffold = '';

    /**
     * Data available to display
     * 
     * @var mixed
     */
    public $data;

    /**
     * Builder
     *
     * @param array $args
     */
    public function __construct($args)
    {
        /*module to which the controller belongs */
        $this->module_name = $args['module'];
        $this->controller_name = $args['controller'];
        $this->parameters = $args['parameters'];
        $this->action_name = $args['action'];
        View::select($args['action']);
        View::setPath($args['controller_path']);
    }

    /**
     *  BeforeFilter
     *
     * @return false|null
     */
    protected function before_filter()
    {
    }

    /**
     * AfterFilter
     *
     * @return false|void
     */
    protected function after_filter()
    {
    }

    /**
     * Initialize
     *
     * @return false|void
     */
    protected function initialize()
    {
    }

    /**
     * Finalize
     *
     * @return false|void
     */
    protected function finalize()
    {
    }

    /**
     * Run the callback filtr
     *
     * @param boolean $init start filters
     * @return false|void
     */
    final public function k_callback($init = false)
    {
        if ($init) {
            if ($this->initialize() !== false) {
                return $this->before_filter();
            }
            return false;
        }

        $this->after_filter();
        $this->finalize();
    }
}
