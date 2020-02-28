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
 *
 * @copyright  Copyright (c) 2005 - 2019 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */

/**
 * Upload files to the server.
 *
 * @category   Kumbia
 * @package    Upload
 */
abstract class Upload
{

    /**
     * File name uploaded by POST method
     *
     * @var string
     */
    protected $_name;

    /**
     * Path where the file will be saved
     *
     * @var string
     */
    protected $_path;

    /**
     * Allow upload of executable script files
     *
     * @var boolean
     */
    protected $_allowScripts = FALSE;

    /**
     * Minimum file size
     *
     * @var string
     */
    protected $_minSize = '';

    /**
     * Maximum file size
     *
     * @var string
     */
    protected $_maxSize = '';

    /**
     * File types allowed using mime
     *
     * @var array
     */
    protected $_types = array();

    /**
     * File Extension Allowed
     *
     * @var array
     */
    protected $_extensions = array();

    /**
     * Allow to overwrite files
     *
     * @var bool Por defecto FALSE
     */
    protected $_overwrite = FALSE;

    /**
     * Builder
     *
     * @param string $name File name by POST method
     */
    public function __construct($name)
    {
        $this->_name = $name;
    }

    /**
     * Indicates whether to save executable script files
     *
     * @param boolean $value
     */
    public function setAllowScripts($value)
    {
        $this->_allowScripts = $value;
    }

    /**
     * Assign the minimum size allowed for the file
     *
     * @param string $size
     */
    public function setMinSize($size)
    {
        $this->_minSize = trim($size);
    }

    /**
     * Assign the maximum size allowed for the file
     *
     * @param string $size
     */
    public function setMaxSize($size)
    {
        $this->_maxSize = trim($size);
    }

    /**
     * Assign the types of files allowed (mime)
     *
     * @param array|string $value list of allowed file types (mime) if it is string separated by |
     */
    public function setTypes($value)
    {
        if (!is_array($value)) {
            $value = explode('|', $value);
        }

        $this->_types = $value;
    }

    /**
     * Assign the allowed file extensions
     *
     * @param array|string $value list of extensions for files, if it is string separated by |
     */
    public function setExtensions($value)
    {
        if (!is_array($value)) {
            $value = explode('|', $value);
        }

        $this->_extensions = $value;
    }

    /**
     * Allow to overwrite the file
     *
     * @param bool $value
     */
    public function overwrite($value)
    {
        $this->_overwrite = (bool) $value;
    }

    /**
     * Actions before saving
     *
     * @param string $name name with which the file is to be saved
     * @return  boolean|null
     */
    protected function _beforeSave($name)
    {
    }

    /**
     * Actions after saving
     *
     * @param string $name name with which the file was saved
     * @return  boolean|null
     */
    protected function _afterSave($name)
    {
    }

    /**
     * Save the uploaded file
     *
     * @param string $name name with which the file will be saved
     * @return boolean|string File name generated with the extension or FALSE if it fails
     */
    public function save($name = '')
    {
        if (!$this->isUploaded()) {
            return FALSE;
        }
        if (!$name) {
            $name = $_FILES[$this->_name]['name'];
        } else {
            $name = $name . $this->_getExtension();
        }

        // Save the file
        if ($this->_beforeSave($name) !== FALSE && $this->_overwrite($name) && $this->_validates() && $this->_saveFile($name)) {
            $this->_afterSave($name);
            return $name;
        }
        return FALSE;
    }

    /**
     * Save the file with a random name
     *
     * @return string|false File name generated or FALSE if it fails
     */
    public function saveRandom()
    {

        // Generate the file name
        $name = md5(time());

        // Save the file
        if ($this->save($name)) {
            return $name . $this->_getExtension();
        }

        return FALSE;
    }

