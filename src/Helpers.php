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
 * Class that contains Helper Functions
 *
 *
 * @author  DeRavenedWriter <deravenedwriter@gmail.com>
 * @package InpUserParser
 * @license https://www.gnu.org/licenses/gpl-2.0.txt
 */
class Helpers
{

    /**
     * Formats and Change first character to uppercase
     *
     * @param  string  $field  String in which to work on
     *
     * @return  string
     */
    public static function ucFields(string $field): string
    {
        return \ucwords(\preg_replace('/(?<!\ )[A-Z]/', ' $0', $field));
    }
}
