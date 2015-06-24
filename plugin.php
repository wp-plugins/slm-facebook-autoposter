<?php
/*
* Plugin Name:   Social Link Machine - Facebook Autoposter
* Version:       0.1.1
* Plugin URI:    http://www.maxvim.com/private/tools/slm.php?who=facebookslm
* Description:   Share your posts and pages on Facebook.
* Author:        Dr. Max V
* Author URI:    http://www.maxvim.com/private/tools/slm.php?who=facebookslm
*/

require_once(dirname(__FILE__).'/SLMFacebookPlugin.php');
if (class_exists('SLMFacebookPlugin')){
	$p = new SLMFacebookPlugin();
	register_activation_hook(__FILE__,array('SLMFacebookPlugin', 'activate'));
	register_deactivation_hook(__FILE__,array('SLMFacebookPlugin', 'deactivate'));
	register_uninstall_hook(__FILE__, array('SLMFacebookPlugin', 'uninstall'));
}

?>