    /**
     * Check if the file is uploaded to the server and ready to save
     *
     * @return boolean
     */
    public function isUploaded()
    {

        // Check if an error occurred while uploading
        if ($_FILES[$this->_name]['error'] > 0) {
            $error = array(UPLOAD_ERR_INI_SIZE => 'the file exceeds the maximum size (' . ini_get('upload_max_filesize') . 'b) allowed by server', UPLOAD_ERR_FORM_SIZE => 'the file exceeds the maximum allowed size', UPLOAD_ERR_PARTIAL => 'the file has been partially uploaded', UPLOAD_ERR_NO_FILE => 'no file has been uploaded', UPLOAD_ERR_NO_TMP_DIR => 'temporary files directory not found', UPLOAD_ERR_CANT_WRITE => 'failed to write file to disk', UPLOAD_ERR_EXTENSION => 'a php extension has stopped file upload');

            Flash::error('Error: ' . $error[$_FILES[$this->_name]['error']]);
            return FALSE;
        }
        return TRUE;
    }

    /**
     * Validate the file before saving
     *
     * @return boolean
     */
    protected function _validates()
    {
        $validations = array('allowScripts', 'types', 'extensions', 'maxSize', 'minSize');
        foreach ($validations as $value) {
            $func = "_{$value}";
            if ($this->$func && !$this->$func()) {
                return FALSE;
            }
        }
        return TRUE;
    }

    /**
     * Returns the extension
     *
     * @return string
     */
    protected function _getExtension()
    {
        if ($ext = pathinfo($_FILES[$this->_name]['name'], PATHINFO_EXTENSION)) {
            return '.' . $ext;
        }
    }

    /**
     * Valid if you can overwrite the file
     *
     * @return boolean
     */
    protected function _overwrite($name)
    {
        if ($this->_overwrite) {
            return TRUE;
        }
        if (file_exists("$this->_path/$name")) {
            Flash::error('Error: this file already exists. And it is not allowed to rewrite it');
            return FALSE;
        }
        return TRUE;
    }

    /**
     * Convert human readable size to bytes
     *
     * @param string $size
     * @return int
     */
    protected function _toBytes($size)
    {
        if (is_int($size) || ctype_digit($size)) {
            return (int) $size;
        }

        $tipo = strtolower(substr($size, -1));
        $size = (int) $size;

        switch ($tipo) {
            case 'g':

                //Gigabytes
                $size *= 1073741824;
                break;

            case 'm':

                //Megabytes
                $size *= 1048576;
                break;

            case 'k':

                //Kilobytes
                $size *= 1024;
                break;

            default:
                $size = -1;
                Flash::error('Error: the size must be an int for bytes, or a string ending with K, M or G. Ex: 30k, 2M, 2G');
        }

        return $size;
    }

    /**
     * Save the file to the server
     *
     * @param string $name name with which the file will be saved
     * @return boolean
     */
    protected abstract function _saveFile($name);

    /**
     * Get the adapter for Upload
     *
     * @param string $name filename received by POST
     * @param string $adapter (file, image, model)
     * @return Upload
     */
    public static function factory($name, $adapter = 'file')
    {
        require_once __DIR__ . "/adapters/{$adapter}_upload.php";
        $class = $adapter . 'upload';

        return new $class($name);
    }

    /**
     * @param boolean $cond
     */
    protected function _cond($cond, $message)
    {
        if ($cond) {
            Flash::error("Error: $message");
            return FALSE;
        }
        return TRUE;
    }

    protected function _allowScripts()
    {
        return $this->_cond(
            !$this->_allowScripts && preg_match('/\.(php|phtml|php3|php4|js|shtml|pl|py|rb|rhtml)$/i', $_FILES[$this->_name]['name']),
            'uploading executable scripts is not allowed'
        );
    }

    /**
     * Valid that the file type
     *
     * @return boolean
     */
    protected function _types()
    {
        return $this->_cond(
            !in_array($_FILES[$this->_name]['type'], $this->_types),
            'Invalid file type.'
        );
    }

    protected function _extensions()
    {
        return $this->_cond(
            !preg_match('/\.(' . implode('|', $this->_extensions) . ')$/i', $_FILES[$this->_name]['name']),
            'the file extension is not valid'
        );
    }

    protected function _maxSize()
    {
        return $this->_cond(
            $_FILES[$this->_name]['size'] > $this->_toBytes($this->_maxSize),
            "files larger than $this->_maxSize b"
        );
    }

    protected function _minSize()
    {
        return $this->_cond(
            $_FILES[$this->_name]['size'] < $this->_toBytes($this->_minSize),
            "Error: files smaller than $this->_minSize b"
        );
    }
}
