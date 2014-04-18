<?php namespace Mosaicpro\WpCore;

/**
 * Class PostData
 * @package Mosaicpro\WpCore
 */
class PostData
{
    /**
     * Adds save_post action for saving post meta fields
     * @param array $fields
     */
    public static function save_meta_fields(array $fields = [])
    {
        // POST['meta']['name']
        add_action('save_post', function($id) use ($fields)
        {
            foreach($fields as $field)
            {
                $name = $field;
                if (isset($field['name'])) $name = $field['name'];
                if (ends_with($name, '[]')) $name = substr($name, 0, -2);

                $db_field_name = $name;
                if (starts_with($db_field_name, 'meta['))
                    $db_field_name = str_replace(["meta[", "]"], "", $db_field_name);

                if (isset($field['type']) && $field['type'] == 'checkbox')
                    update_post_meta( $id, $name, '' );

                if ( isset($_POST[$name]) || isset($_POST['meta'][$db_field_name]) )
                {
                    $value = isset($_POST[$name]) ? $_POST[$name] : (isset($_POST['meta'][$db_field_name]) ? $_POST['meta'][$db_field_name] : '');
                    if (!is_array($value)) $value = strip_tags($value);
                    update_post_meta( $id, $db_field_name, $value );
                }
            }
        }, 10, 2);
    }

    /**
     * Allow wp_insert_post with empty title and content
     * Useful for custom post types that don't require these fields
     */
    public static function allow_empty()
    {
        add_filter('pre_post_title', [__CLASS__, 'allow_empty_mask']);
        add_filter('pre_post_content', [__CLASS__, 'allow_empty_mask']);
        add_filter('wp_insert_post_data', [__CLASS__, 'allow_empty_unmask']);
    }

    /**
     * Used by allow_empty filters
     * Sets the post title and content to a blank space string to mime that it's not empty
     * @param $value
     * @return string
     */
    public static function allow_empty_mask($value)
    {
        if ( empty($value) ) return ' ';
        return $value;
    }

    /**
     * Used by allow_empty filters
     * Sets the post title and content to an empty string
     * @param $data
     * @return mixed
     */
    public static function allow_empty_unmask($data)
    {
        if ( ' ' == $data['post_title'] ) $data['post_title'] = '';
        if ( ' ' == $data['post_content'] ) $data['post_content'] = '';
        return $data;
    }
} 