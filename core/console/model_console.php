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
 * Console to handle models.
 *
 * @category   Kumbia
 * @package    Console
 */
class ModelConsole
{
    /**
     * Console command to create a model.
     *
     * @param array  $params named parameters of the console
     * @param string $model  model
     * @throw KumbiaException
     */
    public function create($params, $model)
    {
        //filename
        $file = APP_PATH . 'models';

        //get the path
        $path = explode('/', trim($model, '/'));

        // get the model name
        $model_name = array_pop($path);

        if (count($path)) {
            $dir = implode('/', $path);
            $file .= "/$dir";
            if (!is_dir($file) && !FileUtil::mkdir($file)) {
                throw new KumbiaException("The directory could not be created \"$file\"");
            }
        }
        $file .= "/$model_name.php";

        // if it does not exist or is overwritten
        if (
            !is_file($file) ||
            Console::input('The model exists, do you want to overwrite it? (s / n): ', array('s', 'n')) == 's'
        ) {
            //class name
            $class = Util::camelcase($model_name);

            // model code
            ob_start();
            include __DIR__ . '/generators/model.php';
            $code = '<?php' . PHP_EOL . ob_get_clean();

            // generate the file
            if (file_put_contents($file, $code)) {
                echo "-> Created model $model_name in: $file" . PHP_EOL;
            } else {
                throw new KumbiaException("Could not create file \"$file\"");
            }
        }
    }

    /**
     * Console command to delete a model.
     *
     * @param array  $params named parameters of the console
     * @param string $model  model
     * @throw KumbiaException
     */
    public function delete($params, $model)
    {
        // filename
        $file = APP_PATH . 'models/' . trim($model, '/');

        // if it is a directory
        if (is_dir($file)) {
            $success = FileUtil::rmdir($file);
        } else {
            // then it is a file
            $file = "$file.php";
            $success = unlink($file);
        }

        // message
        if ($success) {
            echo "-> Removed: $file" . PHP_EOL;
        } else {
            throw new KumbiaException("It has not been possible to eliminate \"$file\"");
        }
    }
}
