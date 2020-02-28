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
 * @package    Upload
 * @subpackage Adapters
 *
 * @copyright  Copyright (c) 2005 - 2019 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */

/**
 * Class to save uploaded file
 *
 * @category   Kumbia
 * @package    Upload
 * @subpackage Adapters
 */
class FileUpload extends Upload
{
    /**
     * Constructor
     *
     * @param string $name File name by POST method
     */
    public function __construct($name)
    {
        parent::__construct($name);

        // Path where the file will be saved
        $this->_path = dirname($_SERVER['SCRIPT_FILENAME']) . '/files/upload';
    }

    /**
     * Assign the path to the destination directory for the file
     *
     * @param string $path path to destination directory (Ex: / home / user / data)
     */
    public function setPath($path)
    {
        $this->_path = $path;
    }

    /**
     * Save the file to the server
     *
     * @param string $name name with which the file will be saved
     * @return boolean
     */
    protected function _saveFile($name)
    {
        return move_uploaded_file($_FILES[$this->_name]['tmp_name'], "$this->_path/$name");
    }

    /**
     * Validate the file before saving
     *
     * @return boolean
     */
    protected function _validates()
    {
        // Verify that it can be written to the directory
        if (!is_writable($this->_path)) {
            Flash::error('Error: cannot write to directory');
            return FALSE;
        }

        return parent::_validates();
    }

}
