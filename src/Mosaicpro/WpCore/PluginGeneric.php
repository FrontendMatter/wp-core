<?php namespace Mosaicpro\WpCore;

use Mosaicpro\Core\IoC;

/**
 * Class PluginGeneric
 * @package Mosaicpro\WpCore
 */
class PluginGeneric
{
    /**
     * Holds the plugin prefix
     * @var
     */
    protected $prefix;

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
     * Method called automatically on plugin activation
     * @return bool
     */
    public static function activate() { return false; }
} 