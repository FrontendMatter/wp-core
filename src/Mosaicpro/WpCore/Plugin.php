<?php namespace Mosaicpro\WpCore;

/**
 * Class Plugin
 * @package Mosaicpro\WpCore
 */
class Plugin extends PluginGeneric
{
    /**
     * Create a new Plugin instance
     * @param $prefix
     */
    public function __construct($prefix)
    {
        $this->setPrefix($prefix);
    }
}