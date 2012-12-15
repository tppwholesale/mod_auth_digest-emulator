<?php
/* mod_auth_digest emulator for PHP (by TPPw CS)
* Known issues:
*  # Multiple realms are unsupported
*/
class mod_auth_digest
{
    protected $htpasswd = array();
    protected $realm = "protected";

    function __construct($pathToHtpasswd)
    {
        if (!is_file($pathToHtpasswd)) {
            die("Unable to locate .htpasswd file.");
        }
        if (!is_readable($pathToHtpasswd)) {
            ;
            die("Unable to read .htpasswd file.");
        }
        foreach (file($pathToHtpasswd) as $htpasswdLine) {
            $h = explode(":", $htpasswdLine); //Split up the .htpasswd file into each indiv part (user:realm:pass)
            $this->htpasswd[trim($h[0])] = trim($h[2]);
            $this->realm = $h[1];
        }
    }

    private function getRealm()
    {
        return $this->realm;
    }

    private function failAuth()
    {
        //TODO add stuff here to fail auth
        die("Invalid Response");
    }

    function doAuth()
    {
        //This header is sent from a .htaccess file
        if (empty($_SERVER['PHP_AUTH_DIGEST'])) {
            $_SERVER['PHP_AUTH_DIGEST'] = $_SERVER['PHP_AUTH_DIGEST2'];
        }
        //Prompt for auth if auth is not sent to us
        if (empty($_SERVER['PHP_AUTH_DIGEST'])) {
            header('HTTP/1.1 401 Unauthorized');
            header('WWW-Authenticate: Digest realm="' . $this->getRealm() . '",qop="auth",nonce="' . uniqid() . '",opaque="' . md5($this->getRealm()) . '"');
        }
        //Parse the received data
        $data = $this->parseDigest($_SERVER['PHP_AUTH_DIGEST']);
        //If we fail to parse the data OR we cannot find the username in the htpasswd file, fail the auth
        if ($data === false || !array_key_exists($data['username'], $this->htpasswd)) {
            $this->failAuth();
        }
        if ($data['response'] != $this->computeHash($data['username'], $data['nonce'], $data['nc'], $data['cnonce'], $data['qop'], $data['uri'])) {
            $this->failAuth();
        }
        return true;
    }

    private function getKnownHashForUsername($username)
    {
        if (array_key_exists($username, $this->htpasswd)) {
            return $this->htpasswd[$username];
        }
        return null;
    }

    private function computeHash($username, $nonce, $nc, $cnonce, $qop, $uri)
    {
        $hashPrefix = $this->getKnownHashForUsername($username); //Get HTPASSWD Hash
        $hashSuffix = md5($_SERVER['REQUEST_METHOD'] . ":$uri");
        return md5("$hashPrefix:$nonce:$nc:$cnonce:$qop:$hashSuffix");
    }

    private function parseDigest($input)
    {
        $needed_parts = array('nonce' => 1, 'nc' => 1, 'cnonce' => 1, 'qop' => 1, 'username' => 1, 'uri' => 1, 'response' => 1);
        $data = array();
        $keys = implode('|', array_keys($needed_parts));

        preg_match_all('@(' . $keys . ')=(?:([\'"])([^\2]+?)\2|([^\s,]+))@', $input, $matches, PREG_SET_ORDER);

        foreach ($matches as $m) {
            $data[$m[1]] = $m[3] ? $m[3] : $m[4];
            unset($needed_parts[$m[1]]);
        }

        return $needed_parts ? false : $data;
    }

}

?>