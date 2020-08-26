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
 * Custom InpUserParser Exeption Class
 *
 *
 * @author  DeRavenedWriter <deravenedwriter@gmail.com>
 * @package InpUserParser
 * @license https://www.gnu.org/licenses/gpl-2.0.txt
 */
class InpUserParserException extends \Exception
{
    /**
     * @var string Contains Default Error Message for InpUserParser Exception
     */
    protected $message = "Error In InpUserParser Plugin";

    /**
     * Returns the Generated InpUserParser Front Page
     *
     * @param  string  $message  Custom Error Message
     *
     * @return  InpUserParserException
     */
    public function setErrorMessage($message) : InpUserParserException
    {
        $this->message = $message;
        return $this;
    }

    /**
     * Returns the Generated InpUserParser Front Page
     *
     * @param  int  $id  Id Of User
     *
     * @return  InpUserParserException
     */
    public function setToUserError(int $id) : InpUserParserException
    {
        $this->message = "Could not Fetch User with Id:" . $id;
        return $this;
    }
}
