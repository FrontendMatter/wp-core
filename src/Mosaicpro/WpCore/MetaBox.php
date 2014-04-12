<?php namespace Mosaicpro\WpCore;

use Mosaicpro\Core\IoC;

/**
 * Class MetaBox
 * @package Mosaicpro\WpCore
 */
class MetaBox
{
    /**
     * @var string
     */
    protected $prefix = '';

    /**
     * @var string
     */
    protected $name = '';

    /**
     * @var string
     */
    protected $label = '';

    /**
     * @var string
     */
    protected $post_type = '';

    /**
     * @var array
     */
    protected $display = [];

    /**
     * @var array
     */
    protected $fields = [];

    /**
     * @var string
     */
    protected $context = 'advanced';

    /**
     * @var string
     */
    protected $priority = 'default';

    /**
     *
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
     * @return mixed
     */
    public function register()
    {
        return $this->add()->save();
    }

    /**
     * @return $this
     */
    private function add()
    {
        add_action('add_meta_boxes', function()
        {
            add_meta_box(
                $this->prefix . '_' . $this->name,
                $this->label,
                function($post) {
                    $this->display($post);
                },
                $this->prefix . '_' . $this->post_type,
                $this->context,
                $this->priority
            );
        });
        return $this;
    }

    /**
     * @return $this
     */
    private function save()
    {
        if (empty($this->fields))
            return $this;

        add_action('save_post', function($id)
        {
            foreach($this->fields as $field)
            {
                $name = $field['name'];
                if (ends_with($name, '[]')) $name = substr($name, 0, -2);

                if ( isset($_POST[$name]) )
                {
                    $value = $_POST[$name];
                    if (!is_array($value)) $value = strip_tags($value);

                    update_post_meta(
                        $id,
                        $name,
                        $value
                    );
                }
            }
        }, 10, 2);

        return $this;
    }

    /**
     * @param $post
     */
    private function display($post)
    {
        $components = ['fields'];

        if (empty($this->display))
            $this->display_component('fields', [$post]);
        else
        {
            foreach ($this->display as $display => $args)
            {
                if (is_numeric($display))
                    $display = $args;

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
    }

    /**
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
     * @param $post
     */
    private function fields($post)
    {
        $form = IoC::getContainer('form');
        foreach ($this->fields as $field)
        {
            $name = $field['name'];
            if (ends_with($name, '[]')) $name = substr($name, 0, -2);
            $value = get_post_meta($post->ID, $name, true);
            $label = isset($field['label']) ? $field['label'] : ucwords(str_replace("_", " ", $name));
            $values = isset($field['values']) ? $this->get_select_values($field['values']) : [];
            switch ($field['type'])
            {
                default: break;

                case 'input':
                    ?>
                        <p><label for="<?php echo $field['name']; ?>"><?php echo $label; ?>:</label>
                            <?php echo $form->input('text', $field['name'], $value, ['class' => 'widefat']); ?>
                        </p>
                    <?php
                    break;

                case 'textarea':
                    ?>
                    <p><label for="<?php echo $field['name']; ?>"><?php echo $label; ?>:</label>
                        <?php echo $form->textarea($field['name'], $value, ['class' => 'widefat']); ?>
                    </p>
                    <?php
                    break;

                case 'select':
                    ?>
                    <p><label for="<?php echo $field['name']; ?>"><?php echo $label; ?>:</label>
                        <?php echo $form->select($field['name'], $values, $value, ['class' => 'widefat']); ?>
                    </p>
                    <?php
                    break;

                case 'select_multiple':
                    ?>
                    <p><label for="<?php echo $field['name']; ?>"><?php echo $label; ?>:</label>
                        <?php echo $form->select($field['name'], $values, $value, ['class' => 'widefat', 'multiple' => 'multiple']); ?>
                    </p>
                    <?php
                    break;

                case 'FormField':
                    echo forward_static_call_array(['FormField', $field['name']], []);
                    break;
            }
        }
    }

    /**
     * @param $post_type
     * @return array
     */
    private function get_select_values($post_type)
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

    /**
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
     * @param $post_type
     * @return $this
     */
    public function setPostType($post_type)
    {
        $this->post_type = $post_type;
        return $this;
    }

    /**
     * @param $context
     * @return $this
     */
    public function setContext($context)
    {
        $this->context = $context;
        return $this;
    }

    /**
     * @param $priority
     * @return $this
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;
        return $this;
    }

    /**
     * @param $fields
     * @return $this
     */
    public function setFields($fields)
    {
        $this->fields = $fields;
        return $this;
    }

    /**
     * @param $display
     * @return $this
     */
    public function setDisplay($display)
    {
        $this->display = $display;
        return $this;
    }
}