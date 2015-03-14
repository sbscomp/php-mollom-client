<?php

namespace sbscomp\mollom;

require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../../vendor/mollom/client/mollom.class.inc';

class MollomClient extends \Mollom {

    private $_platformName = "com.sbscomp.mollomclient";
    private $_platformVersion = "1.0.0";
    private $CLIENT_NAME = "com.sbscomp.mollomclient";
    private $CLIENT_VERSION = "1.0.0";

    function __construct($publicKey, $privateKey)
    {
        $this->publicKey = $publicKey;
        $this->privateKey = $privateKey;
        $this->server = "rest.mollom.com";
    }

    public function setPlatformName($name)
    {
        $this->_platformName = $name;
    }

    public function setPlatformVersion($version)
    {
        $this->_platformVersion = $version;
    }

    public function enableDevelopmentMode($isCreating)
    {
        $this->server = "dev.mollom.com";
        if($isCreating) {
            $this->oAuthStrategy = ""; //clear the oAuth strategy for creating a new test site.
        }
    }

    public function setCredentials($publicKey, $privateKey)
    {
        $this->publicKey = $publicKey;
        $this->privateKey = $privateKey;
    }

    /**
     * Loads a configuration value from client-side storage.
     *
     * @param string $name
     *   The configuration setting name to load, one of:
     *   - publicKey: The public API key for Mollom authentication.
     *   - privateKey: The private API key for Mollom authentication.
     *   - expectedLanguages: List of expected language codes for site content.
     *
     * @return mixed
     *   The stored configuration value or NULL if there is none.
     *
     * @see Mollom::saveConfiguration()
     * @see Mollom::deleteConfiguration()
     */
    protected function loadConfiguration($name)
    {
        // TODO: Implement loadConfiguration() method.
    }

    /**
     * Saves a configuration value to client-side storage.
     *
     * @param string $name
     *   The configuration setting name to save.
     * @param mixed $value
     *   The value to save.
     *
     * @see Mollom::loadConfiguration()
     * @see Mollom::deleteConfiguration()
     */
    protected function saveConfiguration($name, $value)
    {
        // TODO: Implement saveConfiguration() method.
    }

    /**
     * Deletes a configuration value from client-side storage.
     *
     * @param string $name
     *   The configuration setting name to delete.
     *
     * @see Mollom::loadConfiguration()
     * @see Mollom::saveConfiguration()
     */
    protected function deleteConfiguration($name)
    {
        // TODO: Implement deleteConfiguration() method.
    }

    /**
     * Returns platform and version information about the Mollom client.
     *
     * Retrieves platform and Mollom client version information to send along to
     * Mollom when verifying keys.
     *
     * This information is used to speed up support requests and technical
     * inquiries. The data may also be aggregated to help the Mollom staff to make
     * decisions on new features or the necessity of back-porting improved
     * functionality to older versions.
     *
     * @return array
     *   An associative array containing:
     *   - platformName: The name of the platform/distribution; e.g., "Drupal".
     *   - platformVersion: The version of platform/distribution; e.g., "7.0".
     *   - clientName: The official Mollom client name; e.g., "Mollom".
     *   - clientVersion: The version of the Mollom client; e.g., "7.x-1.0".
     */
    public function getClientInformation()
    {
        return array(
            "platformName" => $this->_platformName,
            "platformVersion" => $this->_platformVersion,
            "clientName" => $this->CLIENT_NAME,
            "clientVersion" => $this->CLIENT_VERSION,
        );
    }

    /**
     * Performs a HTTP request to a Mollom server.
     *
     * @param string $method
     *   The HTTP method to use; i.e., 'GET', 'POST', or 'PUT'.
     * @param string $server
     *   The base URL of the server to perform the request against; e.g.,
     *   'http://foo.mollom.com'.
     * @param string $path
     *   The REST path/resource to request; e.g., 'site/1a2b3c'.
     * @param string $query
     *   (optional) A prepared string of HTTP query parameters to append to $path
     *   for $method GET, or to use as request body for $method POST.
     * @param array $headers
     *   (optional) An associative array of HTTP request headers to send along
     *   with the request.
     *
     * @return object
     *   An object containing response properties:
     *   - code: The HTTP status code as integer returned by the Mollom server.
     *   - message: The HTTP status message string returned by the Mollom server,
     *     or NULL if there is no message.
     *   - headers: An associative array containing the HTTP response headers
     *     returned by the Mollom server. Header name keys are expected to be
     *     lower-case; i.e., "content-type" instead of "Content-Type".
     *   - body: The HTTP response body string returned by the Mollom server, or
     *     NULL if there is none.
     *
     * @see Mollom::handleRequest()
     */
    protected function request($method, $server, $path, $query = NULL, array $headers = array())
    {
        $retObj = new \stdClass();
        $ch = curl_init($server . '/' . $path);
        switch($method)
        {
            case "GET":
                //do nothing. this is the default.
                break;
            case "POST":
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
                break;
            case "PUT":
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
                break;
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        $response = curl_exec($ch);
        $errorMsg = curl_error($ch);
        $retObj->code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $retObj->headers = array();
        list($header,$body) = explode("\r\n\r\n", $response, 2);
        foreach( explode("\r\n", $header) as $hdr)
        {
            if(strpos($hdr,":") > 0) {
                $hsplit = explode(":", $hdr);
                $retObj->headers[strtolower($hsplit[0])] = $hsplit[1];
            }
        }
        $retObj->body = empty($body)?null:$body;
        $retObj->message = empty($errorMsg)?null:$errorMsg;
        curl_close($ch);
        return $retObj;
    }
}