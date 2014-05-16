<?php namespace Mosaicpro\WpCore;

use Mosaicpro\Core\IoC;

/**
 * Class FormBuilder
 * @package Mosaicpro\WpCore
 */
class FormBuilder
{
    /**
     * Holds the Form Builder component dependency
     * @var mixed
     */
    protected static $form;

    /**
     * Create a new FormBuilder instance
     * @param $name
     * @param $args
     */
    public function __construct($name = null, $args = null)
    {
        self::$form = IoC::getContainer('form');
        if (!is_null($name)) return call_user_func_array([$this, $name], $args);
    }

    /**
     * Create a new FormBuilder instance statically
     * @param $name
     * @param $args
     * @return static
     */
    public static function __callStatic($name, $args)
    {
        return new static($name, $args);
    }

    /**
     * Echo out a radio input field
     * @param $name
     * @param $label
     * @param $value
     * @param null $checked
     * @param array $attributes
     */
    private function radio_single($name, $label, $value, $checked = null, array $attributes = [])
    {
        echo $this->get_radio_single($name, $label, $value, $checked, $attributes);
    }

    /**
     * Create a radio input field
     * @param $name
     * @param $label
     * @param $value
     * @param null $checked
     * @param array $attributes
     * @return string
     */
    public function get_radio_single($name, $label, $value, $checked = null, array $attributes = [])
    {
        $output = [];
        $output[] = '<div class="radio"><label>';

        if ($checked) $checked = ['checked'];
        if (is_null($checked)) $checked = $value ? ['checked'] : [];
        if (!$checked) $checked = [];

        $attributes = array_merge($checked, $attributes);
        $output[] = self::$form->radio($name, $value, null, $attributes) . $label;

        $output[] = '</label></div>';

        return implode(PHP_EOL, $output);
    }

    /**
     * Echo out a radio input field group
     * @param $name
     * @param $label
     * @param $value
     * @param $values
     * @param array $attributes
     */
    private function radio($name, $label, $value, $values, array $attributes = [])
    {
        echo $this->get_radio($name, $label, $value, $values, $attributes);
    }

    /**
     * Create a radio input field group
     * @param $name
     * @param $label
     * @param $value
     * @param $values
     * @param array $attributes
     * @return string
     */
    public function get_radio($name, $label, $value, $values, array $attributes = [])
    {
        $output = [];
        $output[] = '<strong>' . $label . '</strong>';
        foreach($values as $value_id => $value_label) {
            $checked = (string) $value_id == (string) $value;
            $output[] = $this->get_radio_single($name, $value_label, $value_id, $checked, $attributes);
        }
        return implode(PHP_EOL, $output);
    }

    /**
     * Echo out a group of radio buttons
     * @param $name
     * @param $label
     * @param $value
     * @param $values
     * @param array $attributes
     */
    private function radio_buttons($name, $label, $value, $values, array $attributes = [])
    {
        echo $this->get_radio_buttons($name, $label, $value, $values, $attributes);
    }

    /**
     * Echo out a group of checkbox buttons
     * @param $name
     * @param $label
     * @param $value
     * @param $values
     * @param array $attributes
     */
    private function checkbox_buttons($name, $label, $value, $values, array $attributes = [])
    {
        echo $this->get_checkbox_buttons($name, $label, $value, $values, $attributes);
    }

    /**
     * Get a group of radio/checkbox buttons
     * @param string $type
     * @param $name
     * @param $label
     * @param $value
     * @param $values
     * @param array $attributes
     * @return string
     */
    public function get_type_buttons($type = 'radio', $name, $label, $value, $values, array $attributes = [])
    {
        $output = [];
        if (!empty($label)) $output[] = '<p><strong>' . $label . '</strong></p>';
        $output[] = '<div class="btn-group btn-group-sm" data-toggle="buttons">';
        foreach($values as $value_id => $value_label) {
            $checked = (string) $value_id == (string) $value;
            $output[] = $this->{"get_$type" . "_single_button"}($name, $value_label, $value_id, $checked, $attributes);
        }
        $output[] = '</div>';
        return implode(PHP_EOL, $output);
    }

    /**
     * Get a group of radio buttons
     * @param $name
     * @param $label
     * @param $value
     * @param $values
     * @param array $attributes
     * @return string
     */
    public function get_radio_buttons($name, $label, $value, $values, array $attributes = [])
    {
        return $this->get_type_buttons('radio', $name, $label, $value, $values, $attributes);
    }

