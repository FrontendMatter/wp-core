<?php namespace Mosaicpro\WpCore;

/**
 * Class PostType
 * @package Mosaicpro\WpCore
 */
class PostType
{
    /**
     * Holds the PostType name
     * @var
     */
    protected $name;

    /**
     * Holds the PostType prefix
     * @var
     */
    protected $prefix;

    /**
     * Holds the PostType options
     * @var array
     */
    protected $args = [];

    /**
     * Creates a new PostType instance
     * @param $name
     * @param $prefix
     */
    private function __construct($name, $prefix)
    {
        $this->setName($name);
        $this->prefix = $prefix;
        return $this;
    }

    /**
     * Creates a new PostType instance statically
     * @param $name
     * @param null $prefix
     * @return static
     */
    public static function make($name, $prefix = null)
    {
        return new static($name, $prefix);
    }

    /**
     * Sets the name of the PostType
     * @param $name
     */
    private function setName($name)
    {
        if (!is_array($name)) $name = [$name, $name . 's'];
        $this->name = $name;
    }

    /**
     * Sets the Options of the PostType
     * @param $args
     * @return $this
     */
    public function setOptions($args)
    {
        $this->args = $args;
        return $this;
    }

    /**
     * Registers the PostType with WordPress
     */
    public function register()
    {
        $single = $this->name[0];
        $multiple = $this->name[1];

        $label_single = ucwords($single);
        $label_multiple = ucwords($multiple);
        $slug_single = str_replace(" ", "_", $single);
        $slug_multiple = str_replace(" ", "_", $multiple);

        $args_default = array(
            'labels' => array(
                'name' => $label_multiple,
                'singular_name' => $label_single,
                'add_new' => 'Add New ' . $label_single,
                'add_new_item' => 'Add New ' . $label_single,
                'edit_item' => 'Edit Item',
                'new_item' => 'Add New Item',
                'view_item' => 'View ' . $label_single,
                'search_items' => 'Search ' . $label_multiple,
                'not_found' => 'No ' . $label_multiple . ' Found',
                'not_found_in_trash' => 'No ' . $label_multiple . ' Found in Trash'
            ),
            'query_var' => $slug_multiple,
            'rewrite' => array(
                'slug' => $slug_multiple
            ),
            'public' => true,
            'supports' => array(
                'title',
                'thumbnail',
                'excerpt'
            )
        );
        $args = array_merge($args_default, $this->args);

        add_action('init', function() use ($slug_single, $args)
        {
            $post_type = !empty($this->prefix) ? $this->prefix . '_' . $slug_single : $slug_single;
            if (post_type_exists($post_type)) return false;
            register_post_type($post_type, $args);
        });
    }
}