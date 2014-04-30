<?php namespace Mosaicpro\WpCore;
use WP_Post;

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
     * Adds save_post action for saving the selected custom page templates
     * on the post edit screen in WP Admin;
     * @param $post_type
     */
    public static function save_page_template($post_type)
    {
        add_action('save_post', function($post_id) use ($post_type)
        {
            if ( $post_type !== $_POST['post_type'] ) return;

            # Skip the auto saves
            if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
            elseif ( defined( 'DOING_AJAX' ) && DOING_AJAX ) return;
            elseif ( defined( 'DOING_CRON' ) && DOING_CRON ) return;

            update_post_meta($post_id, '_wp_page_template', esc_attr($_POST['_wp_page_template']));
        });
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

    /**
     * Get a default WP_Post object
     * @param $post_type
     * @return stdClass|WP_Post
     */
    public static function get_default($post_type)
    {
        $post = new \stdClass;
        $post->ID = 0;
        $post->post_author = get_current_user_id();
        $post->post_date = '';
        $post->post_date_gmt = '';
        $post->post_password = '';
        $post->post_type = $post_type;
        $post->post_status = 'draft';
        $post->to_ping = '';
        $post->pinged = '';
        $post->comment_status = get_option( 'default_comment_status' );
        $post->ping_status = get_option( 'default_ping_status' );
        $post->post_pingback = get_option( 'default_pingback_flag' );
        $post->post_category = get_option( 'default_category' );
        $post->page_template = 'default';
        $post->post_parent = 0;
        $post->menu_order = 0;
        $post = new WP_Post( $post );
        return $post;
    }
} 