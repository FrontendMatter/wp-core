<?php namespace Mosaicpro\WpCore;

/**
 * Class Utility
 * @package Mosaicpro\WpCore
 */
class Utility
{
    /**
     * Utility method show_hide
     * Load the show_hide.js utility and;
     * Localize $localize_data and;
     * Only on $show_post_types post types and;
     * Only on $show_pages admin pages;
     * @param $localize_data
     * @param array $show_post_types
     * @param array $show_pages
     */
    public static function show_hide($localize_data, array $show_post_types = [], array $show_pages = ['post.php', 'post-new.php'])
    {
        add_action('admin_enqueue_scripts', function($hook) use ($localize_data, $show_post_types, $show_pages)
        {
            global $post_type;
            if (in_array($hook, $show_pages, true) && in_array($post_type, $show_post_types, true))
            {
                $localize_data_default = [
                    'when' => '#quiz_unit_typechecklist',
                    'selector_changes' => ' :radio',
                    'attribute' => 'data-slug',
                    'is_value' => 'multiple_choice',
                    'show_target' => '#mp_lms_quiz_answer'
                ];
                $localize_data = array_merge($localize_data_default, $localize_data);
                $script_id = 'utility_show_hide';
                wp_enqueue_script($script_id, plugin_dir_url(__FILE__) . 'js/utility/show_hide.js', ['jquery'], '1.0', true);
                wp_localize_script(
                    $script_id,
                    'utility_show_hide',
                    $localize_data
                );
            }
        });
    }
} 