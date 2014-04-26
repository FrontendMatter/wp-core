<?php namespace Mosaicpro\WpCore;

/**
 * Class Plugin
 * @package Mosaicpro\WpCore
 */
class Plugin extends PluginGeneric
{
    /**
     * Create a new Plugin instance
     * @param $plugin_file
     */
    public function __construct($plugin_file)
    {
        $prefix = $this->getPluginName( $plugin_file );
        $this->setPrefix($prefix);
        $this->setPluginFile($plugin_file);
        $this->setTextDomain($prefix);
    }

    /**
     * Automatically load the Plugin's Templates into the Theme
     * This method should be called only once in the Plugin
     */
    public function initPluginTemplates()
    {
        // Add a filter to template_include in order to determine if the page has a template assigned within the plugin and return it's path
        add_filter('template_include', array( $this, 'viewPageTemplate') );

        // Include partials from the plugin if the partial doesn't already exist in the theme
        add_action('get_template_part_' . $this->getPrefix(), function($slug, $name)
        {
            $template_file = $name . '.php';

            // template file path in the current theme
            $file_theme = $this->getThemeDirectory() . $slug . '/templates/partials/' . $template_file;

            if (file_exists($file_theme)) require $file_theme;

            // template file path in the plugin
            $file_plugin = plugin_dir_path( $this->getPluginFile() ) . 'templates/partials/' . $template_file;

            if (file_exists($file_plugin)) require $file_plugin;

        }, 10, 2);
    }

    /**
     * Determines what template file to use when displaying a page
     * template_include filter callback
     * @param $template
     * @return string
     */
    public function viewPageTemplate( $template )
    {
        global $post;

        if (!is_single()) return $template;

        $template_file = get_post_meta( $post->ID, '_wp_page_template', true );
        $templates = $this->getPageTemplates();

        if ( ! isset( $templates[ $template_file ] ) )
        {
            $template_file = 'single-' . $post->post_type . '.php';

            // load the template file only if it was defined by our plugin
            // if ( ! isset( $templates[ $template_file ] ) ) return $template;
        }

        // template file path in the current theme
        $file_theme = $this->getThemeDirectory() . $template_file;

        // template file path in the plugin
        $file_plugin = plugin_dir_path( $this->getPluginFile() ) . 'templates/' . $template_file;

        // Template files in the current theme directory have priority
        if ( file_exists( $file_theme ) ) return $file_theme;

        // If the current theme doesn't have a template file, use the one provided by the plugin
        if ( file_exists( $file_plugin ) ) return $file_plugin;

        // Return the default template
        return $template;
    }

    /**
     * Get the Page templates WP cache key
     * @return string
     */
    private function getCacheKey()
    {
        $cache_key = 'page_templates-' . md5( get_theme_root() . '/' . get_stylesheet() );
        return $cache_key;
    }

    /**
     * Get the page templates from the WP cache
     * @return array|bool|mixed
     */
    public function getCachePageTemplates()
    {
        $cache_key = $this->getCacheKey();

        // Retrieve the cache list. If it doesn't exist, or it's empty prepare an array
        $templates = wp_cache_get( $cache_key, 'themes' );
        if ( empty( $templates ) ) {
            $templates = array();
        }

        return $templates;
    }

    /**
     * Inject page templates into the WP cache
     * @param array $templates
     */
    public function addCachePageTemplates(array $templates)
    {
        $cache_key = $this->getCacheKey();
        $templates_cache = $this->getCachePageTemplates();

        // Since we'll update the cache, we need to delete the old cache
        wp_cache_delete( $cache_key , 'themes');

        // Now add our template to the list of templates by merging our templates
        // with the existing templates array from the cache.
        $templates = array_merge( $templates_cache, $templates );

        // Add the modified cache to allow WordPress to pick it up for listing
        // available templates
        wp_cache_add( $cache_key, $templates, 'themes', 1800 );
    }

    /**
     * Hooks callback that injects the page templates into WP cache
     * @param $atts
     * @return mixed
     */
    public function registerPageTemplates($atts)
    {
        $this->addCachePageTemplates($this->getPageTemplates());
        return $atts;
    }

    /**
     * Enable selection of Plugin Page Templates from the Edit Page in WP Admin
     */
    public function initPageTemplates()
    {
        // Add a filter to the page attributes metabox to inject our templates into the page template cache.
        add_filter('page_attributes_dropdown_pages_args', array( $this, 'registerPageTemplates' ) );

        // Add a filter to the save post in order to inject the page templates into the page cache
        add_filter('wp_insert_post_data', array( $this, 'registerPageTemplates' ) );
    }

    /**
     * Allow the selection of a page template from the $post_type post edit page in WP Admin
     * @param $post_type
     */
    public function initPostTemplates($post_type)
    {
        // hook into the page template metabox and inject the templates into the cache
        // so it shows in the page templates dropdown
        add_action('post_attributes_metabox', array( $this, 'registerPageTemplates' ) );

        // add a save_post action in order to save the selected page template from the
        // custom post type edit screen in WP Admin
        PostData::save_page_template($this->prefix . '_' . $post_type);

        // create the page template metabox
        MetaBox::make($this->prefix, 'page_template', $this->__('Page Template'))
            ->setPostType($post_type)
            ->setContext('side')
            ->setDisplay([
                function($post)
                {
                    do_action('post_attributes_metabox');

                    $template = $post->_wp_page_template;
                    echo '<label class="screen-reader-text" for="page_template">' . $this->__('Page Template') . '</label>
                        <select name="_wp_page_template" id="page_template">
                        <option value="default">' . $this->__('Default Template') . '</option>';

                    page_template_dropdown($template);

                    echo "</select>";
                }
            ])
            ->register();
    }
}