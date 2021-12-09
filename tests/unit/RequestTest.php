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

/**
 * @author  DeRavenedWriter <okerefe@gmail.com>
 * @package InpUserParser
 * @license https://www.gnu.org/licenses/gpl-2.0.txt
 */
final class RequestTest extends InpUserParserTest
{


    //........Data Providers

    public function dataForCheckValidityReturnFalse()
    {
        return [
            [
                ['hello','hy'],['how' => 'hey'],
            ],
            [
                ['hello','hy'],[],
            ],
        ];
    }

    public function dataForRequestTypes()
    {
        return [
            ['id'],
            ['all'],
            ['search'],
        ];
    }

    public function dataForTableTemplateBug()
    {
        return [
            ['name'],
            ['username'],
            ['email'],
            ['street'],
            ['suite'],
            ['city'],
            ['zipcode'],
            ['lat'],
            ['lng'],
            ['phone'],
            ['website'],
            ['companyName'],
            ['companyCatchPhrase'],
            ['companyBs'],
        ];
    }

    //...........End of Data Providers


    /** @test
     * @dataProvider dataForCheckValidityReturnFalse
     */
    public function ifCheckValidityReturnsFalse($needle, $haystack)
    {
        $request = new Request();
        $this->assertFalse($request->checkValidity($needle, $haystack));
    }

    /** @test */
    public function ifCheckValidityReturnsTrue()
    {
        $request = new Request();
        $this->assertTrue(
            $request->checkValidity(
                [
                    'hello',
                    'hy',
                ],
                [
                    'hello' => 'hey',
                    'hy'    => 'how',
                ]
            )
        );
    }

    /** @test */
    public function ifBuildUpThrowsExceptionCauseOfCheckValidity()
    {
        $request = $this->getMockBuilder(Request::class)
            ->onlyMethods(['checkValidity'])
            ->getMock();

        $request->expects($this->once())
            ->method('checkValidity')
            ->willReturn(false);

        $this->expectException('InvalidArgumentException');
        $request->buildUp();
    }

    /** @test */
    public function ifBuildUpThrowsExceptionCauseOfInArrayFalse()
    {
        $request = $this->getMockBuilder(Request::class)
            ->onlyMethods(['checkValidity', 'post'])
            ->getMock();

        $request->expects($this->once())
            ->method('checkValidity')
            ->willReturn(true);

        $request->expects($this->exactly(2))
            ->method('post')
            ->willReturn(['requestType' => 'pleasejustfail']);

        $this->expectException('InvalidArgumentException');
        $request->buildUp();
    }

    /** @test
     *  @dataProvider dataForRequestTypes
     */
    public function ifBuildUpThrowsExceptionCauseOfValidityFunction($type)
    {
        $request = $this->getMockBuilder(Request::class)
            ->onlyMethods(['checkValidity', 'post', "{$type}", "{$type}" . 'Validity'])
            ->getMock();

        $request->expects($this->once())
            ->method('checkValidity')
            ->willReturn(true);

        $request->expects($this->exactly(3))
            ->method('post')
            ->willReturn(['requestType' => $type]);

        $request->expects($this->once())
            ->method("{$type}" . 'Validity')
            ->willReturn(false);

        $this->expectException('InvalidArgumentException');
        $request->buildUp();
    }

    /** @test
     *  @dataProvider dataForRequestTypes
     */
    public function ifBuildUpRunsSuccessfully($type)
    {
        $request = $this->getMockBuilder(Request::class)
            ->onlyMethods(['checkValidity', 'post', "{$type}", "{$type}" . 'Validity'])
            ->getMock();

        $request->expects($this->once())
            ->method('checkValidity')
            ->willReturn(true);

        $request->expects($this->exactly(4))
            ->method('post')
            ->willReturn(['requestType' => $type]);

        $request->expects($this->once())
            ->method("{$type}" . 'Validity')
            ->willReturn(true);

        $request->buildUp();
        $this->assertSame(true, $request->isBuilt);
    }

    /** @test */
    public function ifHandleCatchesInvalidException()
    {
        $request = $this->getMockBuilder(Request::class)
            ->onlyMethods(['buildUp'])
            ->getMock();

        $request->expects($this->once())
            ->method('buildUp')
            ->willThrowException(new \InvalidArgumentException('Yea, am a Test so you should see me.'));

        Functions\expect('check_ajax_referer')
            ->once()
            ->with('inpuserparser_hook', 'nonce')
            ->andReturn(true);

        Functions\expect('wp_die')
            ->once()
            ->andReturn(true);

        $request->handle();

    }

