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
 * Selective charger.
 *
 * Class for loading libraries both core and app.
 * Loading models of an app.
 *
 * @category   Kumbia
 */
class Load
{
    /**
     * APP library load, if there is no CORE load.
     *
     * @param string $lib library to load
     * @throw KumbiaException
     */
    public static function lib($lib)
    {
        $file = APP_PATH . "libs/$lib.php";
        if (is_file($file)) {
            return include $file;
        }

        return self::coreLib($lib);
    }

    /**
     * Core library load.
     *
     * @param string $lib library to load
     * @throw KumbiaException
     */
    public static function coreLib($lib)
    {
        if (!include CORE_PATH . "libs/$lib/$lib.php") {
            throw new KumbiaException("Library: \"$lib\" Not found");
        }
    }

    /**
     * Gets the instance of a model.
     *
     * @param string $model  model to instantiate in small_case
     * @param array  $params parameters to instantiate the model
     *
     * @return obj model
     */
    public static function model($model, array $params = array())
    {
        //Class name
        $Model = Util::camelcase(basename($model));
        //If the class is not loaded
        if (!class_exists($Model, false)) {
            //Load class
            if (!include APP_PATH . "models/$model.php") {
                throw new KumbiaException($model, 'no_model');
            }
        }

        return new $Model($params);
    }

    /**
     * Load models
     *
     * @param string $model in small_case
     * @throw KumbiaException
     */
    public static function models($model)
    {
        $args = is_array($model) ? $model : func_get_args();
        foreach ($args as $model) {
            $Model = Util::camelcase(basename($model));
            //If it is loaded continue with the next class
            if (class_exists($Model, false)) {
                continue;
            }
            if (!include APP_PATH . "models/$model.php") {
                throw new KumbiaException($model, 'no_model');
            }
        }
    }
}
