<?php
/**
 * Primitive proxy class for proxying requests
 * Supports GET/POST only
 */
class proxy_passthru
{
    /**
     * @param $url string - url of destination
     * @param null $host Optional contents of "Host" header to send through with request (eg if URL is an IP)
     */
    function __construct($url, $host = null)
    {
        $this->doProxy($url);
    }

    private function setHeader($ch, $string)
    {
        //TODO, work out which headers break the proxy
        if (stripos($string, "Content-Type") !== false) {
            header($string);
        }
        return strlen($string);
    }
    //TODO support setting headers
    //TODO pass through headers from client
    private function doProxy($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-Forwarded-For: " . $_SERVER['REMOTE_ADDR']));
        if ($_SERVER['REQUEST_METHOD'] === "POST") {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $_POST);
        }
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, array($this, "setHeader"));
        curl_exec($ch);
    }


}

?>