    /**
     * Get a group of checkbox buttons
     * @param $name
     * @param $label
     * @param $value
     * @param $values
     * @param array $attributes
     * @return string
     */
    public function get_checkbox_buttons($name, $label, $value, $values, array $attributes = [])
    {
        return $this->get_type_buttons('checkbox', $name, $label, $value, $values, $attributes);
    }

    /**
     * Get a single radio/checkbox button
     * @param string $type
     * @param $name
     * @param $label
     * @param $value
     * @param null $checked
     * @param array $attributes
     * @return string
     */
    public function get_type_single_button($type = 'radio', $name, $label, $value, $checked = null, array $attributes = [])
    {
        $output = [];

        if ($checked) $checked = ['checked'];
        if (is_null($checked)) $checked = $value ? ['checked'] : [];
        if (!$checked) $checked = [];

        $active = !empty($checked);
        $output[] = '<label class="btn btn-default' . ($active ? ' active' : '') . '">';

        $attributes = array_merge($checked, $attributes);
        $output[] = self::$form->$type($name, $value, null, $attributes) . $label;

        $output[] = '</label>';

        return implode(PHP_EOL, $output);
    }

    /**
     * Get a single radio button
     * @param $name
     * @param $label
     * @param $value
     * @param null $checked
     * @param array $attributes
     * @return string
     */
    public function get_radio_single_button($name, $label, $value, $checked = null, array $attributes = [])
    {
        return $this->get_type_single_button('radio', $name, $label, $value, $checked, $attributes);
    }

    /**
     * Get a single checkbox button
     * @param $name
     * @param $label
     * @param $value
     * @param null $checked
     * @param array $attributes
     * @return string
     */
    public function get_checkbox_single_button($name, $label, $value, $checked = null, array $attributes = [])
    {
        return $this->get_type_single_button('checkbox', $name, $label, $value, $checked, $attributes);
    }

    /**
     * Echo out a checkbox input field
     * @param $name
     * @param $label
     * @param $value
     * @param null $checked
     */
    private function checkbox($name, $label, $value, $checked = null)
    {
        echo $this->get_checkbox($name, $label, $value, $checked);
    }

    /**
     * Create a checkbox input field
     * @param $name
     * @param $label
     * @param $value
     * @param null $checked
     * @return string
     */
    public function get_checkbox($name, $label, $value, $checked = null)
    {
        if (empty($value))
        {
            if (is_null($checked)) $checked = [];
            $value = 1;
        }
        $output = [];
        $output[] = '<div class="checkbox"><label>';
        if ($checked) $checked = ['checked'];
        if (is_null($checked)) $checked = $value ? ['checked'] : [];
        $output[] = self::$form->checkbox($name, $value, null, $checked) . $label;
        $output[] = '</label></div>';
        return implode(PHP_EOL, $output);
    }

    /**
     * Echo out a checkbox input field group
     * @param $name
     * @param $label
     * @param $value
     * @param $values
     */
    private function checkbox_multiple($name, $label, $value, $values)
    {
        echo $this->get_checkbox_multiple($name, $label, $value, $values);
    }

    /**
     * Create a checkbox input field group
     * @param $name
     * @param $label
     * @param $value
     * @param $values
     * @return string
     */
    public function get_checkbox_multiple($name, $label, $value, $values)
    {
        $output = [];
        $output[] = '<strong>' . $label . '</strong>';
        foreach($values as $value_id => $value_label) {
            $checked = is_array($value) && in_array( (string) $value_id, $value );
            $output[] = $this->get_checkbox($name, $value_label, $value_id, $checked);
        }
        return implode(PHP_EOL, $output);
    }

    /**
     * Echo out a select dropdown
     * @param $name
     * @param $label
     * @param $value
     * @param $values
     * @param array $attributes
     */
    private function select($name, $label, $value, $values, array $attributes = [])
    {
        echo $this->get_select($name, $label, $value, $values, $attributes);
    }

    /**
     * Create a select dropdown
     * @param $name
     * @param $label
     * @param $value
     * @param $values
     * @param array $attributes
     * @return string
     */
    public function get_select($name, $label, $value, $values, array $attributes = [])
    {
        $output = [];
        $attributes = array_merge(['class' => 'form-control'], $attributes);
        $output[] = '<div class="form-group"><label for="' . $name . '">' . $label . ':</label><br/>';
        $output[] = self::$form->select($name, $values, $value, $attributes);
        $output[] = '</div>';
        return implode(PHP_EOL, $output);
    }

    /**
     * Echo out a multi-select field
     * @param $name
     * @param $label
     * @param $value
     * @param $values
     */
    private function select_multiple($name, $label, $value, $values)
    {
        echo $this->get_select_multiple($name, $label, $value, $values);
    }

