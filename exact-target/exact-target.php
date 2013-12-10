<?php
/*
Plugin Name: Exact Target User Integration
Description: Allows integration between Wordpress user accounts and Exact Target.  When user's register with Wordpress they are added to Exact Target
Version: 1.0
Author: MoCo, Inc.
Author URI: http://www.bigideas.com
*/

define( 'XT_FILE_PATH', plugin_dir_path(__FILE__) );

require_once XT_FILE_PATH . '/inc/XtAdmin.php';
require_once XT_FILE_PATH . '/inc/XtUserProfile.php';
require_once XT_FILE_PATH . '/inc/XtUpdate.php';

$xtAdmin       = new XtAdmin( XT_FILE_PATH );
$xtUserProfile = new XtUserProfile( XT_FILE_PATH );
$xtUpdate      = new XtUpdate( XT_FILE_PATH );
