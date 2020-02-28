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
 * @package    Console
 *
 * @copyright  Copyright (c) 2005 - 2019 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */

/**
 * Console to handle controllers
 *
 * @category   Kumbia
 * @package    Console
 */
class ControllerConsole
{

    /**
     * Console command to create a controller
     *
     * @param array $params named parameters of the console
     * @param string $controller controller
     * @throw KumbiaException
     */
    public function create($params, $controller)
    {
        // filename
        $file = APP_PATH . 'controllers';

        // clean the controller path
        $clean_path = trim($controller, '/');

        // get the path
        $path = explode('/', $clean_path);

        // get the driver name
        $controller_name = array_pop($path);

        // if the controller is grouped in a directory
        if (count($path)) {
            $dir = implode('/', $path);
            $file .= "/$dir";
            if (!is_dir($file) && !FileUtil::mkdir($file)) {
                throw new KumbiaException("The directory could not be created \"$file\"");
            }
        }
        $file .= "/{$controller_name}_controller.php";

        // if it does not exist or is overwritten
        if (
            !is_file($file) ||
            Console::input("The controller exists, do you want to overwrite it? (s / n):", array('s', 'n')) == 's'
        ) {

            // class name
            $class = Util::camelcase($controller_name);

            // controller code
            ob_start();
            include __DIR__ . '/generators/controller.php';
            $code = '<?php' . PHP_EOL . ob_get_clean();

            // generate the file
            if (file_put_contents($file, $code)) {
                echo "-> Created driver $controller_name in: $file" . PHP_EOL;
            } else {
                throw new KumbiaException("Could not create file \"$file\"");
            }

            // directory for views
            $views_dir = APP_PATH . "views/$clean_path";

            //if the directory does not exist
            if (!is_dir($views_dir)) {
                if (FileUtil::mkdir($views_dir)) {
                    echo "-> Created directory for views:$views_dir" . PHP_EOL;
                } else {
                    throw new KumbiaException("The directory could not be created \"$views_dir\"");
                }
            }
        }
    }

    /**
     * Console command to remove a controller
     *
     * @param array $params named parameters of the console
     * @param string $controller ccontroller
     * @throw KumbiaException
     */
    public function delete($params, $controller)
    {
        // clean path to controller
        $clean_path = trim($controller, '/');

        // filename
        $file = APP_PATH . "controllers/$clean_path";

        // if it is a directory
        if (is_dir($file)) {
            $success = FileUtil::rmdir($file);
        } else {
            // then it is a file
            $file = "{$file}_controller.php";
            $success = unlink($file);
        }

        // message
        if ($success) {
            echo "-> Removed: $file" . PHP_EOL;
        } else {
            throw new KumbiaException("It has not been possible to eliminate \"$file\"");
        }

        // directory for views
        $views_dir = APP_PATH . "views/$clean_path";

        // try deleting the views directory
        if (
            is_dir($views_dir)
            && Console::input('Do you want to delete the views directory? (s / n): ', array('s', 'n')) == 's'
        ) {

            if (!FileUtil::rmdir($views_dir)) {
                throw new KumbiaException("It has not been possible to eliminate \"$views_dir\"");
            }

            echo "-> Removed: $views_dir" . PHP_EOL;
        }
    }
}