    /** @test
     *  @dataProvider dataForRequestTypes
     */
    public function ifHandleCatchesInpUserParserException($type)
    {
        $request = $this->getMockBuilder(Request::class)
            ->onlyMethods(['buildUp', $type])
            ->getMock();
        $request->requestResource = $type;

        $request->expects($this->once())
            ->method('buildUp')
            ->willReturn(true);

        $request->expects($this->once())
            ->method($type)
            ->willThrowException(new InpUserParserException('Ya, Just a Test Msg meant to be seen'));

        Functions\expect('check_ajax_referer')
            ->once()
            ->with('inpuserparser_hook', 'nonce')
            ->andReturn(true);

        Functions\expect('wp_die')
            ->once()
            ->andReturn(true);

        $request->handle();
    }

    /** @test
     *  @dataProvider dataForRequestTypes
     */
    public function ifHandleCatchesRunsSuccessfully($type)
    {
        $request = $this->getMockBuilder(Request::class)
            ->onlyMethods(['buildUp', $type])
            ->getMock();
        $request->requestResource = $type;

        $request->expects($this->once())
            ->method('buildUp')
            ->willReturn(true);

        $request->expects($this->once())
            ->method($type);

        Functions\expect('check_ajax_referer')
            ->once()
            ->with('inpuserparser_hook', 'nonce')
            ->andReturn(true);

        Functions\expect('wp_die')
            ->once()
            ->andReturn(true);

        $request->handle();
    }

    /** @test */
    public function searchValidityFunctionality()
    {
        $request = $this->getMockBuilder(Request::class)
            ->onlyMethods(['checkValidity', 'post'])
            ->getMock();

        $request->expects($this->once())
            ->method('checkValidity')
            ->with(['searchStr', 'column'], ['testingstuffs'])
            ->willReturn(true);

        $request->expects($this->once())
            ->method('post')
            ->willReturn(['testingstuffs']);

        $this->assertTrue($request->searchValidity());
    }

    /** @test */
    public function idValidityFunctionality()
    {
        $request = $this->getMockBuilder(Request::class)
            ->onlyMethods(['checkValidity', 'post'])
            ->getMock();

        $request->expects($this->once())
            ->method('checkValidity')
            ->with(['id'], ['testingstuffs'])
            ->willReturn(true);

        $request->expects($this->once())
            ->method('post')
            ->willReturn(['testingstuffs']);

        $this->assertTrue($request->idValidity());
    }

    /** @test */
    public function idThrowsException()
    {
        $userGen = $this->getMockBuilder(UserGenerator::class)
            ->onlyMethods(['userById'])
            ->getMock();

        $request = $this->getMockBuilder(Request::class)
            ->onlyMethods(['userGen', 'post'])
            ->getMock();

        $userGen->expects($this->once())
            ->method('userById')
            ->with(100)
            ->willThrowException(new InpUserParserException());

        $request->expects($this->once())
            ->method('userGen')
            ->willReturn($userGen);

        $request->expects($this->once())
            ->method('post')
            ->willReturn(['id' => 100]);


        $this->expectException('InpUserParser\\InpUserParserException');
        $request->id();
    }

    /** @test */
    public function idPasses()
    {
        $user = $this->user();

        $userGen = $this->getMockBuilder(UserGenerator::class)
            ->onlyMethods(['userById'])
            ->getMock();

        $request = $this->getMockBuilder(Request::class)
            ->onlyMethods(['userGen', 'post'])
            ->getMock();


        $userGen->expects($this->once())
            ->method('userById')
            ->with(100)
            ->willReturn($user);

        $request->expects($this->once())
            ->method('userGen')
            ->willReturn($userGen);

        $request->expects($this->once())
            ->method('post')
            ->willReturn(['id' => 100]);

        $response = $request->id();
        $this->assertSame('Leanne Graham', $response->name);
    }

