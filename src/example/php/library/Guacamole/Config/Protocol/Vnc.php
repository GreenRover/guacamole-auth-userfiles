<?php
require_once __DIR__ . '/Abstract.php';
require_once __DIR__ . '/Sftp.php';

/**
 * Virtual Network Computing
 * Guacamole Version: 0.9.8
 */

class Guacamole_Config_Protocol_Vnc extends Guacamole_Config_Protocol_Abstract {

    /**
     * VNC does not normally support file transfer, but Guacamole can provide file transfer over SFTP
     * even when the remote desktop is otherwise being accessed through VNC and not SSH.
     * If SFTP is enabled on a Guacamole VNC connection,
     * users will be able to upload and download files.
     */
    use Guacamole_Config_Protocol_Sftp;

    protected $autoretry = null;

    protected $color_depth = null;
    protected $swap_red_blue = null;
    protected $remote_cursor = null;
    protected $encodings = null;
    protected $read_only = null;

    protected $dest_host = null;
    protected $dest_port = null;

    protected $reverse_connect = null;
    protected $listen_timeout = null;

    protected $enable_audio = null;
    protected $audio_servername = null;

    protected $clipboard_encoding = null;

    /**
     * Get protocol name.
     *
     * @return string
     */
    protected function getProtocol() {
        return 'vnc';
    }

    /**
     * Set the port the VNC server is listening on, usually 5900 or 5900 + display number.
     * For example, if your VNC server is serving display number 1 (sometimes written as :1), your port number here would be 5901.
     *
     * @param int $port
     * @throws Guacamole_Config_Exception
     *
     * @return \Guacamole_Config_Protocol_Sftp
     */
    public function setPort($port) {
        parent::setPort($port);

        return $this;
    }

    /**
     * Set the number of times to retry connecting before giving up and returning an error.
     * In the case of a reverse connection,
     * this is the number of times the connection process is allowed to time out.
     *
     * @return int
     */
    public function getAutoretry() {
        return $this->autoretry;
    }

    /**
     * @param int $autoretry
     *
     * @return \Guacamole_Config_Protocol_Sftp
     */
    public function setAutoretry($autoretry) {
        $this->autoretry = $autoretry;

        return $this;
    }

    /**
     * @return int
     */
    public function getColorDepth() {
        return $this->color_depth;
    }

