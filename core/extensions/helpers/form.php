<?php

/**
 * KumbiaPHP web & app Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.
 *
 * @category   KumbiaPHP
 * @package    Helpers
 *
 * @copyright  Copyright (c) 2005 - 2019 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */
/**
 * Forms Helper.
 *
 * @category   KumbiaPHP
 */
class Form
{
    /**
     * Used to generate the radio button id,
     * carries an internal count.
     *
     * @var array
     */
    protected static $radios = array();

    /**
     * Used to notify the programmer, if using Form::file ()
     * and does not have the form mulipart shows an error.
     *
     * @var bool
     */
    protected static $multipart = false;

    /**
     * Get the value of a component taken
     * of the same value of the field name and form
     * corresponding to an attribute of the same name
     * that is a string, object or array.
     *
     * @param string $field
     * @param mixed  $value    field value
     * @param bool   $filter   filter html special characters
     * @param bool   $check    if the checkbox is marked
     * @param bool   $is_check
     *
     * @return array returns an array of length 3 with the form array (id, name, value)
     */
    public static function getField($field, $value = null, $is_check = false, $filter = true, $check = false)
    {
        // Gets considering the form.field format pattern
        $formField = explode('.', $field, 2);
        list($id, $name) = self::fieldName($formField);
        // Check in $ _POST
        if (Input::hasPost($field)) {
            $value = $is_check ?
                Input::post($field) == $value : Input::post($field);
        } elseif ($is_check) {
            $value = $check;
        } elseif ($tmp_val = self::getFromModel($formField)) {
            // Data autoload
            $value = $is_check ? $tmp_val == $value : $tmp_val;
        }
        // Filter special characters
        if (!$is_check && $value !== null && $filter) {
            $value = htmlspecialchars($value, ENT_COMPAT, APP_CHARSET);
        }
        // Return the data
        return array($id, $name, $value);
    }

    /**
     * Returns the value of the model.
     *
     * @param array $formField array [modelo, campo]
     *
     * @return mixed
     */
    protected static function getFromModel(array $formField)
    {
        $form = View::getVar($formField[0]);
        if (is_scalar($form) || is_null($form)) {
            return $form;
        }
        $form = (object) $form;

        return isset($form->{$formField[1]}) ? $form->{$formField[1]} : null;
    }

    /**
     * Returns the name and id of a field.
     *
     * @param array $field explode array
     *
     * @return array array(id, name)
     */
    protected static function fieldName(array $field)
    {
        return isset($field[1]) ?
            array("{$field[0]}_{$field[1]}", "{$field[0]}[{$field[1]}]") : array($field[0], $field[0]);
    }

    /**
     * Get the value of a component taken
     * of the same value of the field name and form
     * corresponding to an attribute of the same name
     * that is a string, object or array.
     *
     * @param string $field
     * @param mixed  $value  field value
     * @param bool   $filter filter html special characters
     *
     * @return array returns an array of length 3 with the form array (id, name, value)
     */
    public static function getFieldData($field, $value = null, $filter = true)
    {
        return self::getField($field, $value, false, $filter);
    }

    /**
     * Get the value of a check component taken
     * of the same value of the field name and form
     * corresponding to an attribute of the same name
     * that is a string, object or array.
     *
     * @param string $field
     * @param string $checkValue
     * @param bool   $checked
     *
     * @return array Returns an array of length 3 with the form array (id, name, checked);
     */
    public static function getFieldDataCheck($field, $checkValue, $checked = false)
    {
        return self::getField($field, $checkValue, true, false, $checked);
    }

    /**
     * @param string       $tag
     * @param string       $field
     * @param string       $value
     * @param string|array $attrs
     */
    protected static function tag($tag, $field, $attrs = '', $value = null, $extra = '', $close = true)
    {
        $attrs = Tag::getAttrs($attrs);
        $end = $close ? ">{{value}}</$tag>" : '/>';
        // Get name, id and value (only for autoload) for the field and load them into the scope
        list($id, $name, $value) = self::getFieldData($field, $value);

        return str_replace('{{value}}', $value, "<$tag id=\"$id\" name=\"$name\" $extra $attrs $end");
    }

    /*
     * Create an input field
     *
     * @param string|array $attrs Field Attributes (optional)
     * @param string $type
     * @param string $field
     * @param string $value
     * @return string
     */
    public static function input($type, $field, $attrs = '', $value = null)
    {
        return self::tag('input', $field, $attrs, $value, "type=\"$type\" value=\"{{value}}\"", false);
    }

