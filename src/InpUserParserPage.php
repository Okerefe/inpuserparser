<?php declare(strict_types=1);
# -*- coding: utf-8 -*-
/*
 * This file is part of the InpUserParser Wordpress Plugin
 *
 * (c) DeRavenedWriter
 *
 */

namespace InpUserParser;

use Twig;

/**
 * Class Responsible for Displaying InpUserParser Front End display page
 *
 * This Class co-ordinates all functionality involved with displaying Front Page including
 * Parsing associated request, and generating the page for the request
 *
 * @author  DeRavenedWriter <okerefe@gmail.com>
 * @package InpUserParser
 * @license https://www.gnu.org/licenses/gpl-2.0.txt
 */
class InpUserParserPage
{

    /**
     * @var array Associative Array Containing Properies of Page to be built.
     */

    protected $pageProperties;

    /**
     * @var bool Stores boolean state weather or not a User can manage options.
     */
    protected $canManageOptions;

    /**
     * @var bool If or not the page is built
     */
    protected $isBuilt = false;

    /**
     * @var string Contains Constant of Query Var
     */
    const QUERY_VAR = 'inpuserparser';

    /**
     * @var string Contains Name of InpUserParser Front Page Template
     */
    const PAGE_TEMPLATE ='inpuserparserpage.twig.php';

    /**
     * Initializes all needed Hooks needed to Parse Request and intercept associated ones
     *
     * @return  void
     */
    public function init()
    {
        \add_action('init', [$this, 'addRewriteRule']);
        \add_action('plugins_loaded', [$this, 'loadTextDomain']);
        \add_action('parse_request', [$this, 'parseRequest']);
        \add_filter('query_vars', [$this, 'inputQueryVars']);
    }

    /**
     * Formats and Change first character to uppercase
     *
     * @param   string $field         String to work on
     *
     * @return  string
     */
    public function ucField(string $field) : string
    {
        return Helpers::ucFields($field);
    }

    /**
     * Triggers the loading of the languages dir
     *
     * @return  void
     */
    public function loadTextDomain()
    {
        \load_plugin_textdomain('inpuserparser', false, \plugin_dir_path(__FILE__) . 'lang/');
    }

    /**
     * Adds rewrite rule to Wordpress to intercept request to InpUserParser
     *
     * @return  void
     */
    public function addRewriteRule()
    {
        \add_rewrite_rule('^' . self::QUERY_VAR . '/?$', 'index.php?' . self::QUERY_VAR . '=1', 'top');
    }

    /**
     * Adds Query Var for InpUserParser Plugin to Wordpress array
     *
     * @param   array $queryVars
     *
     * @return  array
     */
    public function inputQueryVars(array $queryVars): array
    {
        $queryVars[] = self::QUERY_VAR;
        return $queryVars;
    }

    /**
     * Exit's Request
     *
     * @return  void
     */
    public function endRequest()
    {
        exit();
    }

    /**
     * Parse Wordpress Request and Intercept's the one for InpUserParser Plugin
     *
     * @param Object $wp    Wordpress Environment Instance
     *
     * @throws  InpUserParserException
     * @return  void
     */
    public function parseRequest(Object &$wp)
    {
        if (\array_key_exists('inpuserparser', $wp->query_vars)) {
            $this->loadPage();
            $this->endRequest();
        }
    }

    /**
     * Echoes Out InpUserParser Front End Page
     *
     * @throws InpUserParserException
     * @return  void
     */
    public function loadPage()
    {
        // Output won't be escapes cause it's various parts
        // Have been escaped while building up
        echo $this->generatePage();
    }

    /**
     * Returns Link to InpUserParser Settings Page
     *
     * @return  string
     */
    public function settingsLink(): string
    {
        return (new Settings())->settingsLink();
    }

    /**
     * Builds Up InpUserParser Front End display page
     *
     * @return  void
     */
    public function buildUp()
    {
        $this->pageProperties['scriptUrl'] =
            \esc_url(\plugins_url('/../public/js/min/script.js', __FILE__));
        $this->pageProperties['styleUrl'] =
            \esc_url(\plugins_url('/../public/css/bootstrap.min.css', __FILE__));
        $this->pageProperties['nonce'] =
            \esc_attr(\wp_create_nonce('inpuserparser_hook'));
        $this->pageProperties['ajaxUrl'] = \esc_url(\admin_url('admin-ajax.php')); //esc url
        $this->pageProperties['heading'] = \esc_html__('InpUserParser', 'inpuserparser');
        $this->pageProperties['viewSearchText'] =
            \esc_html__('View and Search for Users Details from: ', 'inpuserparser');
        $this->pageProperties['hook'] = "inpuserparser_hook";

        //settings from InpUserParser\Settings::getSettingsLink()
        $this->pageProperties['settingsText'] = '<p>' . \esc_html__('Visit InpUserParser', 'inpuserparser')
            . ' ' . $this->settingsLink() . '</p>';

        $this->pageProperties['searchByText'] = \esc_html__("Search By:", 'inpuserparser');

        $this->canManageOptions = \current_user_can('manage_options');

        $this->isBuilt = true;
    }

    /**
     * Wrapper Function to return count of SearchFields
     **
     * @return  int
     */
    public function searchFieldCount() : int
    {
        return count($this->searchFields());
    }

    /**
     * Returns Value of the CanManageOptions variable
     **
     * @return  boolean
     */
    public function canManageOptions() : bool
    {
        return $this->canManageOptions;
    }

    /**
     * Returns Requested Properties of the Page
     *
     * @param String $field    Page Property been requested.
     *
     * @return  string
     */
    public function pageProperty(string $field) : string
    {
        if (array_key_exists($field, $this->pageProperties)) {
            return $this->pageProperties[$field];
        }
    }

    /**
     * Returns array of Search Fields to be used
     *
     * @return  array
     */
    public function searchFields(): array
    {
        return (new Settings())->visibleSearchFields();
    }

    /**
     * Returns a ready instance of the Twig Environment with templates Dir Loaded
     *
     * @return  Twig\Environment
     */
    public function templateEngine() : Twig\Environment
    {
        $loader = new Twig\Loader\FilesystemLoader(__DIR__.'/templates');
        return new Twig\Environment($loader);
    }

    /**
     * Returns the Generated InpUserParser Front Page
     *
     * @throws InpUserParserException
     * @return  string
     */
    public function generatePage() : string
    {
        $this->buildUp();

        try {
            return $this->templateEngine()->render(self::PAGE_TEMPLATE, ['page' => $this]);
        } catch (Twig\Error\Error $error) {
            throw new InpUserParserException(
                "Failed Generating InpUserParser Page with Error: " . $error->getMessage()
            );
        }
    }
}
