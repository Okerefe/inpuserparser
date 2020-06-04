<?php declare(strict_types=1);

namespace InpUserParser;

use PHPUnit\Framework\TestCase;

final class UserTest extends TestCase
{

//    Returns Mock user Json String
    public function usersJson() : string
    {
        return file_get_contents(__DIR__ . '/userjson.txt');
    }

//    Returns Mock User Object
    public function user() : object
    {
        return new User(
            '1',
            'Leanne Graham',
            'Bret',
            'Sincere@april.biz',
            'Kulas Light',
            'Apt. 556',
            'Gwenborough',
            '92998-3874',
            '-37.3159',
            '81.1496',
            '1-770-736-8031 x56442',
            'hildegard.org',
            'Romaguera-Crona',
            'Multi-layered client-server neural-net',
            'harness real-time e-markets'
        );
    }

    public function testGetUsersFromJsonObject()
    {
//        Create a Json Object array of Users from txt File
        $users = [];
        $users[] = json_decode($this->usersJson())[0];

//        Load a mock Array (with size 1) of users from a method of current test class
        $usersArray = [];
        $usersArray[] = $this->user();

//        Assert if return value of already instantiated $usersArray is the same when passed to getUsersFromJsonObject()
        $this->assertSame(
            $usersArray[0]->valueOf('name'),
            User::getUsersFromJsonObject($users)[0]->valueOf('name')
        );
    }

    public function testSearch()
    {
        $usersArray = [];
        $usersArray[] = $this->user();

        $this->assertSame(
            $usersArray[0]->valueOf('street'),
            User::search('lean', 'name', $usersArray)[0]->valueOf('street')
        );
    }

    public function testUsers()
    {
        $usersArray = [];
        $usersArray[] = $this->user();


        $this->assertInstanceOf(
            User::class,
            User::users($this->usersJson())[0]
        );
    }

    public function testIterateArray()
    {
        $this->assertArrayHasKey(
            'name',
            $this->user()->iterateArray()
        );
    }

    public function testCacheMaxAge()
    {
        $this->assertSame(
            120,
            User::cacheMaxAge('max-age=120')
        );
    }
}
