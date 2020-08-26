<?php declare(strict_types=1);
# -*- coding: utf-8 -*-
/*
 * This file is part of the InpUserParser Wordpress Plugin
 *
 * (c) DeRavenedWriter
 *
 */

namespace InpUserParser;

/**
 * Class Responsible for Producing Instance and array of Instance of User Class
 *
 *
 * @author  DeRavenedWriter <deravenedwriter@gmail.com>
 * @package InpUserParser
 * @license https://www.gnu.org/licenses/gpl-2.0.txt
 */
class UserGenerator
{

    /**
     * @var string Contains Transient Constant for User
     */
    const USERS_TRANSIENT = 'inpuserparser_users';

    /**
     * @var string Contains URI for fetching user data
     */
    const USERS_FETCH_URL = 'https://jsonplaceholder.typicode.com/users';

    /**
     * Fetches a User By It's Id
     *
     * @param int $id Id Of Needed User
     *
     * @throws InpUserParserException
     * @return User
     */
    public function userById(int $id) : User
    {
        try {
            $json = $this->fetchFromWeb(self::USERS_FETCH_URL . "/{$id}")['body'];
            return $this->extractUsersFromJsonObject([\json_decode($json)])[0];
        } catch (InpUserParserException $e) {
            throw $e->setToUserError($id);
        }
    }

    /**
     * Fetches a User By It's Id
     *
     * @param string $uri Uri of Resource to access
     *
     * @throws InpUserParserException
     * @return array
     */
    public function fetchFromWeb($uri) : array
    {
        $args = [
            'method' => 'GET',
            'timeout' => 5,
            'user-agent' => 'InpUserParserPlugin: HTTP API; ' . \home_url(),
        ];

        $response = \wp_safe_remote_get($uri, $args);

        // Server Side Http Error Handling
        if (\is_wp_error($response)) {
            throw new InpUserParserException("Error While making Web Request");
        }
        $body = \wp_remote_retrieve_body($response);
        $maxAge = $this->cacheMaxAge(\wp_remote_retrieve_header($response, 'cache-control'));
        return ['body' => $body, 'maxAge' => $maxAge];
    }

    /**
     * Fetches a Json Values of all Users
     *
     * @throws InpUserParserException
     * @return string
     */
    public function usersJson() : string
    {
        $usersJson = \get_transient(self::USERS_TRANSIENT);
        if (!$usersJson) {
            try {
                $response = $this->fetchFromWeb(self::USERS_FETCH_URL);
                $usersJson = $response['body'];
                $maxAge = $response['maxAge'];
                \set_transient(self::USERS_TRANSIENT, $usersJson, $maxAge);
            } catch (InpUserParserException $e) {
                throw $e->setErrorMessage("Error While Fetching all Users Detail");
            }
        }
        return $usersJson;
    }

    /**
     * Constructs and returns an array of User Objects from a Json Encoded Object
     *
     * @param array $usersArrayObject   Array Object Of Users
     *
     * @return User[]
     */
    public function extractUsersFromJsonObject(array $usersArrayObject) : array
    {
        $usersArray = [];
        foreach ($usersArrayObject as $obj) {
            $usersArray[] = new User(
                (string)$obj->id,
                (string)$obj->name,
                (string)$obj->username,
                (string)$obj->email,
                (string)$obj->address->street,
                (string)$obj->address->suite,
                (string)$obj->address->city,
                (string)$obj->address->zipcode,
                (string)$obj->address->geo->lat,
                (string)$obj->address->geo->lng,
                (string)$obj->phone,
                (string)$obj->website,
                (string)$obj->company->name,
                (string)$obj->company->catchPhrase,
                (string)$obj->company->bs
            );
        }
        return $usersArray;
    }

    /**
     * Fetches, Constructs and returns an array of User Objects
     *
     * @throws InpUserParserException
     * @return User[]
     */
    public function allUsers() : array
    {
        $userJson = $this->usersJson();
        return $this->extractUsersFromJsonObject(\json_decode($userJson));
    }


    /**
     * Searches and Returns Max-age by Regex Search
     *
     * @param string $content       Content of Which to Parse
     *
     * @return int
     */
    public function cacheMaxAge(string $content) : int
    {
        \preg_match('/max-age=([0-9]+)/', $content, $match);
        return (int)$match[1];
    }


    /**
     * Searches and Returns Max-age by Regex Search
     *
     * @param string $searchStr     Search Str to search for
     * @param string $column        Column to Search from
     *
     * @throws InpUserParserException
     * @return User[]
     */
    public function search(string $searchStr, string $column) : array
    {
        try {
            $users = $this->allUsers();
            $matchedUsers = [];
            foreach ($users as $user) {
                if (\strpos(\strtolower($user->$column), \strtolower($searchStr)) !== false) {
                    $matchedUsers[] = $user;
                }
            }
            return $matchedUsers;
        } catch (InpUserParserException $e) {
            throw $e->setErrorMessage("Error in Fetching User Detail to Perform Search");
        }
    }
}
