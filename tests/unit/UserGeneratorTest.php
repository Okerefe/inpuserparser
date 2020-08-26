<?php declare(strict_types=1);
# -*- coding: utf-8 -*-
/*
 * This file is part of the InpUserParser Wordpress Plugin
 *
 * (c) DeRavenedWriter
 *
 */

namespace InpUserParser;

use Brain\Monkey\Functions;
use Mockery;

/**
 * @author  DeRavenedWriter <deravenedwriter@gmail.com>
 * @package InpUserParser
 * @license https://www.gnu.org/licenses/gpl-2.0.txt
 */
final class UserGeneratorTest extends InpUserParserTest
{

    /** @test */
    public function ifUsersJsonReturnsUsersFromTransient()
    {
        Functions\expect('get_transient')
            ->once()
            ->with(UserGenerator::USERS_TRANSIENT)
            ->andReturn('BrainMonkeyRocks');

        $this->assertSame("BrainMonkeyRocks", (new UserGenerator())->usersJson());
    }

    /** @test */
    public function ifUsersJsonReturnsUsersFromWebCall()
    {
        $userGen = $this->getMockBuilder(UserGenerator::class)
            ->onlyMethods(['fetchFromWeb'])
            ->getMock();

        $userGen->expects($this->once())
                ->method('fetchFromWeb')
                ->with(UserGenerator::USERS_FETCH_URL)
                ->willReturn(['body' => "howdy", 'maxAge' => "amfine"]);

        Functions\expect('get_transient')
            ->once()
            ->with(UserGenerator::USERS_TRANSIENT)
            ->andReturn(false);

        Functions\expect('set_transient')
            ->once()
            ->with(UserGenerator::USERS_TRANSIENT, 'howdy', 'amfine');

        $this->assertSame("howdy", $userGen->usersJson());
    }

    /** @test */
    public function ifUsersJsonThrowsException()
    {
        $userGen = $this->getMockBuilder(UserGenerator::class)
            ->onlyMethods(['fetchFromWeb'])
            ->getMock();

        $userGen->expects($this->once())
            ->method('fetchFromWeb')
            ->with(UserGenerator::USERS_FETCH_URL)
            ->willThrowException(new InpUserParserException());

        Functions\expect('get_transient')
            ->once()
            ->with(UserGenerator::USERS_TRANSIENT)
            ->andReturn(false);

        $this->expectException('InpUserParser\\InpUserParserException');
        $userGen->usersJson();
    }


    /** @test */
    public function ifAllUsersReturnsUsers()
    {
        $userGen = $this->getMockBuilder(UserGenerator::class)
            ->onlyMethods(['usersJson', 'extractUsersFromJsonObject'])
            ->getMock();

        $userGen->expects($this->once())
            ->method('usersJson')
            ->willReturn('[{"id": 1,"name": "Leanne Graham"}]');

        $userGen->expects($this->once())
            ->method('extractUsersFromJsonObject')
            ->willReturn([$this->user()]);

        $this->assertInstanceOf(User::class, $userGen->allUsers()[0]);
    }

    /** @test */
    public function extractUsersFromJsonObject()
    {
        $userObject = [];
        $userObject[] = json_decode($this->userJson());

        $this->assertInstanceOf(
            User::class,
            (new UserGenerator())->extractUsersFromJsonObject($userObject)[0]
        );
    }

