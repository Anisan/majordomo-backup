<?php
//namespace dvcarrot\WebDAV;

/**
 * Class Result
 * @package dvcarrot\WebDAV
 */
class Result
{
    /**
     * @var int
     */
    var $code;

    /**
     * @var string
     */
    var $response;

    /**
     * Sets variables
     * @param int $code
     * @param string $response
     */
    function __construct($code, $response)
    {
        $this->code = (int)$code;
        $this->response = (string)$response;
    }

    /**
     * @return array
     */
    function getResponseArray()
    {
        $str = strtr($this->response, array('<d:' => '<', '</d:' => '</'));
        $xml = simplexml_load_string($str);
        $json = json_encode($xml);
        $array = json_decode($json, true);
        return $array['response'];
    }
}