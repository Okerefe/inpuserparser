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
 * @author  DeRavenedWriter <okerefe@gmail.com>
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

}
