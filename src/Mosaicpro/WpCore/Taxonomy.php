<?php namespace Mosaicpro\WpCore;

class Taxonomy 
{
    public static function register($name, $post_type, array $args = [])
    {
        if (!is_array($post_type)) $post_type = [$post_type];
        if (!is_array($name)) $name = [$name, $name . 's'];
        $single = isset($name[0]) ? $name[0] : false;
        $multiple = isset($name[1]) ? $name[1] : false;
        if (!$single || !$multiple) return false;

        $label_single = ucwords($single);
        $label_multiple = ucwords($multiple);
        $slug_single = str_replace(" ", "_", $single);

        $args_default = array(
            'hierarchical' => true,
            'query_var' => $slug_single,
            'labels' => array(
                'name' => $label_multiple,
                'singular_name' => $label_single,
                'edit_item' => 'Edit ' . $label_single,
                'update_item' => 'Update ' . $label_single,
                'add_new_item' => 'Add ' . $label_single,
                'new_item_name' => 'Add New ' . $label_single,
                'all_items' => 'All ' . $label_multiple,
                'search_items' => 'Search ' . $label_multiple,
                'popular_items' => 'Popular ' . $label_multiple,
                'separate_items_with_comments' => 'Separate ' . $label_multiple . ' with commas',
                'add_or_remove_items' => 'Add or remove ' . $label_multiple,
                'choose_from_most_used' => 'Choose from most used ' . $label_multiple
            )
        );
        $args = array_merge($args_default, $args);
        register_taxonomy($slug_single, $post_type, $args);
    }

    public static function registerMany(array $taxonomies, array $args = [])
    {
        foreach ($taxonomies as $taxonomy => $args)
            self::register($taxonomy, $args['post_type'], $args['args']);
    }
} 