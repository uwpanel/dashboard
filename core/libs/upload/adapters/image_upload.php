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
 * Class to save uploaded image
 *
 * @category   Kumbia
 * @package    Upload
 * @subpackage Adapters
 */
class ImageUpload extends Upload
{
    /**
     * Image Information
     *
     * @var array|boolean
     */
    protected $_imgInfo;
    /**
     * Minimum image width
     *
     * @var int
     */
    protected $_minWidth = NULL;
    /**
     * Image width
     *
     * @var int
     */
    protected $_maxWidth = NULL;
    /**
     * Minimum image height
     *
     * @var int
     */
    protected $_minHeight = NULL;
    /**
     * Image height
     *
     * @var int
     */
    protected $_maxHeight = NULL;

    /**
     * Builder
     *
     * @param string $name File name by POST method
     */
    public function __construct($name)
    {
        parent::__construct($name);

        $this->_imgInfo = getimagesize($_FILES[$name]['tmp_name']);

        // Path where the file will be saved
        $this->_path = dirname($_SERVER['SCRIPT_FILENAME']) . '/img/upload';
    }

    /**
     * Assign the path to the destination directory for the image
     *
     * @param string $path path to destination directory (Ex: / home / user / data)
     */
    public function setPath($path)
    {
        $this->_path = $path;
    }

    /**
     * Assign the minimum image width
     *
     * @param int $value
     */
    public function setMinWidth($value)
    {
        $this->_minWidth = $value;
    }

    /**
     * Assign the maximum width of the image
     *
     * @param int $value
     */
    public function setMaxWidth($value)
    {
        $this->_maxWidth = $value;
    }

    /**
     * Assign the minimum image height
     *
     * @param int $value
     */
    public function setMinHeight($value)
    {
        $this->_minHeight = $value;
    }

    /**
     * Assign the maximum height of the image
     *
     * @param int $value
     */
    public function setMaxHeight($value)
    {
        $this->_maxHeight = $value;
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


        $image = $this->_imgInfo;
        // Verify that it is an image file
        if (!$image){
            Flash::error('Error: the file must be an image');
            return FALSE;
        }

        // Verify minimum image width
        if ($this->_minWidth !== NULL) {
            if ($image[0] < $this->_minWidth) {
                Flash::error("Error: the width of the image must be greater than or equal to {$this->_minWidth}px");
                return FALSE;
            }
        }

        // Verify maximum image width
        if ($this->_maxWidth !== NULL) {
            if ($image[0] > $this->_maxWidth) {
                Flash::error("Error: the width of the image must be less than or equal to {$this->_maxWidth}px");
                return FALSE;
            }
        }

        // Verify high minimum of the image
        if ($this->_minHeight !== NULL) {
            if ($image[1] < $this->_minHeight) {
                Flash::error("Error: the height of the image must be greater than or equal to {$this->_minHeight}px");
                return FALSE;
            }
        }

        // Verify maximum image height
        if ($this->_maxHeight !== NULL) {
            if ($image[1] > $this->_maxHeight) {
                Flash::error("Error: the height of the image must be less than or equal to {$this->_maxHeight}px");
                return FALSE;
            }
        }

        // Validations
        return parent::_validates();
    }

    /**
     * Valid that the file type
     *
     * @return boolean
     */
    protected function _validatesTypes()
    {
        // Verify that it is an image file
        if (!$this->_imgInfo) return FALSE;

        foreach ($this->_types as $type) {
            if ($this->_imgInfo['mime'] == "image/$type") return TRUE;
        }

        return FALSE;
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

}
