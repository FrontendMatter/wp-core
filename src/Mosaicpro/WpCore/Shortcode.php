<?php namespace Mosaicpro\WpCore;

/**
 * Class Shortcode
 * @package Mosaicpro\WpCore
 */
abstract class Shortcode
{
    /**
     * Holds a Shortcode instance
     * @var
     */
    protected static $instance;

    /**
     * Constructor
     */
    public function __construct(){}

    /**
     * Get a Shortcode Singleton instance
     * @return static
     */
    public static function getInstance()
    {
        $class = get_called_class();
        if (!isset(self::$instance[$class]) || (isset(self::$instance[$class]) && is_null(self::$instance[$class])))
        {
            self::$instance[$class] = new $class;
        }
        return self::$instance[$class];
    }

    /**
     * Initialize the Shortcode
     */
    public static function init()
    {
        $instance = forward_static_call([get_called_class(), 'getInstance']);
        $instance->addShortcode();
        return $instance;
    }

    /**
     * Add the Shortcode to WP
     */
    abstract function addShortcode();
}