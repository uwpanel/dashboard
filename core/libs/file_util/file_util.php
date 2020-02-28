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
 * @package    Core
 *
 * @copyright  Copyright (c) 2005 - 2019 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */

/**
 * Utilities for handling files and directories
 * @category   Kumbia
 * @package    Core
 */
class FileUtil
{
    /**
     * Create a path if it does not exist
     *
     * @param string $path route to create
     * @todo It must be optimized
     * @return boolean
     */
    public static function mkdir($path)
    {
        if (file_exists($path) || @mkdir($path))
            return TRUE;
        return (self::mkdir(dirname($path)) && mkdir($path));
    }

    /**
     * Delete a directory.
     *
     * @param string $dir directory path to delete
     * @todo It must be optimized
     * @return boolean
     */
    public static function rmdir($dir)
    {
        // I get the files in the directory to delete
        if ($files = array_merge(glob("$dir/*"), glob("$dir/.*"))) {
            // I delete each subdirectory or file
            foreach ($files as $file) {
                //  If not the directories "." or ".."
                if (!preg_match("/^.*\/?[\.]{1,2}$/", $file)) {
                    if (is_dir($file)) {
                        return self::rmdir($file);
                    } elseif (!@unlink($file)) {
                        return FALSE;
                    }
                }
            }
        }
        return @rmdir($dir);
    }
}
