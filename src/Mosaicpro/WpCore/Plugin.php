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
}