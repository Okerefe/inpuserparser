<?php declare(strict_types = 1);

namespace InpUserParser;

const USERS_FETCH_URL = 'https://jsonplaceholder.typicode.com/users';
const USERS_TRANSIENT = 'inpuserparser_users';

class User
{
    private $id;
    private $name;
    private $username;
    private $email;
    private $street;
    private $suite;
    private $city;
    private $zipcode;
    private $lat;
    private $lng;
    private $phone;
    private $website;
    private $companyName;
    private $companyCatchPhrase;
    private $companyBs;

    public function __construct(
        string $id,
        string $name,
        string $username,
        string $email,
        string $street,
        string $suite,
        string $city,
        string $zipcode,
        string $lat,
        string $lng,
        string $phone,
        string $website,
        string $companyName,
        string $companyCatchPhrase,
        string $companyBs
    ) {

        $this->id = $id;
        $this->name = $name;
        $this->username = $username;
        $this->email = $email;
        $this->street = $street;
        $this->suite = $suite;
        $this->city = $city;
        $this->zipcode = $zipcode;
        $this->lat = $lat;
        $this->lng = $lng;
        $this->phone = $phone;
        $this->website = $website;
        $this->companyName = $companyName;
        $this->companyCatchPhrase = $companyCatchPhrase;
        $this->companyBs = $companyBs;
    }

//    Returns Columns that Must be Visible
//    Most Used by the InpUserParser\Settings Class
    public static function defaultColumns() : array
    {
        return[
            'id',
            'name',
            'username',
        ];
    }

//    Returns Used Fields for Both Searches and Visible Colummns
    public static function usedFields() : array
    {
        return[
            'id',
            'name',
            'username',
            'email',
            'street',
            'phone',
            'website',
            'companyName',
        ];
    }

    /*
     * This Method Handles all Web Request with WP's apis
     * It Depends solely on external dependencies so tests are not provided Yet
     */
    public static function fetchResponse(string $url): array
    {
        $args = [
            'method' => 'GET',
            'timeout' => 5,
            'user-agent' => 'InpUserParserPlugin: HTTP API; '. home_url(),
        ];

        $response =  wp_safe_remote_get($url, $args);

        // Server Side Http Error Handling
        if (is_wp_error($response)) {
            echo json_encode(
                [
                    'success' => 'false',
                    'reply' => 'HTTP Request Error',
                ]
            );
            wp_die();
        }
        $body = wp_remote_retrieve_body($response);
        $maxAge = self::cacheMaxAge(wp_remote_retrieve_header($response, 'cache-control'));
        return ['body' => $body, 'maxAge' => $maxAge];
    }

    /*
     * Get Particular User Raw Json From Webcall by the Id provided.
     * Depends on fetchResponse() (tests are not provided)
     */
    public static function userJson(int $id) : string
    {
        $response = self::fetchResponse(USERS_FETCH_URL . '/' . $id);
        return (string) $response['body'];
    }

    /*
     * Get Users Raw Json From Webcall or Transients
     * Depends on either fetchResponse() or WP Transients
     */
    public static function usersJson() : string
    {
        $usersJson = get_transient(USERS_TRANSIENT);
        if (!$usersJson) {
            $response = self::fetchResponse(USERS_FETCH_URL);
            $usersJson = $response['body'];
            $maxAge = $response['maxAge'];
            set_transient(USERS_TRANSIENT, $usersJson, $maxAge);
        }
        return $usersJson;
    }

//    Dynamic Getter Function for all properties of the Class
    public function valueOf(string $param) : string
    {
        return (string) $this->$param;
    }

//    Returns an Associative array of objects properties and values
    public function iterateArray() : array
    {
        $userArray = [];
        foreach ($this as $var => $value) {
            $userArray[$var] = $value;
        }
        return $userArray;
    }

//    Performs a search on all given users for a particular string in a particular column
    public static function search(string $searchStr, string $column, array $users) : array
    {
        $matchedUsers = [];
        foreach ($users as $user) {
            if (strpos(strtolower($user->valueOf($column)), strtolower($searchStr)) !== false) {
                $matchedUsers[] = $user;
            }
        }
        return $matchedUsers;
    }

//    Loops Through Json decoded array and return a User Object
    public static function getUsersFromJsonObject(array $usersArrayObject) : array
    {
        $usersArray = [];
        foreach ($usersArrayObject as $obj) {
            $usersArray[] = new User(
                (string) $obj->id,
                (string) $obj->name,
                (string) $obj->username,
                (string) $obj->email,
                (string) $obj->address->street,
                (string) $obj->address->suite,
                (string) $obj->address->city,
                (string) $obj->address->zipcode,
                (string) $obj->address->geo->lat,
                (string) $obj->address->geo->lng,
                (string) $obj->phone,
                (string) $obj->website,
                (string) $obj->company->name,
                (string) $obj->company->catchPhrase,
                (string) $obj->company->bs
            );
        }
        return $usersArray;
    }

//     Returns an array of users from a json string
    public static function users(string $userJson) : array
    {
        if (!is_array(json_decode($userJson))) {
            $usersObjArray = [];
            $usersObjArray[] = json_decode($userJson);
            return self::getUsersFromJsonObject($usersObjArray);
        }
        return self::getUsersFromJsonObject(json_decode($userJson));
    }

//    Get the Max Age from the Cache-control parameter in the request header
    public static function cacheMaxAge(string $content) : int
    {
        preg_match('/max-age=([0-9]+)/', $content, $match);
        return (int) $match[1];
    }
}