    /** @test */
    public function ifFetchFromWebReturnsResponse()
    {

        $userGen = $this->getMockBuilder(UserGenerator::class)
            ->onlyMethods(['cacheMaxAge'])
            ->getMock();

        $userGen->expects($this->once())
            ->method('cacheMaxAge')
            ->with('header')
            ->willReturn(10);

        Functions\expect('home_url')
            ->once()
            ->andReturn('blank');

        Functions\expect('wp_safe_remote_get')
            ->once()
            ->with('someargs', Mockery::type('array'))
            ->andReturn('webresponse');

        Functions\expect('is_wp_error')
            ->once()
            ->with('webresponse')
            ->andReturn(false);

        Functions\expect('wp_remote_retrieve_body')
            ->once()
            ->with('webresponse')
            ->andReturn('howfar');

        Functions\expect('wp_remote_retrieve_header')
            ->once()
            ->with('webresponse', 'cache-control')
            ->andReturn('header');

        $this->assertSame(['body' => 'howfar', 'maxAge' => 10], $userGen->fetchFromWeb('someargs'));
    }


    /** @test */
    public function ifFetchFromWebThrowsException()
    {
        Functions\expect('home_url')
            ->once()
            ->andReturn('blank');

        Functions\expect('is_wp_error')
            ->once()
            ->with('webresponse')
            ->andReturn(true);

        Functions\expect('wp_safe_remote_get')
            ->once()
            ->with('someargs', Mockery::type('array'))
            ->andReturn('webresponse');

        $this->expectException('InpUserParser\\InpUserParserException');
        (new UserGenerator())->fetchFromWeb('someargs');
    }

    /** @test */
    public function ifGetUserByIdReturnsUser()
    {
        $userGen = $this->getMockBuilder(UserGenerator::class)
            ->onlyMethods(['fetchFromWeb', 'extractUsersFromJsonObject'])
            ->getMock();

        $userGen->expects($this->once())
            ->method('extractUsersFromJsonObject')
            ->with([json_decode('{"stuff" : "goodstuff"}')])
            ->willReturn([$this->user()]);

        $userGen->expects($this->once())
            ->method('fetchFromWeb')
            ->with(UserGenerator::USERS_FETCH_URL. '/100')
            ->willReturn(['body' => '{"stuff" : "goodstuff"}']);

        $this->assertInstanceOf(
            User::class,
            $userGen->userById(100)
        );
    }

    /** @test */
    public function ifGetUserByIdThrowsException()
    {
        $userGen = $this->getMockBuilder(UserGenerator::class)
            ->onlyMethods(['fetchFromWeb'])
            ->getMock();

        $userGen->expects($this->once())
            ->method('fetchFromWeb')
            ->with(UserGenerator::USERS_FETCH_URL . "/100")
            ->willThrowException(new InpUserParserException());

        $this->expectException('InpUserParser\\InpUserParserException');
        $userGen->userById(100);
    }


    /** @test */
    public function cacheMaxAge()
    {
        $this->assertSame(43200, (new UserGenerator())->cacheMaxAge('max-age=43200'));
    }

    /** @test */
    public function searchReturnsEmpty()
    {
        $userGen = $this->getMockBuilder(UserGenerator::class)
            ->onlyMethods(['allUsers'])
            ->getMock();

        $userGen->expects($this->once())
            ->method('allUsers')
            ->willReturn([$this->user()]);

        $this->assertSame([], $userGen->search('doesntexist', 'id'));
    }

    /** @test */
    public function searchReturnsValue()
    {
        $userGen = $this->getMockBuilder(UserGenerator::class)
            ->onlyMethods(['allUsers'])
            ->getMock();

        $userGen->expects($this->exactly(2))
            ->method('allUsers')
            ->willReturn([$this->user(), $this->user()]);

        $this->assertSame(2, count($userGen->search('Sincere@', 'email')));
        $this->assertInstanceOf(User::class, $userGen->search('Sincere@', 'email')[0]);
    }

    /** @test */
    public function searchThrowsExeption()
    {
        $userGen = $this->getMockBuilder(UserGenerator::class)
            ->onlyMethods(['allUsers'])
            ->getMock();

        $userGen->expects($this->exactly(1))
            ->method('allUsers')
            ->willThrowException(new InpUserParserException());


        $this->expectException('InpUserParser\\InpUserParserException');
        $userGen->search('Sincere@', 'email');
    }


}