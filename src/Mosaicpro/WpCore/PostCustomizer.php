<?php namespace Mosaicpro\WpCore;

use Mosaicpro\WpCore\Customizer\Controls\Page_Template;

/**
 * Class PostCustomizer
 * @package Mosaicpro\WpCore
 */
class PostCustomizer
{
    /**
     * Holds a PostCustomizer instance
     * @var
     */
    protected static $instance;

    /**
     * Create a new PostCustomizer instance
     */
    public function __construct(){}

    /**
     * Get a PostCustomizer Singleton instance
     * @return static
     */
    public static function getInstance()
    {
        if (is_null(self::$instance))
        {
            self::$instance = new static();
        }
        return self::$instance;
    }

    /**
     * Dynamically Display Page Templates Control in the Theme Customizer;
     * Applies only on single pages and where get_post_type is starting with $prefix
     * @param $prefix
     */
    public function initPageTemplates($prefix)
    {
        if (!is_admin()) return;

        add_action('customize_register', function($wp_customize) use ($prefix)
        {
            $post = $this->getPost($prefix);
            if (!$post) return false;

            // block loading other links from the preview in the iframe
            add_filter('customize_allowed_urls', function(){ return [ $_GET['url'] ]; });

            // load the Page_Template control
            require_once realpath(__DIR__) . '/Customizer/Controls/Page_Template.php';

            $wp_customize->add_section( 'page_template',
                array(
                    'title' => __( 'Page Template' ),
                    'capability' => 'edit_theme_options',
                    'description' => __('Allows you to change the layout of this page.')
                )
            );

            $wp_customize->add_setting( '_wp_page_template' , [
                'default' => $post->_wp_page_template,
                'type' => 'page_template'
            ]);

            $wp_customize->add_setting( '_wp_post_id' , [
                'default' => $post->ID,
                'type' => 'post'
            ]);

            $wp_customize->add_control( new Page_Template(
                $wp_customize,
                '_wp_page_template',
                array(
                    'label'          => __( 'Select a page template' ),
                    'section'        => 'page_template',
                    'settings'       => '_wp_page_template'
                )
            ));

        });

        add_action('customize_update_page_template', function($value) use ($prefix)
        {
            $post = $this->getPost($prefix);
            if (!$post) return false;

            return update_post_meta($post->ID, '_wp_page_template', $value);
        });
    }

    /**
     * Get the post data from the url opened in the Theme Customizer;
     * If we're not on a single post page, return false;
     * @param string $prefix Optional. Filter by post type prefix;
     * @return bool|null|\WP_Post
     */
    public function getPost($prefix = null)
    {
        if (empty($_GET['url']))
        {
            $wp_customize = !empty($_POST['wp_customize']) && $_POST['wp_customize'] == 'on';
            $customized = isset($_POST['customized']) ? $_POST['customized'] : false;
            if (!$wp_customize || !$customized)
                return false;

            $customized = json_decode(wp_unslash($customized), true);
            $post_id = isset($customized['_wp_post_id']) ? $customized['_wp_post_id'] : false;
            if (!$post_id)
                return false;
        }
        else
        {
            $url = $_GET['url'];
            $url = urldecode( $url );

            $post_id = url_to_postid($url);
            if (empty($post_id)) return false;
        }

        $post = get_post($post_id);
        if (!$post) return false;

        if (!is_null($prefix) && !starts_with(get_post_type($post_id), $prefix)) return false;
        return $post;
    }
}