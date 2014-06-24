<?php
/*
Plugin Name: WP Core Library
Plugin URI: http://mosaicpro.biz
Description: WordPress Development Utility Toolkit
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