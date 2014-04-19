<?php namespace Mosaicpro\WpCore;

class PostType
{
    public static function register($prefix = __CLASS__, $type, array $args = [])
    {
        if (!is_array($type)) $type = [$type, $type . 's'];
        $single = isset($type[0]) ? $type[0] : false;
        $multiple = isset($type[1]) ? $type[1] : false;
        if (!$single || !$multiple) return false;

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
            'menu_icon' => admin_url() . 'images/media-button-video.gif',
            'supports' => array(
                'title',
                'thumbnail',
                'excerpt'
            )
        );
        $args = array_merge($args_default, $args);

        add_action('init', function() use ($prefix, $slug_single, $args)
        {
            register_post_type($prefix . '_' . $slug_single, $args);
        });
    }
}