    /**
     * Create a multi-select field
     * @param $name
     * @param $label
     * @param $value
     * @param $values
     * @return string
     */
    public function get_select_multiple($name, $label, $value, $values)
    {
        return $this->get_select($name, $label, $value, $values, ['multiple' => 'multiple']);
    }

    /**
     * Echo out a textarea field
     * @param $name
     * @param $label
     * @param $value
     * @param array $attributes
     */
    private function textarea($name, $label, $value, array $attributes = [])
    {
        echo $this->get_textarea($name, $label, $value, $attributes);
    }

    /**
     * Create a textarea field
     * @param $name
     * @param $label
     * @param $value
     * @param array $attributes
     * @return string
     */
    public function get_textarea($name, $label, $value, array $attributes = [])
    {
        $attributes = array_merge(['class' => 'form-control'], $attributes);
        $output = [];
        $output[] = '<div class="form-group"><label for="' . $name . '">' . $label . ':</label>';
        $output[] = self::$form->textarea($name, $value, $attributes);
        $output[] = '</div>';
        return implode(PHP_EOL, $output);
    }

    /**
     * Echo out a text input field
     * @param $name
     * @param $label
     * @param $value
     */
    private function input($name, $label, $value)
    {
        echo $this->get_input($name, $label, $value);
    }

    /**
     * Create a text input field
     * @param $name
     * @param $label
     * @param $value
     * @return string
     */
    public function get_input($name, $label, $value)
    {
        $output = [];
        $output[] = '<div class="form-group"><label for="' . $name . '">' . $label . ':</label>';
        $output[] = self::$form->input('text', $name, $value, ['class' => 'form-control']);
        $output[] = '</div>';
        return implode(PHP_EOL, $output);
    }

    /**
     * Echo out a group of select fields for editing hh:mm:ss format
     * @param $name
     * @param $label
     * @param $value
     */
    private function select_hhmmss($name, $label, $value)
    {
        echo $this->get_select_hhmmss($name, $label, $value);
    }

    /**
     * Create a group of select fields for editing hh:mm:ss format
     * @param $name
     * @param $label
     * @param $value
     * @return string
     */
    public function get_select_hhmmss($name, $label, $value)
    {
        $output = [];
        $select = [ 'hh' => [00,23], 'mm' => [0,59], 'ss' => [0,59] ];
        $output[] = '<div class="form-group"><label for="' . $name . '">' . $label . ':</label><br/>';

        foreach ($select as $select_name => $range)
            $output[] = $this->get_select_range($name . "[" . $select_name . "]", null, $value[$select_name], $range, "%02d") . PHP_EOL;

        $output[] = '</div>';
        return implode(PHP_EOL, $output);
    }

    /**
     * Echo out a select range
     * @param $name
     * @param $label
     * @param $value
     * @param $range
     * @param null $format
     */
    private function select_range($name, $label, $value, $range, $format = null)
    {
        echo $this->get_select_range($name, $label, $value, $range, $format);
    }

    /**
     * Create a select dropdown from a range
     * @param $name
     * @param $label
     * @param $value
     * @param $range
     * @param null $format
     * @return string
     */
    public function get_select_range($name, $label, $value, $range, $format = null)
    {
        $output = [];
        if (!is_null($label)) $output[] = '<div class="form-group"><label for="' . $name . '">' . $label . ':</label><br/>';
        $range = range($range[0], $range[1]);
        $values = [];
        foreach($range as $r)
        {
            $fr = is_null($format) ? $r : sprintf($format, $r);
            $values[$fr] = $fr;
            if ($r == 0) $values[$fr] = '-- None --';
        }
        $output[] = self::$form->select($name, $values, $value) . PHP_EOL;
        if (!is_null($label)) $output[] = '</div>';
        return implode(PHP_EOL, $output);
    }

    /**
     * Fetch a list of posts by $post_type and;
     * Compose an array of data for use with a select dropdown
     * @param $post_type
     * @param string $default_label
     * @param array $query
     * @return array
     */
    public static function select_values($post_type, $default_label = '-- Select --', array $query = [])
    {
        $posts_values = [];
        if (!is_array($post_type))
        {
            $query_default = [
                'post_type' => $post_type,
                'numberposts' => -1
            ];
            $query = array_merge($query_default, $query);
            $posts = get_posts($query);
        }
        else $posts = $post_type;

        foreach($posts as $post) $posts_values[$post->ID] = $post->post_title;
        if ($default_label) $posts_values = [$default_label] + $posts_values;

        return $posts_values;
    }
} 