    /** @test */
    public function searchThrowsException()
    {
        $userGen = $this->getMockBuilder(UserGenerator::class)
            ->onlyMethods(['search'])
            ->getMock();

        $request = $this->getMockBuilder(Request::class)
            ->onlyMethods(['userGen', 'post'])
            ->getMock();

        $userGen->expects($this->once())
            ->method('search')
            ->with('Sincere@', 'email')
            ->willThrowException(new InpUserParserException());

        $request->expects($this->once())
            ->method('userGen')
            ->willReturn($userGen);

        $request->expects($this->exactly(2))
            ->method('post')
            ->willReturn(['searchStr' => 'Sincere@', 'column' => 'email']);

        $this->expectException('InpUserParser\\InpUserParserException');
        $request->search();
    }

    /** @test */
    public function searchDoesntMatch()
    {
        $userGen = $this->getMockBuilder(UserGenerator::class)
            ->onlyMethods(['search'])
            ->getMock();

        $request = $this->getMockBuilder(Request::class)
            ->onlyMethods(['userGen', 'post'])
            ->getMock();

        $userGen->expects($this->once())
            ->method('search')
            ->with('Sincere@', 'email')
            ->willReturn([]);

        $request->expects($this->once())
            ->method('userGen')
            ->willReturn($userGen);

        $request->expects($this->exactly(4))
            ->method('post')
            ->willReturn(['searchStr' => 'Sincere@', 'column' => 'email']);

        $response = $request->search();
        $this->assertArrayHasKey('searchSuccess', $response);
        $this->assertNotTrue($response['searchSuccess']);
        $this->assertStringContainsString("Search param 'Sincere@' Does not Match Any Email", $response['error']);

    }


    /** @test */
    public function searchRunsSuccessfully()
    {
        $userGen = $this->getMockBuilder(UserGenerator::class)
            ->onlyMethods(['search'])
            ->getMock();

        $request = $this->getMockBuilder(Request::class)
            ->onlyMethods(['userGen', 'post', 'visibleColumns'])
            ->getMock();

        $userGen->expects($this->once())
            ->method('search')
            ->with('Sincere@', 'email')
            ->willReturn([$this->user()]);

        $request->expects($this->once())
            ->method('userGen')
            ->willReturn($userGen);

        $request->expects($this->once())
            ->method('visibleColumns')
            ->willReturn(['field1', 'field2']);

        $request->expects($this->exactly(2))
            ->method('post')
            ->willReturn(['searchStr' => 'Sincere@', 'column' => 'email']);

        $response = $request->search();
        $this->assertArrayHasKey('users', $response);
        $this->assertArrayHasKey('columns', $response);
        $this->assertArrayHasKey('searchSuccess', $response);
        $this->assertNotFalse($response['searchSuccess']);
        $this->assertSame(['field1', 'field2'], $response['columns']);
        $this->assertSame("Leanne Graham", $response['users'][0]->name);
    }

    /** @test */
    public function allThrowsException()
    {
        $userGen = $this->getMockBuilder(UserGenerator::class)
            ->onlyMethods(['allUsers'])
            ->getMock();

        $request = $this->getMockBuilder(Request::class)
            ->onlyMethods(['userGen'])
            ->getMock();

        $userGen->expects($this->once())
            ->method('allUsers')
            ->willThrowException(new InpUserParserException());

        $request->expects($this->once())
            ->method('userGen')
            ->willReturn($userGen);

        $this->expectException('InpUserParser\\InpUserParserException');
        $request->all();
    }


    /** @test */
    public function allRunsSuccessfully()
    {
        $userGen = $this->getMockBuilder(UserGenerator::class)
            ->onlyMethods(['allUsers'])
            ->getMock();

        $request = $this->getMockBuilder(Request::class)
            ->onlyMethods(['userGen', 'visibleColumns'])
            ->getMock();

        $userGen->expects($this->once())
            ->method('allUsers')
            ->willReturn([$this->user()]);

        $request->expects($this->once())
            ->method('userGen')
            ->willReturn($userGen);

        $request->expects($this->once())
            ->method('visibleColumns')
            ->willReturn(['field1', 'field2']);

        $response = $request->all();
        $this->assertArrayHasKey('users', $response);
        $this->assertArrayHasKey('columns', $response);
        $this->assertSame(['field1', 'field2'], $response['columns']);
        $this->assertSame("Leanne Graham", $response['users'][0]->name);

    }

}
