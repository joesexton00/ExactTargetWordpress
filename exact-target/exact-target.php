<?php
/*
Plugin Name: Exact Target User Integration
Description: Allows integration between Wordpress user accounts and Exact Target.  When user's register with Wordpress they are added to Exact Target
Version: 1.0
Author: MoCo, Inc.
Author URI: http://www.bigideas.com
*/

$dir = plugin_dir_path( __FILE__ );
$url = plugin_dir_url( __FILE__ );

require_once $dir . '/framework/WpClassLoader.php';
$wpClassLoader = new WpClassLoader( $dir, $url );

