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
     * @param null $text_domain
     */
    public function __construct($prefix, $text_domain = null)
    {
        $this->setPrefix($prefix);
        $this->setTextDomain($text_domain);
    }
}