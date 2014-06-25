<?php namespace Mosaicpro\WpCore;

use Mosaicpro\HtmlGenerators\Form\FormBuilder as HtmlFormBuilder;

/**
 * Class FormBuilder
 * @package Mosaicpro\WpCore
 */
class FormBuilder extends HtmlFormBuilder
{
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