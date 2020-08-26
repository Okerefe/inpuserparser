<?php declare(strict_types=1);
# -*- coding: utf-8 -*-
/*
 * This file is part of the InpUserParser Wordpress Plugin
 *
 * (c) DeRavenedWriter
 *
 */

namespace InpUserParser;

use PHPUnit\Framework\TestCase;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Brain\Monkey;

/**
 * @author  DeRavenedWriter <deravenedwriter@gmail.com>
 * @package InpUserParser
 * @license https://www.gnu.org/licenses/gpl-2.0.txt
 */
class InpUserParserTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();
    }

    protected function tearDown(): void
    {
        Monkey\tearDown();
        parent::tearDown();
    }


    /** @test */
    public function preventTestWarning()
    {
        $this->assertSame('idontwantwarnings', 'idontwantwarnings');
    }


    //..........................Commonly Used Methods and mocks

    //    Returns Mock user Json String
    public function userJson() : string
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

    public function dataForUserValidity()
    {
        $user = $this->user();
        return [
            ['1', $user->id],
            ['Leanne Graham', $user->name],
            ['Bret', $user->username],
            ['Sincere@april.biz', $user->email],
            ['Kulas Light', $user->street],
            ['Apt. 556', $user->suite],
            ['Gwenborough', $user->city],
            ['92998-3874', $user->zipcode],
            ['-37.3159', $user->lat],
            ['81.1496', $user->lng],
            ['1-770-736-8031 x56442', $user->phone],
            ['hildegard.org', $user->website],
            ['Romaguera-Crona', $user->companyName],
            ['Multi-layered client-server neural-net', $user->companyCatchPhrase],
            ['harness real-time e-markets', $user->companyBs],
        ];
    }
    //..........................End of Commonly Used Methods

}