    /**
     * Create a form tag.
     *
     * @param string $action Form action (optional)
     * @param string $method Default is post (optional)
     * @param string $attrs  Tag Attributes (optional)
     *
     * @return string
     */
    public static function open($action = '', $method = 'post', $attrs = '')
    {
        $attrs = Tag::getAttrs($attrs);
        if ($action) {
            $action = PUBLIC_PATH . $action;
        } else {
            $action = PUBLIC_PATH . ltrim(Router::get('route'), '/');
        }

        return "<form action=\"$action\" method=\"$method\" $attrs>";
    }

    /**
     * Create a multipart form tag.
     *
     * @param string       $action Form action (optional)
     * @param string|array $attrs  Tag Attributes (optional)
     *
     * @return string
     */
    public static function openMultipart($action = null, $attrs = '')
    {
        self::$multipart = true;
        if (is_array($attrs)) {
            $attrs['enctype'] = 'multipart/form-data';
            $attrs = Tag::getAttrs($attrs);
        } else {
            $attrs .= ' enctype="multipart/form-data"';
        }

        return self::open($action, 'post', $attrs);
    }

    /**
     * Create a tag to close a form.
     *
     * @return string
     */
    public static function close()
    {
        self::$multipart = false;

        return '</form>';
    }

    /**
     * Create a submit button for the current form.
     *
     * @param string       $text  Button text
     * @param string|array $attrs Field Attributes (optional)
     *
     * @return string
     */
    public static function submit($text, $attrs = '')
    {
        return self::button($text, $attrs, 'submit');
    }

    /**
     * Create a reset button.
     *
     * @param string       $text  Button text
     * @param string|array $attrs Field Attributes (optional)
     *
     * @return string
     */
    public static function reset($text, $attrs = '')
    {
        return self::button($text, $attrs, 'reset');
    }

    /**
     * Create a button
     *
     * @param string       $text  Button text
     * @param array|string $attrs Field Attributes (optional)
     * @param string       $type  button type
     * @param string       $value Value for the button
     *
     * @todo IT IS NONE TO ADD A NAME, BECAUSE WITHOUT THIS VALUE IT DOES NOT COME TO THE SERVER
     *
     * @return string
     */
    public static function button($text, $attrs = '', $type = 'button', $value = null)
    {
        $attrs = Tag::getAttrs($attrs);
        $value = is_null($value) ? '' : "value=\"$value\"";

        return "<button type=\"$type\" $value $attrs>$text</button>";
    }

    /**
     * Create a label
     *
     * @param string        $text  Text to display
     * @param string        $field Field referenced
     * @param string|array  $attrs Field Attributes (optional)
     *
     * @return string
     */
    public static function label($text, $field, $attrs = '')
    {
        $attrs = Tag::getAttrs($attrs);

        return "<label for=\"$field\" $attrs>$text</label>";
    }

    /**
     * Create a text field.
     *
     * @param string       $field Field name
     * @param string|array $attrs Field Attributes (optional)
     * @param string       $value (optional)
     *
     * @return string
     */
    public static function text($field, $attrs = '', $value = null)
    {
        return self::input('text', $field, $attrs, $value);
    }

    /**
     * Create a select field.
     *
     * @param string       $field  Field name
     * @param array        $data   Array of values ​​for the drop-down list
     * @param string|array $attrs  Field Attributes (optional)
     * @param string|array $value  Array for select multiple (optional)
     * @param string       $blank  add an empty item if it is different from empty
     * @param string       $itemId In case of using object array property to take as id
     * @param string       $show   text to display, if empty use the to string
     *
     * @return string
     */
    public static function select($field, $data, $attrs = '', $value = null, $blank = '', $itemId = 'id', $show = '')
    {
        $attrs = Tag::getAttrs($attrs);
        // Get name, id and value (only for autoload) for the field and load them into the scope
        list($id, $name, $value) = self::getFieldData($field, $value);
        //If you want to add blank
        $options = empty($blank) ? '' :
            '<option value="">' . htmlspecialchars($blank, ENT_COMPAT, APP_CHARSET) . '</option>';
        foreach ($data as $k => $v) {
            $val = self::selectValue($v, $k, $itemId);
            $text = self::selectShow($v, $show);
            $selected = self::selectedValue($value, $val);
            $options .= "<option value=\"$val\" $selected>$text</option>";
        }

        return "<select id=\"$id\" name=\"$name\" $attrs>$options</select>";
    }

