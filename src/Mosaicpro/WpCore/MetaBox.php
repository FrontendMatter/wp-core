<?php namespace Mosaicpro\WpCore;

/**
 * Class MetaBox
 * @package Mosaicpro\WpCore
 */
class MetaBox
{
    /**
     * Holds the MetaBox prefix
     * @var string
     */
    protected $prefix = '';

    /**
     * Holds the MetaBox unique identifier
     * @var string
     */
    protected $name = '';

    /**
     * Holds the MetaBox Heading Label
     * @var string
     */
    protected $label = '';

    /**
     * Holds the MetaBox post type
     * @var string
     */
    protected $post_type = '';

    /**
     * Holds the MetaBox content
     * @var array
     */
    protected $display = [];

    /**
     * Holds the MetaBox form fields
     * @var array
     */
    protected $fields = [];

    /**
     * Holds the MetaBox context on page
     * @var string
     */
    protected $context = 'advanced';

    /**
     * Holds the MetaBox priority on page
     * @var string
     */
    protected $priority = 'default';

    /**
     * Add a form field to the MetaBox
     * Example usage:
     * ->setField('the_input', 'Your name', 'input')
     * ->setField('select_me', 'Select the correct answer', 'mp_lms_quiz_answer', 'select')
     * ->setField('check_it', 'Check this', 'checkbox')
     * ->setField('radio_multiple', 'Select the correct answer', 'mp_lms_quiz_answer', 'radio')
     * ->setField('radio_array', 'Select one value in the array', ['Option 1', 'Option 2', 'Option 3'], 'radio')
     * ->setField('checkbox_multiple[]', 'Check multiple answers', 'mp_lms_quiz_answer', 'checkbox_multiple')
     * ->setField('select_multiple[]', 'Select multiple answers', 'mp_lms_quiz_answer', 'select_multiple')
     * TODO: adding a callable closure field breaks the rest of the fields
     * @return $this
     */
    public function setField()
    {
        $args = func_get_args();
        $name = isset($args[0]) ? $args[0] : false;
        $label = isset($args[1]) && !is_callable($args[1]) ? $args[1] : $name;
        $values = isset($args[2]) && !is_callable($args[2]) ? $args[2] : '';
        $type = array_pop($args);
        $field = ['type' => $type, 'name' => $name, 'label' => $label, 'values' => $values];
        $this->fields[] = $field;
        return $this;
    }

    /**
     * Create a new MetaBox instance
     */
    public function __construct()
    {
        $this->prefix = '';
        $this->name = '';
        $this->label = '';
        $this->post_type = '';
        $this->display = [];
        $this->fields = [];
    }

    /**
     * Perform WordPress add_meta_boxes and save_post actions
     * @return mixed
     */
    public function register()
    {
        return $this->add()->save();
    }

    /**
     * Perform WordPress add_meta_boxes action
     * @return $this
     */
    private function add()
    {
        if (is_array($this->post_type))
            foreach ($this->post_type as $pt)
                $this->add_meta_box($pt);
        else
            $this->add_meta_box($this->post_type);

        return $this;
    }

    private function add_meta_box($post_type)
    {
        add_action('add_meta_boxes', function() use ($post_type)
        {
            add_meta_box(
                $this->prefix . '_' . $this->name,
                $this->label,
                function($post) {
                    $this->display($post);
                },
                $post_type,
                $this->context,
                $this->priority
            );
        });
    }

    /**
     * Perform WordPress save_post action
     * @return $this
     */
    private function save()
    {
        if (empty($this->fields))
            return $this;

        PostData::save_meta_fields($this->fields);
        return $this;
    }

    /**
     * Output the MetaBox content
     * @param $post
     */
    private function display($post)
    {
        ?>
        <div class="bootstrap">
        <?php
        $components = ['fields'];

        if (empty($this->display))
            $this->display_component('fields', [$post]);
        else
        {
            foreach ($this->display as $display => $args)
            {
                if (is_numeric($display))
                    $display = $args;

                if (is_callable($display))
                {
                    $display($post);
                    continue;
                }

                if (in_array($display, $components, true))
                {
                    if ($display == 'fields')
                        $this->display_component($display, [$post]);
                    else
                        $this->display_component($display, $args);
                }
                else echo $display;
            }
        }
        ?>
        </div>
        <?php
    }

