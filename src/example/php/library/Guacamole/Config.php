<?php
require_once __DIR__ . '/Config/Protocol/Rdp.php';
require_once __DIR__ . '/Config/Protocol/Vnc.php';
require_once __DIR__ . '/Config/Protocol/Ssh.php';

class Guacamole_Config {
    protected $configs = array();
    protected $delete = false;

    /**
     * Add new config.
     *
     * @param \Guacamole_Config_Protocol_Abstract $config
     *
     * @return \Guacamole_Config
     */
    public function addConfig(Guacamole_Config_Protocol_Abstract $config) {
        $this->configs[] = $config;

        return $this;
    }

    /**
     * Get configs xml object.
     *
     * @return \DOMDocument
     */
    public function getXml() {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->xmlStandalone = false;
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $configs = $dom->createElement('configs');

        $attr_delete = $dom->createAttribute('delete');
        $attr_delete->value = ($this->delete === true) ? 'true' : 'false';
        $configs->appendChild($attr_delete);

        foreach ($this->configs as $config) {
            /* @var $config Guacamole_Config_Protocol_Abstract */
            foreach ($config->getXml()->getElementsByTagName('config') as $dom_element) {
                $local_dom_element = $dom->importNode($dom_element, true);
                $configs->appendChild($local_dom_element);
            }
        }

        $dom->appendChild($configs);

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

    /**
     * Enable auto deletion of config file when used.
     * So this link is just usable once.
     *
     * @param boolean $delete
     *
     * @return \Guacamole_Config
     */
    public function setDelete($delete) {
        $this->delete = ($delete == true) ? true: false;

        return $this;
    }

    /**
     * Fluent code helper to create new rdp protocoll.
     *
     * @param string $name
     * @param string $hostname
     *
     * @return \Guacamole_Config_Protocol_Rdp
     */
    public function newProtocolRdp($name, $hostname) {
        $rdp = new Guacamole_Config_Protocol_Rdp($name, $hostname);
        return $rdp;
    }

    /**
     * Fluent code helper to create new vnc protocoll.
     *
     * @param string $name
     * @param string $hostname
     *
     * @return \Guacamole_Config_Protocol_Vnc
     */
    public function newProtocolVnc($name, $hostname) {
        $vnc = new Guacamole_Config_Protocol_Vnc($name, $hostname);
        return $vnc;
    }

    /**
     * Fluent code helper to create new ssh protocoll.
     *
     * @param string $name
     * @param string $hostname
     *
     * @return \Guacamole_Config_Protocol_Ssh
     */
    public function newProtocolSsh($name, $hostname) {
        $ssh = new Guacamole_Config_Protocol_Ssh($name, $hostname);
        return $ssh;
    }
}