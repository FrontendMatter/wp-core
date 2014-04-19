<?php namespace Mosaicpro\WpCore;

use Mosaicpro\Core\IoC;

class PluginGeneric
{
    protected $prefix;

    protected $plugin;

    public function __construct()
    {
        $this->plugin = IoC::getContainer('plugin');
        $this->prefix = $this->plugin->getPrefix();
    }

    public static function init()
    {
        return new static();
    }

    public function getPrefix()
    {
        return $this->prefix;
    }

    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }
} 