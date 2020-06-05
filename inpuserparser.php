<?php declare(strict_types = 1);
/*
Plugin Name: InpUserParser
Description: Parse and Perform Different Search Transactions From a custom REST API endpoint.
Plugin URI:  https://github.com/
Author: Umukoro Okerefe
Text Domain: inpuserparser
Domain Path: /languages
Author URI: https://deravenedwriter.com/
Version:     1.0
License: GPL v2 or later
License URI:  https://www.gnu.org/licenses/gpl-2.0.txt
*/

// Exit if File is called Directly
if (!defined('ABSPATH')) {
    exit;
}

require_once plugin_dir_path(__FILE__) . 'includes/settings.php';
require_once plugin_dir_path(__FILE__) . 'includes/inpuserpage.php';
require_once plugin_dir_path(__FILE__) . 'includes/request.php';
require_once plugin_dir_path(__FILE__) . 'includes/user.php';


if (is_admin()) {
    add_action('init', ['InpUserParser\Settings', 'init']);
    register_activation_hook(__FILE__, ['InpUserParser\Settings', 'install']);
    register_uninstall_hook(__FILE__, ['InpUserParser\Settings', 'uninstall']);
}

add_action('init', ['InpUserParser\InpUserPage', 'init']);

// ajax Ajax hook for logged-in users
add_action('wp_ajax_inpuserparser_hook', ['InpUserParser\Request', 'handle']);

// ajax Ajax hook for non-logged-in users
add_action('wp_ajax_nopriv_inpuserparser_hook', ['InpUserParser\Request', 'handle']);

add_action('plugins_loaded', ['InpUserParser\InpUserPage', 'loadTextDomain']);
