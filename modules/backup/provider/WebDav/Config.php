<?php
//namespace dvcarrot\WebDAV;

/**
 * Class Config
 * @package dvcarrot\WebDAV
 */
class Config
{
    /**
     * @var string
     */
    var $hostname;

    /**
     * @var string
     */
    var $username;

    /**
     * @var string
     */
    var $password;

    /**
     * @var int
     */
    var $authtype;

    /**
     * Sets variables
     * @param $hostname
     * @param $username
     * @param $password
     * @param int $authtype
     */
    function __construct($hostname, $username, $password, $authtype = 0)
    {
        $this->hostname = (string)$hostname;
        $this->username = (string)$username;
        $this->password = (string)$password;
        $this->authtype = (int)$authtype;
    }
}