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
 * Class That Defines the User Instance
 *
 *
 * @author  DeRavenedWriter <deravenedwriter@gmail.com>
 * @package InpUserParser
 * @license https://www.gnu.org/licenses/gpl-2.0.txt
 */
class User
{

    public $id;
    public $name;
    public $username;
    public $email;
    public $street;
    public $suite;
    public $city;
    public $zipcode;
    public $lat;
    public $lng;
    public $phone;
    public $website;
    public $companyName;
    public $companyCatchPhrase;
    public $companyBs;

    /**
     * @var array Contains Array of Default Field of Users
     */
    const USED_FIELDS =[
            'id',
            'name',
            'username',
            'email',
            'street',
            'phone',
            'website',
            'companyName',
        ];


    /**
     * @var array Contains Array of Default Column of Users
     */
    const DEFAULT_COLUMNS =[
            'id',
            'name',
            'username',
        ];


    /**
     * Constructs the User Object
     *
     * @param string $id
     * @param string $name
     * @param string $username
     * @param string $email
     * @param string $street
     * @param string $suite
     * @param string $city
     * @param string $zipcode
     * @param string $lat
     * @param string $lng
     * @param string $phone
     * @param string $website
     * @param string $companyName
     * @param string $companyCatchPhrase
     * @param string $companyBs
     *
     * @return void
     */
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

    /**
     * Returns an Assoc Array of User Properties and Values
     *
     * @return  array
     */
    public function generateArray() : array
    {
        $userArray = [];
        foreach ($this as $var => $value) {
            $userArray[$var] = $value;
        }
        return $userArray;
    }
}