    /**
     * Output a MetaBox display component
     * @param $display
     * @param array $args
     * @return mixed
     */
    private function display_component($display, array $args = [])
    {
        switch ($display)
        {
            default: break;
            case 'fields':
                return @call_user_func_array([$this, 'fields'], $args);
                break;
        }
    }

    /**
     * Output the MetaBox Fields Display component
     * @param $post
     */
    private function fields($post)
    {
        foreach ($this->fields as $field)
        {
            $name = $field['name'];
            if (ends_with($name, '[]')) $name = substr($name, 0, -2);
            $value = get_post_meta($post->ID, $name, true);
            $label = isset($field['label']) ? $field['label'] : ucwords(str_replace("_", " ", $name));
            $values = isset($field['values']) ? (is_array($field['values']) ? $field['values'] : FormBuilder::select_values($field['values'])) : [];
            if (in_array($field['type'], ['select', 'select_multiple'])) $values = [' -- ' . $label . ' -- '] + $values;

            if (is_callable($field['type']))
                return $field['type']($field['name'], $label, $value, $values);

            switch ($field['type'])
            {
                default: break;

                case 'input':
                case 'textarea':
                case 'checkbox':
                case 'select_hhmmss':
                    FormBuilder::$field['type']($field['name'], $label, $value);
                    break;

                case 'select':
                case 'select_multiple':
                case 'checkbox_multiple':
                case 'radio':
                case 'select_range':
                    FormBuilder::$field['type']($field['name'], $label, $value, $values);
                    break;
            }
        }
    }

    /**
     * Create a new MetaBox instance statically
     * @param $name
     * @param $args
     * @return mixed
     */
    public static function __callStatic($name, $args)
    {
        $instance = new static();
        return call_user_func_array([$instance, 'init'], $args);
    }

    /**
     * Initialize the MetaBox
     * @param $prefix
     * @param $name
     * @param $label
     * @return $this
     */
    public function init($prefix, $name, $label)
    {
        $this->prefix = $prefix;
        $this->name = $name;
        $this->label = $label;
        $this->post_type = 'post';
        return $this;
    }

    /**
     * Set the MetaBox post type
     * @param $post_type
     * @return $this
     */
    public function setPostType($post_type)
    {
        $this->post_type = $post_type;
        return $this;
    }

    /**
     * Set the MetaBox context on the page
     * @param $context
     * @return $this
     */
    public function setContext($context)
    {
        $this->context = $context;
        return $this;
    }

    /**
     * Set the MetaBox priority on the page
     * @param $priority
     * @return $this
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;
        return $this;
    }

    /**
     * Set the MetaBox form fields
     * @param $fields
     * @return $this
     */
    public function setFields($fields)
    {
        $this->fields = $fields;
        return $this;
    }

    /**
     * Set the MetaBox content
     * @param $display
     * @return $this
     */
    public function setDisplay($display)
    {
        $this->display = $display;
        return $this;
    }

    /**
     * Update an existing MetaBox
     * @param $metabox
     * @param $post_type
     * @param $old_context
     * @param $new_label
     * @param $new_callback
     * @param $new_context
     * @param $new_priority
     */
    public static function update( $metabox, $post_type, $old_context, $new_label, $new_callback, $new_context, $new_priority )
    {
        if (!is_array($post_type))
            $post_type = [$post_type];

        foreach($post_type as $pt)
            self::update_for_post_type($metabox, $pt, $old_context, $new_label, $new_callback, $new_context, $new_priority);
    }

    /**
     * @param $metabox
     * @param $post_type
     * @param $old_context
     * @param $new_label
     * @param $new_callback
     * @param $new_context
     * @param $new_priority
     */
    public static function update_for_post_type( $metabox, $post_type, $old_context, $new_label, $new_callback, $new_context, $new_priority )
    {
        add_action('admin_head', function() use ($metabox, $post_type, $old_context, $new_label, $new_callback, $new_context, $new_priority)
        {
            remove_meta_box( $metabox . 'div', $post_type, $old_context );
            add_meta_box( $metabox, $new_label, $new_callback, $post_type, $new_context, $new_priority);
        });
    }
}