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
     * Holds a Mosaicpro\WpCore\Plugin instance
     * @var mixed
     */
    protected $plugin;

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
     * @return mixed
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * Set the Plugin prefix
     * @param $prefix
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
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
     * Get the i18n text domain
     * @return mixed
     */
    public function getTextDomain()
    {
        return $this->text_domain;
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