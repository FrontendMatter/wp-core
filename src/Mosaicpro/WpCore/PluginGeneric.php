<?php namespace Mosaicpro\WpCore;

use Mosaicpro\Core\IoC;

/**
 * Class PluginGeneric
 * @package Mosaicpro\WpCore
 */
class PluginGeneric
{
    /**
     * Holds a PluginGeneric instance
     * @var
     */
    protected static $instance;

    /**
     * Holds the plugin prefix
     * @var
     */
    protected $prefix;

    /**
     * Holds the i18n text domain
     * @var
     */
    protected $text_domain;

    /**
     * Holds plugin page templates
     * @var array
     */
    protected $page_templates = [];

    /**
     * Holds a Mosaicpro\WpCore\Plugin instance
     * @var mixed
     */
    protected $plugin;

    /**
     * Holds the main plugin file path
     * @var
     */
    protected $plugin_file;

    /**
     * Creates a new PluginGeneric instance
     */
    public function __construct()
    {
        $this->plugin = IoC::getContainer('plugin');
        $this->prefix = $this->plugin->getPrefix();
        $this->text_domain = $this->plugin->getTextDomain();
    }

    /**
     * Get a PluginGeneric instance
     * @return static
     */
    public static function getInstance()
    {
        self::$instance = new static();
        return self::$instance;
    }

    /**
     * Creates a new PluginGeneric instance statically
     * @return static
     */
    public static function init()
    {
        return new static();
    }

    /**
     * Get the Plugin prefix
     * @param null $post_type Optional post type short name to prefix
     * @return mixed
     */
    public function getPrefix($post_type = null)
    {
        return is_null($post_type) ? $this->prefix : $this->prefix . '_' . $post_type;
    }

    /**
     * Set the Plugin prefix
     * @param $prefix
     */
    public function setPrefix($prefix)
    {
        $prefix = str_replace("-", "_", $prefix);
        $this->prefix = $prefix;
    }

    /**
     * Set the main plugin file path
     * @param $file
     */
    public function setPluginFile($file)
    {
        $this->plugin_file = $file;
    }

    /**
     * Get the main plugin file path
     * @return mixed
     */
    public function getPluginFile()
    {
        return $this->plugin_file;
    }

    /**
     * Get the plugin name from the main plugin file path
     * @param $plugin_file
     * @return string
     */
    public function getPluginName($plugin_file)
    {
        return plugin_basename( dirname($plugin_file) );
    }

    /**
     * Set the i18n text domain
     * @param null $text_domain
     */
    public function setTextDomain($text_domain = null)
    {
        if (is_null($text_domain)) $text_domain = str_replace("_", "-", $this->getPrefix());
        $this->text_domain = $text_domain;
    }

    /**
     * Grab the translations for the plugin
     * This method should be called only once from the main plugin component
     */
    public function loadTextDomain()
    {
        add_action( 'init', function()
        {
            $domain = $this->getTextDomain();
            $locale = apply_filters( 'plugin_locale', get_locale(), $domain );

            load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );
            load_plugin_textdomain( $domain, FALSE, plugin_basename( dirname( $this->plugin->getPluginFile() ) ) . '/languages/' );
        });
    }

    /**
     * Get the i18n text domain
     * @return mixed
     */
    public function getTextDomain()
    {
        return $this->text_domain;
    }

    /**
     * Set the Plugin's page templates
     * Only required when allowing the selection of templates
     * from the page/post edit screen in WP Admin
     * @param array $templates
     */
    public function setPageTemplates(array $templates)
    {
        $this->page_templates = array_merge($this->getPageTemplates(), $templates);
    }

    /**
     * Get the current theme page templates
     * @return array
     */
    public function getPageTemplates()
    {
        // adding support for theme templates to be merged and shown in dropdown
        $templates = wp_get_theme()->get_page_templates();
        $templates = array_merge( $templates, $this->page_templates );

        return $templates;
    }

    /**
     * Get the current theme directory
     * @return string
     */
    public function getThemeDirectory()
    {
        return get_theme_root() . '/' . get_template() . '/';
    }

    /**
     * Method called automatically on plugin activation
     * @return bool
     */
    public static function activate() { return false; }

    /**
     * i18n
     * Extend WP __() to automatically include the text domain
     * @param $text
     * @return string|void
     */
    public function __($text)
    {
        return __($text, $this->getTextDomain());
    }
} 