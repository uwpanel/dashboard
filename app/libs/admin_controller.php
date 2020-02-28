<?php

/**
 * @see Controller nuevo controller
 */
require_once CORE_PATH . 'kumbia/controller.php';

/**
 * Driver to protect the legacy drivers
 * To start creating a security convention and modules
 *
 * All controllers inherit from this class at a higher level
 * therefore the methods defined here are available for
 * any controller
 *
 * @category Kumbia
 * @package Controller
 */
class AdminController extends Controller
{

    final protected function initialize()
    {
        //Auth code and permissions
        //It will be free, but we will add one by default shortly
        //Possibly an abstract class is created with what it should have by default
    }

    final protected function finalize()
    {
    }
}
