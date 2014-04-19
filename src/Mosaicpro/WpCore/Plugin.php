<?php namespace Mosaicpro\WpCore;

class Plugin extends PluginGeneric
{
    public function __construct($prefix)
    {
        $this->setPrefix($prefix);
    }
}