<?php

require_once __DIR__ . '/../Exception.php';

/**
 * Class Guacamole_Config_Protocol_Abstract
 * Abstract class for all guacamole configs.
 */
abstract class Guacamole_Config_Protocol_Abstract {

    protected $name;
    protected $hostname;
    protected $port = null;
    protected $password = null;

    /**
     * Class constructor.
     *
     * @param string $name
     * @param string $hostname
     */
    public function __construct($name, $hostname) {
        $this->name = $name;
        $this->hostname = $hostname;
    }

    /**
     * Get protocol name.
     *
     * @return string
     */
    abstract protected function getProtocol();

    /**
     * Get remote session name.
     *
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getPort() {
        return $this->port;
    }

    /**
     * @param int $port
     * @throws Guacamole_Config_Exception
     *
     * @return \Guacamole_Config_Protocol_Sftp
     */
    public function setPort($port) {
        if (!is_numeric($port) || $port < 10) {
            throw new Guacamole_Config_Exception('Invalid tcp port');
        }

        $this->port = $port;

        return $this;
    }

    /**
     * Get optional password.
     *
     * @return string
     */
    public function getPassword() {
        return $this->password;
    }

    /**
     * Set optional password.
     *
     * @param string $password
     *
     * @return \Guacamole_Config_Protocol_Sftp
     */
    public function setPassword($password) {
        $this->password = $password;

        return $this;
    }

    /**
     * Get hostname.
     *
     * @return string
     */
    public function getHostname() {
        return $this->hostname;
    }

    /**
     * Get config xml object.
     *
     * @return \DOMDocument
     */
    public function getXml() {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->xmlStandalone = false;
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $config = $dom->createElement('config');

        $attr_name = $dom->createAttribute('name');
        $attr_name->value = $this->getName();
        $config->appendChild($attr_name);

        $attr_protocol = $dom->createAttribute('protocol');
        $attr_protocol->value = $this->getProtocol();
        $config->appendChild($attr_protocol);

        $vars = get_object_vars($this);
        unset($vars['name']);
        unset($vars['protocol']);

        foreach ($vars as $key => $val) {
            if ($val === null) {
                // This value was not set. So dont add to xml.
                continue;
            }

            $param = $dom->createElement('param');

            $attr_name = $dom->createAttribute('name');
            $attr_name->value = str_replace('_', '-', $key);
            $param->appendChild($attr_name);

            if (is_bool($val)) {
                // Convert boolean to required string.
                $val = ($val === true) ? 'true' : 'false';
            }

            $attr_value = $dom->createAttribute('value');
            $attr_value->value = $val;
            $param->appendChild($attr_value);

            $config->appendChild($param);
        }

        $dom->appendChild($config);

        return $dom;
    }

    /**
     * Magic methode for string converting.
     *
     * @return string
     */
    public function __toString() {
        $xml = '';
        $dom = $this->getXml();
        foreach($dom->childNodes as $node) {
            $xml .= $dom->saveXML($node) . "\n";
        }

        return $xml;
    }

}