<?php namespace Mosaicpro\WpCore;

/**
 * Class PostList
 * @package Mosaicpro\WpCore
 */
class PostList
{
    /**
     * Create a new PostList instance
     * @param $name
     * @param $args
     */
    public function __construct($name, $args)
    {
        return call_user_func_array([$this, $name], $args);
    }

    /**
     * Handle the magic
     * @param $name
     * @param $args
     * @return static
     */
    public static function __callStatic($name, $args)
    {
        return new static($name, $args);
    }

    /**
     * Add custom columns to any post type listing in WP Admin
     * @param $post_type
     * @param array $add_columns
     */
    private function add_columns($post_type, $add_columns = [])
    {
        add_filter('manage_' . $post_type . '_posts_columns', function($columns, $add_columns) use ($add_columns)
        {
            foreach($add_columns as $add)
            {
                $position = false;
                if (count($add) === 3) $position = array_pop($add);
                $column = [$add[0] => $add[1]];

                if ($position) $columns = array_slice( $columns, 0, $position, true ) + $column + array_slice( $columns, $position, null, true );
                else $columns += $column;
            }
            return $columns;
        }, 10, 2);

        add_action('manage_' . $post_type . '_posts_custom_column', function($column, $post_id)
        {
            if ($column == 'thumbnail') echo self::post_thumbnail_edit_link($post_id);
            else echo get_post_meta($post_id, $column, true);
        }, 10, 2);
    }

    /**
     * Handle the display of custom columns added to post listings in WP Admin
     * @param $post_type
     * @param $callback
     */
    private function bind_column($post_type, $callback)
    {
        add_action('manage_' . $post_type . '_posts_custom_column', $callback, 10, 2);
    }

    /**
     * Helper method to get a post thumbnail
     * @param $post_id
     * @param int $width
     * @param int $height
     * @return string|void
     */
    public static function post_thumbnail($post_id, $width = 100, $height = 100)
    {
        $thumbnail_id = get_post_meta( $post_id, '_thumbnail_id', true );
        $attachments = get_children( array('post_parent' => $post_id, 'post_type' => 'attachment', 'post_mime_type' => 'image') );
        if ($thumbnail_id)
            $thumb = wp_get_attachment_image( $thumbnail_id, array($width, $height), true );
        elseif ($attachments)
        {
            foreach ( $attachments as $attachment_id => $attachment ) {
                $thumb = wp_get_attachment_image( $attachment_id, array($width, $height), true );
            }
        }
        if ( !empty($thumb) ) return $thumb;
        else return __('None');
    }

    /**
     * Helper method to get a link to the edit post WP Admin page
     * @param $post_id
     * @param string $content
     * @return string
     */
    public static function post_edit_link($post_id, $content = 'Edit')
    {
        return '<a href="' . get_edit_post_link($post_id) . '">' . $content . '</a>';
    }

    /**
     * Helper method to get a post thumbnail linking to the post edit WP Admin page
     * @param $post_id
     * @param int $width
     * @param int $height
     * @return string
     */
    public static function post_thumbnail_edit_link($post_id, $width = 100, $height = 100)
    {
        return self::post_edit_link($post_id, self::post_thumbnail($post_id, $width, $height));
    }
}