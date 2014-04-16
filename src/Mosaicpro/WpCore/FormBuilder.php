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
    public function __construct($name, $args)
    {
        self::$form = IoC::getContainer('form');
        return call_user_func_array([$this, $name], $args);
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
     * Create a radio input field
     * @param $name
     * @param $label
     * @param $value
     * @param null $checked
     */
    private function radio_single($name, $label, $value, $checked = null)
    {
        ?>
        <div class="radio">
            <label>
                <?php
                if ($checked) $checked = ['checked'];
                if (is_null($checked)) $checked = $value ? ['checked'] : [];
                echo self::$form->radio($name, $value, null, $checked) . $label;
                ?>
            </label>
        </div>
        <?php
    }

    /**
     * Create a radio input field group
     * @param $name
     * @param $label
     * @param $value
     * @param $values
     */
    private function radio($name, $label, $value, $values)
    {
        ?>
        <strong><?php echo $label; ?></strong>
        <?php
        foreach($values as $value_id => $value_label) {
            $checked = (string) $value_id == (string) $value;
            echo $this->radio_single($name, $value_label, $value_id, $checked);
        }
    }

    /**
     * Create a checkbox input field
     * @param $name
     * @param $label
     * @param $value
     * @param null $checked
     */
    private function checkbox($name, $label, $value, $checked = null)
    {
        if (empty($value))
        {
            if (is_null($checked)) $checked = [];
            $value = 1;
        }
        ?>
        <div class="checkbox">
            <label>
                <?php
                if ($checked) $checked = ['checked'];
                if (is_null($checked)) $checked = $value ? ['checked'] : [];
                echo self::$form->checkbox($name, $value, null, $checked) . $label;
                ?>
            </label>
        </div>
        <?php
    }

    /**
     * Create a checkbox input field group
     * @param $name
     * @param $label
     * @param $value
     * @param $values
     */
    private function checkbox_multiple($name, $label, $value, $values)
    {
        ?>
        <strong><?php echo $label; ?></strong>
        <?php
        foreach($values as $value_id => $value_label) {
            $checked = is_array($value) && in_array( (string) $value_id, $value );
            echo $this->checkbox($name, $value_label, $value_id, $checked);
        }
    }

    /**
     * Create a select dropdown
     * @param $name
     * @param $label
     * @param $value
     * @param $values
     * @param array $attributes
     */
    private function select($name, $label, $value, $values, array $attributes = [])
    {
        $attributes = array_merge(['class' => 'form-control'], $attributes);
        ?>
        <div class="form-group">
            <label for="<?php echo $name; ?>"><?php echo $label; ?>:</label>
            <?php echo self::$form->select($name, $values, $value, $attributes); ?>
        </div>
        <?php
    }

    /**
     * Create a multi-select field
     * @param $name
     * @param $label
     * @param $value
     * @param $values
     */
    private function select_multiple($name, $label, $value, $values)
    {
        return $this->select($name, $label, $value, $values, ['multiple' => 'multiple']);
    }

    /**
     * Create a textarea field
     * @param $name
     * @param $label
     * @param $value
     */
    private function textarea($name, $label, $value)
    {
        ?>
        <div class="form-group">
            <label for="<?php echo $name; ?>"><?php echo $label; ?>:</label>
            <?php echo self::$form->textarea($name, $value, ['class' => 'form-control']); ?>
        </div>
        <?php
    }

    /**
     * Create a text input field
     * @param $name
     * @param $label
     * @param $value
     */
    private function input($name, $label, $value)
    {
        ?>
        <div class="form-group">
            <label for="<?php echo $name; ?>"><?php echo $label; ?>:</label>
            <?php echo self::$form->input('text', $name, $value, ['class' => 'form-control']); ?>
        </div>
        <?php
    }

    /**
     * Fetch a list of posts by $post_type and;
     * Compose an array of data for use with a select dropdown
     * @param $post_type
     * @return array
     */
    public static function select_values($post_type)
    {
        $posts = get_posts([
            'post_type' => $post_type,
            'numberposts' => -1
        ]);
        $posts_values = [];

        foreach($posts as $post)
            $posts_values[$post->ID] = $post->post_title;

        return $posts_values;
    }
} 