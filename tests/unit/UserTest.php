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
 * @author  DeRavenedWriter <deravenedwriter@gmail.com>
 * @package InpUserParser
 * @license https://www.gnu.org/licenses/gpl-2.0.txt
 */
final class UserTest extends InpUserParserTest
{


    /** @test
     *  @dataProvider dataForUserValidity
     */
    public function constructorIsValid($expected, $actual)
    {
        $this->assertSame($expected, $actual);
    }

    /** @test
     *  @dataProvider dataForUserValidity
     */
    public function generateArray($expected)
    {
        $err =  "User::generateArray not consistent";
        $this->assertContains($expected, $this->user()->generateArray(), $err);
    }
}
