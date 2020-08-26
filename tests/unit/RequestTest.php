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
 * @author  DeRavenedWriter <deravenedwriter@gmail.com>
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
        $user = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['generateArray'])
            ->getMock();

        $userGen = $this->getMockBuilder(UserGenerator::class)
            ->onlyMethods(['userById'])
            ->getMock();

        $request = $this->getMockBuilder(Request::class)
            ->onlyMethods(['userGen', 'post'])
            ->getMock();


        $user->expects($this->once())
            ->method('generateArray')
            ->willReturn(['helloyou']);

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

        $this->assertSame(['helloyou'], $request->id());
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

        $this->assertStringContainsString("<p class='text-info'>", $request->search());
    }


    /** @test */
    public function searchRunsSuccessfully()
    {
        $userGen = $this->getMockBuilder(UserGenerator::class)
            ->onlyMethods(['search'])
            ->getMock();

        $request = $this->getMockBuilder(Request::class)
            ->onlyMethods(['userGen', 'post', 'generateTable'])
            ->getMock();

        $userGen->expects($this->once())
            ->method('search')
            ->with('Sincere@', 'email')
            ->willReturn([$this->user()]);

        $request->expects($this->once())
            ->method('userGen')
            ->willReturn($userGen);

        $request->expects($this->once())
            ->method('generateTable')
            ->with([$this->user()])
            ->willReturn("receivedusers");

        $request->expects($this->exactly(2))
            ->method('post')
            ->willReturn(['searchStr' => 'Sincere@', 'column' => 'email']);

        $this->assertSame("receivedusers", $request->search());
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
            ->onlyMethods(['userGen', 'generateTable'])
            ->getMock();

        $userGen->expects($this->once())
            ->method('allUsers')
            ->willReturn([$this->user()]);

        $request->expects($this->once())
            ->method('userGen')
            ->willReturn($userGen);

        $request->expects($this->once())
            ->method('generateTable')
            ->with([$this->user()])
            ->willReturn("receivedusers");
        $this->assertSame("receivedusers", $request->all());
    }


    /** @test */
    public function ifGeneratorTableWorks()
    {

        $columns =  [
            'id',
            'name',
            'username',
        ];

        $twigEnviron = $this->getMockBuilder(\Twig\Environment::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['render'])
            ->getMock();

        $request = $this->getMockBuilder(Request::class)
            ->onlyMethods(['templateEngine', 'visibleColumns'])
            ->getMock();

        $twigEnviron->expects($this->once())
            ->method('render')
            ->with(
                Request::TABLE_TEMPLATE,
                ['users' => [$this->user()], 'columns' => $columns, 'helper' => (new Helpers())]
            )
            ->willReturn("Test");

        $request->expects($this->once())
            ->method('templateEngine')
            ->willReturn($twigEnviron);

        $request->expects($this->once())
            ->method('visibleColumns')
            ->willReturn($columns);

        $request->generateTable([$this->user()]);
    }

    /** @test */
    public function ifGeneratorTableThrowsException()
    {

        $columns =  [
            'id',
            'name',
            'username',
        ];

        $twigEnviron = $this->getMockBuilder(\Twig\Environment::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['render'])
            ->getMock();

        $request = $this->getMockBuilder(Request::class)
            ->onlyMethods(['templateEngine', 'visibleColumns'])
            ->getMock();

        $twigEnviron->expects($this->once())
            ->method('render')
            ->with(
                Request::TABLE_TEMPLATE,
                ['users' => [$this->user()], 'columns' => $columns, 'helper' => (new Helpers())]
            )
            ->willThrowException(new \Twig\Error\Error('someerrors'));

        $request->expects($this->once())
            ->method('templateEngine')
            ->willReturn($twigEnviron);

        $request->expects($this->once())
            ->method('visibleColumns')
            ->willReturn($columns);

        $this->expectException('InpUserParser\\InpUserParserException');
        $request->generateTable([$this->user()]);
    }


    /** @test
     * @dataProvider dataForTableTemplateBug
     * Integration Test for GenerateTables Interaction with the Template Engine
     * We ought to see that there is no bug in the templates..
     */
    public function ifTableTemplateIsBugFree($column)
    {
        $columns = [
            'id',
            'name',
            'username',
            'email',
            'street',
            'suite',
            'city',
            'zipcode',
            'lat',
            'lng',
            'phone',
            'website',
            'companyName',
            'companyCatchPhrase',
            'companyBs',
        ];

        $request = $this->getMockBuilder(Request::class)
            ->onlyMethods(['visibleColumns'])
            ->getMock();

        $request->expects($this->exactly(2))
            ->method('visibleColumns')
            ->willReturn($columns);


        $this->assertStringContainsString(
            "showDetail(" .$this->user()->id . ");",
            $request->generateTable([$this->user()])
        );
        $this->assertStringContainsString($this->user()->$column, $request->generateTable([$this->user()]));
    }
}
