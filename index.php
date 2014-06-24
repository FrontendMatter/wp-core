<?php namespace Mosaicpro\WpCore;
/*
Plugin Name: WP Core Library
Plugin URI: http://mosaicpro.biz
Description: WordPress Development KIT
Version: 0.1.1
Author: MosaicPro
Author URI: http://mosaicpro.biz
*/

// If this file is called directly, exit.
if ( ! defined( 'WPINC' ) ) { die; }

// include the autoloader
require_once dirname(__FILE__) . '/../../../vendor/autoload.php';

// include the illuminate/support helpers
require_once dirname(__FILE__) . '/../../../vendor/illuminate/support/Illuminate/Support/helpers.php';

// a dummy class used to verify if this plugin was specifically de/activated from the WP admin
// a verification for the Mosaicpro\WpCore\PluginActivated class is required because when another plugin is using
// the shared autoloader from composer it will also autoload the wp-core libraries so we need a way to hard disable
// them when this plugin is deactivated from WP admin
class PluginActivated { }