<?php

namespace OpenProvider;

use DOMDocument;

class Request
{
    protected $cmd = null;
    protected $args = null;
    protected $username = null;
    protected $password = null;
    protected $hash = null;
    protected $token = null;
    protected $ip = null;
    protected $language = null;
    protected $raw = null;
    protected $misc = null;
    public function __construct ($str = null) {
        if ($str) {
            $this->raw = $str;
            $this->_parseRequest($str);
        }
    }
    /*
     * Parse request string to assign object properties with command name and
     * arguments structure
     *
     * @return void
     *
     * @uses OP_Request::__construct()
     */
    protected function _parseRequest ($str = "")
    {
        $dom = new DOMDocument;
        $dom->loadXML($str, LIBXML_NOBLANKS);
        $arr = API::convertXmlToPhpObj($dom->documentElement);
        list($dummy, $credentials) = each($arr);
        list($this->cmd, $this->args) = each($arr);
        $this->username = $credentials['username'];
        $this->password = $credentials['password'];
        if (isset($credentials['hash'])) {
            $this->hash = $credentials['hash'];
        }
        if (isset($credentials['misc'])) {
            $this->misc = $credentials['misc'];
        }
        $this->token = isset($credentials['token']) ? $credentials['token'] : null;
        $this->ip = isset($credentials['ip']) ? $credentials['ip'] : null;
        if (isset($credentials['language'])) {
            $this->language = $credentials['language'];
        }
    }
    public function setCommand ($v)
    {
        $this->cmd = $v;
        return $this;
    }
    public function getCommand ()
    {
        return $this->cmd;
    }
    public function setLanguage ($v)
    {
        $this->language = $v;
        return $this;
    }
    public function getLanguage ()
    {
        return $this->language;
    }
    public function setArgs ($v)
    {
        $this->args = $v;
        return $this;
    }
    public function getArgs ()
    {
        return $this->args;
    }
    public function setMisc ($v)
    {
        $this->misc = $v;
        return $this;
    }
    public function getMisc ()
    {
        return $this->misc;
    }
    public function setAuth ($args)
    {
        $this->username = isset($args["username"]) ? $args["username"] : null;
        $this->password = isset($args["password"]) ? $args["password"] : null;
        $this->hash = isset($args["hash"]) ? $args["hash"] : null;
        $this->token = isset($args["token"]) ? $args["token"] : null;
        $this->ip = isset($args["ip"]) ? $args["ip"] : null;
        $this->misc = isset($args["misc"]) ? $args["misc"] : null;
        return $this;
    }
    public function getAuth ()
    {
        return array(
            "username" => $this->username,
            "password" => $this->password,
            "hash" => $this->hash,
            "token" => $this->token,
            "ip" => $this->ip,
            "misc" => $this->misc,
        );
    }
    public function getRaw ()
    {
        if (!$this->raw) {
            $this->raw .= $this->_getRequest();
        }
        return $this->raw;
    }
    public function _getRequest ()
    {
        $dom = new DOMDocument('1.0', API::$encoding);

        $credentialsElement = $dom->createElement('credentials');
        $usernameElement = $dom->createElement('username');
        $usernameElement->appendChild(
            $dom->createTextNode(API::encode($this->username))
        );
        $credentialsElement->appendChild($usernameElement);

        $passwordElement = $dom->createElement('password');
        $passwordElement->appendChild(
            $dom->createTextNode(API::encode($this->password))
        );
        $credentialsElement->appendChild($passwordElement);

        $hashElement = $dom->createElement('hash');
        $hashElement->appendChild(
            $dom->createTextNode(API::encode($this->hash))
        );
        $credentialsElement->appendChild($hashElement);

        if (isset($this->language)) {
            $languageElement = $dom->createElement('language');
            $languageElement->appendChild($dom->createTextNode($this->language));
            $credentialsElement->appendChild($languageElement);
        }

        if (isset($this->token)) {
            $tokenElement = $dom->createElement('token');
            $tokenElement->appendChild($dom->createTextNode($this->token));
            $credentialsElement->appendChild($tokenElement);
        }

        if (isset($this->ip)) {
            $ipElement = $dom->createElement('ip');
            $ipElement->appendChild($dom->createTextNode($this->ip));
            $credentialsElement->appendChild($ipElement);
        }

        if (isset($this->misc)) {
            $miscElement = $dom->createElement('misc');
            $credentialsElement->appendChild($miscElement);
            API::convertPhpObjToDom($this->misc, $miscElement, $dom);
        }

        $rootElement = $dom->createElement('openXML');
        $rootElement->appendChild($credentialsElement);

        $rootNode = $dom->appendChild($rootElement);
        $cmdNode = $rootNode->appendChild(
            $dom->createElement($this->getCommand())
        );
        API::convertPhpObjToDom($this->args, $cmdNode, $dom);

        return $dom->saveXML();
    }
}