    /**
     * Returns the value of an item from a select.
     *
     * @param mixed  $item array item
     * @param string $key  item value within the select
     * @param string $id   possible value of the property of the object for the value
     *
     * @return string
     */
    public static function selectValue($item, $key, $id)
    {
        return htmlspecialchars(
            is_object($item) ? $item->$id : $key,
            ENT_COMPAT,
            APP_CHARSET
        );
    }

    /**
     * returns the attribute so that the item of a
     * select.
     *
     * @param string|array $value value (s) that must be selected
     * @param string       $key   current item value
     *
     * @return string
     */
    public static function selectedValue($value, $key)
    {
        return ((is_array($value) && in_array($key, $value)) || $key === $value) ?
            'selected="selected"' : '';
    }

    /**
     * Returns the value to display of the select item.
     *
     * @param mixed  $item array item
     * @param string $show property the object
     *
     * @return string
     */
    public static function selectShow($item, $show)
    {
        $value = (is_object($item) && !empty($show)) ? $item->$show : (string) $item;

        return htmlspecialchars($value, ENT_COMPAT, APP_CHARSET);
    }

    /**
     * Create a checkbox field.
     *
     * @param string       $field      Field name
     * @param string       $checkValue Value in the checkbox
     * @param string|array $attrs      Field Attributes (optional)
     * @param bool         $checked    Indicates whether the field is marked (optional)
     *
     * @return string
     */
    public static function check($field, $checkValue, $attrs = '', $checked = false)
    {
        $attrs = Tag::getAttrs($attrs);
        // Get name and id for the field and load them into the scope
        list($id, $name, $checked) = self::getFieldDataCheck($field, $checkValue, $checked);

        if ($checked) {
            $checked = 'checked="checked"';
        }

        return "<input id=\"$id\" name=\"$name\" type=\"checkbox\" value=\"$checkValue\" $attrs $checked/>";
    }

    /**
     * Create a radio button field.
     *
     * @param string       $field      Field name
     * @param string       $radioValue Value in the radius
     * @param string|array $attrs      Field Attributes (optional)
     * @param bool         $checked    Indicates whether the field is marked (optional)
     *
     * @return string
     */
    public static function radio($field, $radioValue, $attrs = '', $checked = false)
    {
        $attrs = Tag::getAttrs($attrs);
        // Get name and id for the field and load them into the scope
        list($id, $name, $checked) = self::getFieldDataCheck($field, $radioValue, $checked);

        if ($checked) {
            $checked = 'checked="checked"';
        }

        // radio field counter
        if (isset(self::$radios[$field])) {
            ++self::$radios[$field];
        } else {
            self::$radios[$field] = 0;
        }
        $id .= self::$radios[$field];

        return "<input id=\"$id\" name=\"$name\" type=\"radio\" value=\"$radioValue\" $attrs $checked/>";
    }

    /**
     * Create an image type button.
     *
     * @param string       $img   Name or path of the image
     * @param string|array $attrs Field Attributes (optional)
     *
     * @return string
     */
    public static function submitImage($img, $attrs = '')
    {
        $attrs = Tag::getAttrs($attrs);

        return '<input type="image" src="' . PUBLIC_PATH . "img/$img\" $attrs/>";
    }

    /**
     * Create a hidden field.
     *
     * @param string       $field Field name
     * @param string|array $attrs Field Attributes (optional)
     * @param string       $value
     *
     * @return string
     */
    public static function hidden($field, $attrs = '', $value = null)
    {
        return self::input('hidden', $field, $attrs, $value);
    }

    /**
     * Create a password field.
     *
     * @deprecated Obsolete since version 1.0, use password
     *
     * @param string       $field Field name
     * @param string|array $attrs Field Attributes (optional)
     * @param string       $value
     */
    public static function pass($field, $attrs = '', $value = null)
    {
        return self::password($field, $attrs, $value);
    }

    /**
     * Create a passwordop field.
     *
     * @param string       $field Field name
     * @param string|array $attrs Field Attributes (optional)
     * @param string       $value
     */
    public static function password($field, $attrs = '', $value = null)
    {
        return self::input('password', $field, $attrs, $value);
    }

