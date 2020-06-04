<?php declare(strict_types = 1);

namespace InpUserParser;

/*
 * This Class Handles the Presentation of the InpUserParser Public Page
 * */

class InpUserPage
{

    const QUERY_VAR = 'inpuserparser';

//    Add Necessary Wordpress Hooks to intercept request
    public static function init()
    {
        add_action('init', ['InpUserParser\InpUserPage', 'addRewriteRule']);
        add_filter('query_vars', ['InpUserParser\InpUserPage', 'inputQueryVars']);
        add_action('parse_request', ['InpUserParser\InpUserPage', 'parseRequest']);
    }

    public static function loadTextDomain()
    {
        load_plugin_textdomain('inpuserparser', false, plugin_dir_path(__FILE__) . 'languages/');
    }

    public static function addRewriteRule()
    {
        add_rewrite_rule('^'.self::QUERY_VAR . '/?$', 'index.php?'.self::QUERY_VAR.'=1', 'top');
    }

    public static function inputQueryVars(array $queryVars) : array
    {
        $queryVars[] = self::QUERY_VAR;
        return $queryVars;
    }

    public static function parseRequest(object &$wp)
    {
        if (array_key_exists('inpuserparser', $wp->query_vars)) {
            self::loadPage();
            exit();
        }
    }

    public static function loadPage()
    {
        // Import Page Template
        require_once plugin_dir_path(__FILE__) . 'inpuserpagetpl.php';
    }
}
