<?php namespace Mosaicpro\WpCore;

use Mosaicpro\Button\Button;
use Mosaicpro\Core\IoC;

/**
 * Class Utility
 * @package Mosaicpro\WpCore
 */
class Utility
{
    /**
     * Utility method show_hide
     * Adds an action to load the show_hide.js utility and;
     * Localizes $localize_data and;
     * Only on $show_post_types post types and;
     * Only on $show_pages admin pages;
     * @param $localize_data
     * @param array $show_post_types
     * @param array $show_pages
     * @param bool $admin
     */
    public static function show_hide($localize_data, $show_post_types = [], $show_pages = ['post.php', 'post-new.php'], $admin = true)
    {
        $action = 'enqueue_scripts';
        if ($admin) $action = 'admin_' . $action;
        else $action = 'wp_' . $action;

        add_action($action, function($hook) use ($localize_data, $show_post_types, $show_pages)
        {
            global $post_type;

            if ($show_post_types && !empty($show_post_types))
                if (!in_array($post_type, $show_post_types, true)) return false;

            if ($show_pages && !empty($show_pages))
                if (!in_array($hook, $show_pages, true)) return false;

            self::enqueue_show_hide($localize_data);
        });
    }

    /**
     * Enqueue the show_hide utility script
     * @param $localize_data
     */
    public static function enqueue_show_hide($localize_data)
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
            'utility_show_hide_instance_' . self::str_random(),
            $localize_data
        );
    }

    /**
     * Get the media upload button markup
     * @param $name
     * @param $label
     * @param $value
     * @param string $button_text
     * @return string
     */
    public static function get_media_upload_button($name, $label, $value, $button_text = 'Upload')
    {
        self::enqueue_media_upload_button();
        $form = IoC::getContainer('form');

        $output = [];
        $output[] = '<span class="mp-media-upload-button">' .
            '<label for="' . $name . '">' . $label . ':</label>' .
            '<div class="input-group">' .
                $form->input('text', $name, $value, ['class' => 'regular-text text-upload form-control', 'placeholder' => 'Media URL']) .
                '<span class="input-group-btn">' .
                    Button::primary($button_text)->addClass('button-upload') .
                '</span>' .
            '</div><br/>' .
            '<img style="max-width: 300px; display: block;" src="' . $value . '" class="preview-upload" />' .
        '</span>';

        return implode(PHP_EOL, $output);
    }

    /**
     * Enqueue the media upload button styles & scripts
     */
    public static function enqueue_media_upload_button()
    {
        wp_enqueue_style('thickbox' );
        wp_enqueue_script('thickbox');
        wp_enqueue_script('media-upload');
        wp_enqueue_script('mp-media-upload-button', plugin_dir_url(__FILE__) . 'js/utility/upload/media-upload-button.js', ['jquery', 'thickbox', 'media-upload'], '1.0', true);
    }

    /**
     * Return a random string of $length length
     * @param int $length
     * @return string
     */
    public static function str_random($length = 10)
    {
        return str_shuffle(substr(str_repeat(md5(mt_rand()), 2+$length/32), 0, $length));
    }
} 