    /**
     * Create a select field that takes the values ​​of an array of objects.
     *
     * @param string       $field Field name
     * @param string       $show  Field to be displayed (optional)
     * @param array        $data  Array ('model', 'method', 'param') (optional)
     * @param string       $blank Blank field (optional)
     * @param string|array $attrs Field Attributes (optional)
     * @param string|array $value (optional) Array in select multiple
     *
     * @return string
     */
    public static function dbSelect($field, $show = null, $data = null, $blank = 'Select', $attrs = '', $value = null)
    {
        $model = ($data === null) ? substr($field, strpos($field, '.') + 1, -3) : $data[0];
        $model = Util::camelcase($model);
        $model_asoc = new $model();
        //by default the first field not pk
        $show = $show ?: $model_asoc->non_primary[0];
        $pk = $model_asoc->primary_key[0];
        if ($data === null) {
            $data = $model_asoc->find("columns: $pk,$show", "order: $show asc"); //better use array
        } else {
            $data = (isset($data[2])) ?
                $model_asoc->{$data[1]}($data[2]) :
                $model_asoc->{$data[1]}();
        }

        return self::select($field, $data, $attrs, $value, $blank, $pk, $show);
    }

    /**
     * Create a file field.
     *
     * @param string       $field Field name
     * @param string|array $attrs Field Attributes (optional)
     *
     * @return string
     */
    public static function file($field, $attrs = '')
    {
        // programmer notice
        if (!self::$multipart) {
            Flash::error('To upload files, you must open the form with Form::openMultipart()');
        }

        $attrs = Tag::getAttrs($attrs);

        // Get name and id, and load them into scope
        list($id, $name) = self::getFieldData($field, false);

        return "<input id=\"$id\" name=\"$name\" type=\"file\" $attrs/>";
    }

    /**
     * Create a textarea field.
     *
     * @param string       $field Field name
     * @param string|array $attrs Field Attributes (optional)
     * @param string       $value (optional)
     *
     * @return string
     */
    public static function textarea($field, $attrs = '', $value = null)
    {
        return self::tag('textarea', $field, $attrs, $value);
    }

    /**
     * Create a native date field (HTML5).
     *
     * @param string       $field Field name
     * @param string|array $attrs Field Attributes (optional)
     * @param string       $value (optional)
     *
     * @return string
     */
    public static function date($field, $attrs = '', $value = null)
    {
        return self::input('date', $field, $attrs, $value);
    }

    /**
     * Create a text field for date (Requires JS).
     *
     * @param string       $field Field name
     * @param string       $class Style class (optional)
     * @param string|array $attrs Field Attributes (optional)
     * @param string       $value (optional)
     *
     * @return string
     */
    public static function datepicker($field, $class = '', $attrs = '', $value = null)
    {
        return self::tag('input', $field, $attrs, null, "class=\"js-datepicker $class\" type=\"text\" value=\"$value\" ");
    }

    /**
     * Create a native time field (HTML5).
     *
     * @param string       $field Field name
     * @param string|array $attrs Field Attributes (optional)
     * @param string       $value (optional)
     *
     * @return string
     */
    public static function time($field, $attrs = '', $value = null)
    {
        return self::input('time', $field, $attrs, $value);
    }

    /**
     * Create a native date / time (HTML5) field.
     *
     * @param string       $field Field name
     * @param string|array $attrs Field Attributes (optional)
     * @param string       $value (optional)
     *
     * @return string
     */
    public static function datetime($field, $attrs = '', $value = null)
    {
        return self::input('datetime', $field, $attrs, $value);
    }

    /**
     * Create a native numeric field (HTML5).
     *
     * @param string       $field Field name
     * @param string|array $attrs Field Attributes (optional)
     * @param string       $value (optional)
     *
     * @return string
     */
    public static function number($field, $attrs = '', $value = null)
    {
        return self::input('number', $field, $attrs, $value);
    }

    /**
     * Create a native url field (HTML5).
     *
     * @param string       $field Field name
     * @param string|array $attrs Field Attributes (optional)
     * @param string       $value (optional)
     *
     * @return string
     */
    public static function url($field, $attrs = '', $value = null)
    {
        return self::input('url', $field, $attrs, $value);
    }

    /**
     * Create a native email field (HTML5).
     *
     * @param string       $field Field name
     * @param string|array $attrs Field Attributes (optional)
     * @param string       $value (optional)
     *
     * @return string
     */
    public static function email($field, $attrs = '', $value = null)
    {
        return self::input('email', $field, $attrs, $value);
    }
}
