<?php declare(strict_types = 1);
/*
Plugin Name: InpUserParser
Description: Parse and Perform Different Search Transactions From a custom REST API endpoint.
Plugin URI:  https://github.com/Okerefe/InpuserParser
Author: Umukoro Okerefe
Text Domain: inpuserparser
Domain Path: /lang
Author URI: https://deravenedwriter.com/
Version:     1.0
License: GPL v2 or later
License URI:  https://www.gnu.org/licenses/gpl-2.0.txt
*/

// Exit if File is called Directly
if (!defined('ABSPATH')) {
    exit;
}

//Load Required Dependencies with autoload
require 'vendor/autoload.php';


//Load and Initialize Setting Functionality if user is an admin
if (\is_admin()) {
    $settings = new \InpUserParser\Settings();
    add_action('init', [$settings, 'init']);
    register_uninstall_hook(__FILE__, [$settings, 'uninstall']);
}

//We load the init method of the InpUserPage Class,
//which activates all hooks and functionalities for displaying the Page
add_action('init', [(new \InpUserParser\InpUserParserPage()), 'init']);


//We load the init method of the Request class
//which activates all hooks and functionalities for Processing all Ajax Requests
add_action('init', [(new \InpUserParser\Request()), 'init']);
