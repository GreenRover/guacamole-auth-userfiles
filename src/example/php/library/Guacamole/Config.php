<?php
require_once __DIR__ . '/Config/Protocol/Rdp.php';
require_once __DIR__ . '/Config/Protocol/Vnc.php';
require_once __DIR__ . '/Config/Protocol/Ssh.php';
require_once __DIR__ . '/Config/Exception.php';

class Guacamole_Config {
    protected $configs = array();
    protected $delete = false;
    protected $valid_to = null;

    protected $base_url;
    protected $guacamole_home;

    const CONFIG_SUFIX = 'noauth-config.xml';

    /**
     * Class constructor.
     *
     * @param string $base_url
     *   Optional. Only required for ->getLink()
     *
     * @param string $guacamole_home
     *   Optional. Only required for ->getLink()
     *   Path to store *.xml config file.
     *   Can bet set as variable in "/etc/default/tomcat7"
     *   Example: GUACAMOLE_HOME=/etc/guacamole
     */
    public function __construct($base_url = 'http://localhost:8080/guacamole/', $guacamole_home = '/etc/guacamole') {
        $this->base_url = $base_url;
        $this->guacamole_home = $guacamole_home;
    }

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

        if (!empty($this->valid_to)) {
            $attr_valid_to = $dom->createAttribute('valid_to');
            $attr_valid_to->value = date('c', $this->valid_to);
            $configs->appendChild($attr_valid_to);
        }

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
     * @return int
     */
    public function getValidTo() {
        return $this->valid_to;
    }

    /**
     * Set max vild time for this config.
     *
     * Should only be combined with ->setDelete(true),
     * to not fill disk.
     *
     * @param int $valid_to
     *   Unix timestmap.
     *
     * @return \Guacamole_Config
     *
     * @throws \Guacamole_Config_Exception
     */
    public function setValidTo($valid_to) {
        if (!is_numeric($valid_to) || $valid_to < time()) {
            throw new Guacamole_Config_Exception('Please give a unix timestzmap in future.');
        }

        $this->valid_to = $valid_to;

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

    /**
     * @return string
     */
    public function getBaseUrl() {
        return $this->base_url;
    }

    /**
     * The basic url of guacamole.
     * Example: http://localhost:8080/guacamole/
     *
     * @param string $base_url
     *
     * @return Guacamole_Config
     */
    public function setBaseUrl($base_url) {
        $this->base_url = $base_url;

        return $this;
    }

    /**
     * @return string
     */
    public function getGuacamoleHome() {
        return $this->guacamole_home;
    }

    /**
     * Path to store *.xml config file.
     * Can bet set as variable in "/etc/default/tomcat7"
     *   Example: GUACAMOLE_HOME=/etc/guacamole
     *
     * @param string $guacamole_home
     *
     * @return Guacamole_Config
     */
    public function setGuacamoleHome($guacamole_home) {
        $this->guacamole_home = $guacamole_home;

        return $this;
    }

    /**
     * Write config file to filesystem.
     * Please use prior constructor parametes or ->setGuacamoleHome()
     *
     * @param string $ident
     * @param string $username
     * @param bool|true $overwrite
     *
     * @throws \Guacamole_Config_Exception
     */
    public function writeConfig($ident, $username = null, $overwrite = true) {
        if (!is_dir($this->getGuacamoleHome())) {
            throw new Guacamole_Config_Exception('Missing "guacamole_home" folder "' . $this->getGuacamoleHome() . '".');
        }

        $prefix = '';
        if (!empty($username)) {
            $prefix .= $username . '_';
        }

        $prefix .= $ident;

        if (!preg_match('/^[\w \-öÖäÄüÜßèéêù]+$/', $prefix)) {
            throw new Guacamole_Config_Exception('Invalid username or ident, please avoid special chars.');
        }

        $filename = $this->getGuacamoleHome() . DIRECTORY_SEPARATOR . $prefix . '_' . self::CONFIG_SUFIX;

        if ($overwrite !== true && is_file($filename)) {
            throw new Guacamole_Config_Exception('File exists allreay "' . $filename . '".');
        }

        $written_bytes = file_put_contents($filename, (string) $this);

        if (empty($written_bytes)) {
            throw new Guacamole_Config_Exception('Unable to write file "' . $filename . '".');
        }
    }

    /**
     * Get guacamole link.
     *
     * @param string $ident
     * @param string $username
     * @param bool|true $write_config
     *    Write config file before generate link.
     *
     * @return string
     *   Url
     * @throws \Guacamole_Config_Exception
     */
    public function getLink($ident, $username = null, $write_config = true) {

        if (empty($ident)) {
            throw new Guacamole_Config_Exception('Ident can not be empty.');
        }

        if (!preg_match('/^\w{3,40}$/', $ident)) {
            $ident = md5($ident);
        }

        if ($write_config === true) {
            $this->writeConfig($ident, $username);
        }

        $base_url = rtrim($this->getBaseUrl(), '/#');
        $url = $base_url . '/#/?';
        if (!empty($username)) {
            $url .= 'username=' . urlencode($username) . '&';
        }
        $url .= 'ident=' .urlencode($ident);

        return $url;
    }
}