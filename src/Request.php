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
 * Class Responsible for Handling Requests from Front End Users
 *
 * This Class handles all functionality of the Plugin Requests
 * This Functionality includes Determining and Verifying user requests
 * and providing adequate responses
 *
 * @author  DeRavenedWriter <okerefe@gmail.com>
 * @package InpUserParser
 * @license https://www.gnu.org/licenses/gpl-2.0.txt
 */
class Request
{

    /**
     * @var string Contains Constant for Search str
     */
    const SEARCH_STR = 'searchStr';

    /**
     * @var string Contains Constant for column
     */
    const COLUMN = 'column';

    /**
     * @var string Contains Constant for Id
     */
    const ID = 'id';

    /**
     * @var string Contains Constant for RequestType
     */
    const REQUEST_TYPE = "requestType";

    /**
     * @var string Contains name of Request Table Twig Template
     */
    const TABLE_TEMPLATE ='table.twig.php';

    /**
     * @var array Contains array of all valid types of request
     */
    const REQUEST_TYPES =[
        'all',
        'id',
        'search',
    ];

    /**
     * @var string Contains name of the Type of Request.
     * which is the name of the function that handles it
     */
    public $requestResource;


    /**
     * @var bool State of the Request
     * If true, it means the Request is valid and builtUp
     */
    public $isBuilt = false;


    /**
     * Initializes all needed Hooks needed to detect Ajax Requests
     *
     * @return  void
     */
    public function init()
    {
        // ajax Ajax hook for logged-in users
        \add_action('wp_ajax_inpuserparser_hook', [$this, 'handle']);

        // ajax Ajax hook for non-logged-in users
        \add_action('wp_ajax_nopriv_inpuserparser_hook', [$this, 'handle']);
    }

    /**
     * Checks if a given set of values is Present and valid in an array
     *
     * This is used mainly to verify if some given set of values
     * Is present in the Submitted Post Form
     *
     * @param array $needles         String containing Values
     * @param array $haystack        Assoc Array
     *
     * @return bool
     */
    public function checkValidity(array $needles, array $haystack) : bool
    {
        foreach ($needles as $value) {
            if (!(isset($haystack[$value]) && !empty($haystack[$value]))) {
                return false;
            }
        }
        return true;
    }

    /**
     * Returns the Submitted Post Form
     *
     * @return array
     */
    public function post() : array
    {
        return $_POST;
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
     * Handles all Ajax Request
     *
     * This is the main function called by Wordpress when an ajax request
     * meant for this plugin and this class is sent.
     * It coordinates all other functions that validates the request
     * and gives back a response
     *
     * @return  void
     */
    public function handle()
    {

        \check_ajax_referer('inpuserparser_hook', 'nonce');
        try {
            $this->buildUp();
            $callback = $this->requestResource;
            echo \json_encode(['success' => 'true', 'reply' => $this->$callback()]);
        } catch (\InvalidArgumentException $e) {
            echo \json_encode(['success' => 'false', 'reply' => $e->getMessage()]);
        } catch (InpUserParserException $e) {
            echo \json_encode(['success' => 'false', 'reply' => $e->getMessage()]);
        } finally {
            \wp_die();
        }
    }


    /**
     * Builds up and validates Request
     *
     * This Functions Coordinates the action of  validating
     * the Integrity of every request
     *
     * @return  void
     */
    public function buildUp()
    {
        if (!$this->checkValidity([self::REQUEST_TYPE], $this->post())) {
            throw new \InvalidArgumentException("Error in Request Parameters");
        }
        if (!in_array($this->post()[self::REQUEST_TYPE], self::REQUEST_TYPES)) {
            throw new \InvalidArgumentException("Error in Request Parameters");
        }
        $validateFunction = $this->post()[self::REQUEST_TYPE] . "Validity";
        if (!$this->$validateFunction()) {
            throw new \InvalidArgumentException("Error in Request Parameters");
        }

        $this->requestResource = $this->post()[self::REQUEST_TYPE];
        $this->isBuilt = true;
    }

    /**
     * Checks Validity of "all" request
     *
     * @return  bool
     */
    public function allValidity() : bool
    {
        return true;
    }

    /**
     * Checks Validity of "id" request
     *
     * @return  bool
     */
    public function idValidity() : bool
    {
        return $this->checkValidity([self::ID], $this->post());
    }

    /**
     * Checks Validity of "search" request
     *
     * @return  bool
     */
    public function searchValidity() : bool
    {
        return $this->checkValidity(
            [
                self::SEARCH_STR,
                self::COLUMN,
            ],
            $this->post()
        );
    }


    /**
     * Returns a given User by searching for it's Id
     *
     * This Functions handles the Delivery of every Id Based Request
     * It searches for a User by It's Id
     *
     * @throws InpUserParserException  Exception is thrown. would be caught by caller
     * @return User
     */
    public function id(): User
    {
        return $this->userGen()->userById((int) $this->post()['id']);
    }

    /**
     * Returns an Instance of the UserGenerator Class
     *
     * @return UserGenerator
     */
    public function userGen() : UserGenerator
    {
        return new UserGenerator();
    }


    /**
     * Formats and Change first character to uppercase
     *
     * @param string $str          String to work on
     *
     * @return  string
     */
    public function ucFields(string $str): string
    {
        return Helpers::ucFields($str);
    }

    /**
     * Searches For Users by a given Search str and column
     *
     * This Functions handles the Delivery of every Search Based Request
     * It searches for a User by a given search str and associated Column
     *
     * @throws InpUserParserException
     * @return string
     */
    public function search(): array
    {
        $users = $this->userGen()->search(
            $this->post()[self::SEARCH_STR],
            $this->post()[self::COLUMN]
        );

        if (empty($users)) {
            $error = "Search param '{$this->post()[self::SEARCH_STR]}' Does not Match Any "
                . $this->ucFields($this->post()[self::COLUMN]);
            return ['searchSuccess' => false, 'error' => $error];

        }
        return ['searchSuccess' => true, 'users' => $users, 'columns' => $this->visibleColumns()];
    }

    /**
     * Returns all Users
     *
     * @throws InpUserParserException
     * @return string
     */
    public function all(): array
    {
        $users = $this->userGen()->allUsers();
        return ['users' => $users, 'columns' => $this->visibleColumns()];
        //return $this->generateTable($users);
    }


    /**
     * Returns all Visible Columns from Settings
     *
     * @return array
     */
    public function visibleColumns()
    {
        return (new Settings())->visibleColumns();
    }

}