    /**
     * Set the color depth to request, in bits-per-pixel.
     * This parameter is optional. If specified, this must be either 8, 16, 24, or 32.
     * Regardless of what value is chosen here,
     * if a particular update uses less than 256 colors,
     * Guacamole will always send that update as a 256-color PNG.
     *
     * @param int $color_depth
     * @throws Guacamole_Config_Exception
     *
     * @return \Guacamole_Config_Protocol_Sftp
     */
    public function setColorDepth($color_depth) {
        if (!in_array($color_depth, array(null, 8, 16, 24, 32))) {
            throw new Guacamole_Config_Exception('Invalid color depth. Allowed are: NULL, 8, 16, 24, 32');
        }

        $this->color_depth = $color_depth;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getSwapRedBlue() {
        return $this->swap_red_blue;
    }

    /**
     * If the colors of your display appear wrong (blues appear orange or red, etc.),
     * it may be that your VNC server is sending image data incorrectly, and the red and blue components of each color are swapped.
     * If this is the case, set this parameter to "true" to work around the problem. This parameter is optional.
     *
     * @param boolean $swap_red_blue
     *
     * @return \Guacamole_Config_Protocol_Sftp
     */
    public function setSwapRedBlue($swap_red_blue) {
        $this->swap_red_blue = ($swap_red_blue == true) ? true: false;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getCursor() {
        return $this->remote_cursor;
    }

    /**
     * If TRUE the mouse pointer will be rendered remotely,
     * and the local position of the mouse pointer will be indicated by a small dot.
     * A remote mouse cursor will feel slower than a local cursor,
     * but may be necessary if the VNC server does not support sending the cursor image to the client.
     *
     * @param boolean $remote_cursor
     *
     * @return \Guacamole_Config_Protocol_Sftp
     */
    public function setCursor($remote_cursor) {
        $this->remote_cursor = $remote_cursor;

        return $this;
    }

    /**
     * A space-delimited list of VNC encodings to use. The format of this parameter is dictated by libvncclient and
     * thus doesn't really follow the form of other Guacamole parameters.
     * This parameter is optional, and libguac-client-vnc will use any supported encoding by default.
     * Beware that this parameter is intended to be replaced with individual, encoding-specific parameters in a future release.
     * 
     * @return string
     */
    public function getEncodings() {
        return $this->encodings;
    }

    /**
     * @param string $encodings
     *
     * @return \Guacamole_Config_Protocol_Sftp
     */
    public function setEncodings($encodings) {
        $this->encodings = $encodings;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getReadOnly() {
        return $this->read_only;
    }

    /**
     * Whether this connection should be read-only.
     * If set to "true", no input will be accepted on the connection at all.
     * Users will only see the desktop and whatever other users using that same desktop are doing.
     * This parameter is optional.
     *
     * @param boolean $read_only
     *
     * @return \Guacamole_Config_Protocol_Sftp
     */
    public function setReadOnly($read_only) {
        $this->read_only = $read_only;

        return $this;
    }

    /**
     * @return string
     */
    public function getClipboardEncoding() {
        return $this->clipboard_encoding;
    }

    /**
     * The encoding to assume for the VNC clipboard.
     * This parameter is optionl.
     * By default, the standard encoding ISO 8859-1 will be used.
     * Only use this parameter if you are sure your VNC server supports other encodings beyond the standard ISO 8859-1.
     *
     * Possible values are:
     *   ISO8859-1
     *      ISO 8859-1 is the clipboard encoding mandated by the VNC standard, and supports only basic Latin characters.
     *      Unless your VNC server specifies otherwise, this encoding is the only encoding guaranteed to work.
     *
     *   UTF-8
     *      UTF-8 - the most common encoding used for Unicode.
     *      Using this encoding for the VNC clipboard violates the VNC specification, but some servers do support this.
     *      This parameter value should only be used if you know your VNC server supports this encoding.
     *
     *   UTF-16
     *      UTF-16 - a 16-bit encoding for Unicode which is not as common as UTF-8,
     *      but still widely used. Using this encoding for the VNC clipboard violates the VNC specification.
     *      This parameter value should only be used if you know your VNC server supports this encoding.
     *
     *   CP1252
     *      Code page 1252 - a Windows-specific encoding for Latin characters which is mostly a superset of ISO 8859-1,
     *      mapping some additional displayable characters onto what would otherwise be control characters.
     *      Using this encoding for the VNC clipboard violates the VNC specification.
     *      This parameter value should only be used if you know your VNC server supports this encoding.
     *
     * @param string $clipboard_encoding
     *
     * @return \Guacamole_Config_Protocol_Sftp
     */
    public function setClipboardEncoding($clipboard_encoding) {
        $this->clipboard_encoding = $clipboard_encoding;

        return $this;
    }

    /**
     * @return string
     */
    public function getDestHost() {
        return $this->dest_host;
    }

    /**
     * VNC - Repeater
     *
     * The destination host to request when connecting to a VNC proxy such as UltraVNC Repeater.
     * This is only necessary if the VNC proxy in use requires the connecting user to specify which VNC server to connect to.
     * If the VNC proxy automatically connects to a specific server, this parameter is not necessary.
     *
     * @param string $dest_host
     *
     * @return \Guacamole_Config_Protocol_Sftp
     */
    public function setDestHost($dest_host) {
        $this->dest_host = $dest_host;

        return $this;
    }

    /**
     * @return int
     */
    public function getDestPort() {
        return $this->dest_port;
    }

    /**
     * VNC - Repeater
     *
     * The destination port to request when connecting to a VNC proxy such as UltraVNC Repeater.
     * This is only necessary if the VNC proxy in use requires the connecting user to specify which VNC server to connect to.
     * If the VNC proxy automatically connects to a specific server, this parameter is not necessary.
     *
     * @param int $dest_port
     *
     * @return \Guacamole_Config_Protocol_Sftp
     */
    public function setDestPort($dest_port) {
        $this->dest_port = $dest_port;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getReverseConnect() {
        return $this->reverse_connect;
    }

    /**
     * Reverse VNC connections
     *
     * Whether reverse connection should be used.
     * If set to "true", instead of connecting to a server at a given hostname and port,
     * guacd will listen on the given port for inbound connections from a VNC server.
     *
     * @param boolean $reverse_connect
     *
     * @return \Guacamole_Config_Protocol_Sftp
     */
    public function setReverseConnect($reverse_connect) {
        $this->reverse_connect = $reverse_connect;

        return $this;
    }

    /**
     * @return int
     */
    public function getListenTimeout() {
        return $this->listen_timeout;
    }

    /**
     * Reverse VNC connections
     *
     * If reverse connection is in use,
     * the maximum amount of time to wait for an inbound connection from a VNC server,
     * in milliseconds.
     *
     * If blank, the default value is 5000 (five seconds).
     *
     * @param int $listen_timeout
     *
     * @return \Guacamole_Config_Protocol_Sftp
     */
    public function setListenTimeout($listen_timeout) {
        $this->listen_timeout = $listen_timeout;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getEnableAudio() {
        return $this->enable_audio;
    }

    /**
     * @param boolean $enable_audio
     *
     * @return \Guacamole_Config_Protocol_Sftp
     */
    public function setEnableAudio($enable_audio) {
        $this->enable_audio = $enable_audio;

        return $this;
    }

    /**
     * @return string
     */
    public function getAudioServername() {
        return $this->audio_servername;
    }

    /**
     * @param string $audio_servername
     *
     * @return \Guacamole_Config_Protocol_Sftp
     */
    public function setAudioServername($audio_servername) {
        $this->audio_servername = $audio_servername;

        return $this;
    }

}