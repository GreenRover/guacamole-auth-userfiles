<?php
require_once __DIR__ . '/Abstract.php';
require_once __DIR__ . '/Sftp.php';

/**
 * Remote Desktop Protocol
 * Guacamole Version: 0.9.8
 */

class Guacamole_Config_Protocol_Rdp extends Guacamole_Config_Protocol_Abstract {

    /**
     * Guacamole can provide file transfer over SFTP even when the remote desktop is otherwise being accessed
     * through RDP and not SSH.
     * If SFTP is enabled on a Guacamole RDP connection, users will be able to upload and download files.
     *
     * This support is independent of the file transfer implemented through RDP's own "drive redirection" (RDPDR),
     * and is particularly useful for RDP servers which do not support RDPDR.
     */
    use Guacamole_Config_Protocol_Sftp;

    protected $username = null;
    protected $password = null;
    protected $domain = null;
    protected $security = null;
    protected $ignore_cert = null;
    protected $disable_auth = null;

    // Session settings
    protected $client_name = null;
    protected $console = null;
    protected $initial_program = null;
    protected $server_layout = null;

    // Display settings
    protected $color_depth = null;
    protected $width = null;
    protected $height = null;
    protected $dpi = null;

    // Device redirection
    protected $disable_audio = null;
    protected $enable_printing = null;
    protected $enable_drive = null;
    protected $drive_path = null;
    protected $create_drive_path = null;
    protected $console_audio = null;
    protected $static_channels = null;

    // Performance flags
    protected $enable_wallpaper = null;
    protected $enable_theming = null;
    protected $enable_font_smoothing = null;
    protected $enable_full_window_drag = null;
    protected $enable_desktop_composition = null;
    protected $enable_menu_animations = null;

    // RemoteApp
    protected $remote_app = null;
    protected $remote_app_dir = null;
    protected $remote_app_args = null;

    const RDP_SECURETY_RDP = 'rdp';
    const RDP_SECURETY_NLA = 'nla';
    const RDP_SECURETY_TLS = 'tls';
    const RDP_SECURETY_ANY = 'any';

    /**
     * @return string
     */
    public function getUsername() {
        return $this->username;
    }

    /**
     * The password to use when attempting authentication, if any.
     * This parameter is optional.
     *
     * @param string $username
     *
     * @return \Guacamole_Config_Protocol_Rdp
     */
    public function setUsername($username) {
        $this->username = $username;

        return $this;
    }
    
    /**
     * Get protocol name.
     *
     * @return string
     */
    protected function getProtocol() {
        return 'rdp';
    }

    /**
     * @return string
     */
    public function getPassword() {
        return $this->password;
    }

    /**
     * The password to use when attempting authentication, if any.
     * This parameter is optional.
     *
     * @param string $password
     *
     * @return \Guacamole_Config_Protocol_Rdp
     */
    public function setPassword($password) {
        $this->password = $password;

        return $this;
    }

    /**
     * @return string
     */
    public function getDomain() {
        return $this->domain;
    }

    /**
     * The domain to use when attempting authentication, if any.
     * This parameter is optional.
     *
     * @param string $domain
     *
     * @return \Guacamole_Config_Protocol_Rdp
     */
    public function setDomain($domain) {
        $this->domain = $domain;

        return $this;
    }

    /**
     * @return string
     */
    public function getSecurity() {
        return $this->security;
    }

    /**
     * The security mode to use for the RDP connection.
     * This mode dictates how data will be encrypted and what type of authentication will be performed, if any.
     * By default, the server is allowed to control what type of security is used.
     *
     * Possible values are:
     *   self::RDP_SECURETY_RDP
     *       Standard RDP encryption.
     *       This mode should be supported by all RDP servers.
     *
     *   self::RDP_SECURETY_NLA
     *       Network Level Authentication.
     *       This mode requires the username and password,
     *       and performs an authentication step before the remote desktop session actually starts.
     *       If the username and password are not given, the connection cannot be made.
     *
     *   self::RDP_SECURETY_TLS
     *       TLS encryption.
     *       TLS (Transport Layer Security) is the successor to SSL.
     *
     *   self::RDP_SECURETY_ANY
     *       Allow the server to choose the type of security.
     *       This is the default.
     *
     * @param string $security
     *
     * @return \Guacamole_Config_Protocol_Rdp
     */
    public function setSecurity($security) {
        $this->security = $security;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getIgnoreCert() {
        return $this->ignore_cert;
    }

    /**
     * If set to "true", the certificate returned by the server will be ignored,
     * even if that certificate cannot be validated.
     * This is useful if you universally trust the server and your connection to the server,
     * and you know that the server's certificate cannot be validated (for example, if it is self-signed).
     *
     * @param boolean $ignore_cert
     *
     * @return \Guacamole_Config_Protocol_Rdp
     */
    public function setIgnoreCert($ignore_cert) {
        $this->ignore_cert = $ignore_cert;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getDisableAuth() {
        return $this->disable_auth;
    }

    /**
     * If set to "true", authentication will be disabled.
     * Note that this refers to authentication that takes place while connecting.
     * Any authentication enforced by the server over the remote desktop session (such as a login dialog) will still take place.
     * By default, authentication is enabled and only used when requested by the server.
     *
     * If you are using NLA, authentication must be enabled by definition.
     *
     * @param boolean $disable_auth
     *
     * @return \Guacamole_Config_Protocol_Rdp
     */
    public function setDisableAuth($disable_auth) {
        $this->disable_auth = $disable_auth;

        return $this;
    }

    /**
     * @return string
     */
    public function getClientName() {
        return $this->client_name;
    }

    /**
     * Session settings
     *
     * When connecting to the RDP server,
     * Guacamole will normally provide its own hostname as the name of the client.
     * If this parameter is specified, Guacamole will use its value instead.
     *
     * On Windows RDP servers, this value is exposed within the session as the CLIENTNAME environment variable.
     *
     * @param string $client_name
     *
     * @return \Guacamole_Config_Protocol_Rdp
     */
    public function setClientName($client_name) {
        $this->client_name = $client_name;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getConsole() {
        return $this->console;
    }

    /**
     * Session settings
     *
     * If set to "true", you will be connected to the console (admin) session of the RDP server.
     *
     * @param boolean $console
     *
     * @return \Guacamole_Config_Protocol_Rdp
     */
    public function setConsole($console) {
        $this->console = $console;

        return $this;
    }

    /**
     * @return string
     */
    public function getInitialProgram() {
        return $this->initial_program;
    }

    /**
     * Session settings
     *
     * The full path to the program to run immediately upon connecting.
     * This parameter is optional.
     *
     * @param string $initial_program
     *
     * @return \Guacamole_Config_Protocol_Rdp
     */
    public function setInitialProgram($initial_program) {
        $this->initial_program = $initial_program;

        return $this;
    }

    /**
     * @return string
     */
    public function getServerLayout() {
        return $this->server_layout;
    }

    /**
     * Session settings
     *
     * The server-side keyboard layout.
     * This is the layout of the RDP server and has nothing to do with the keyboard layout in use on the client.
     * The Guacamole client is independent of keyboard layout.
     * The RDP protocol, however, is not independent of keyboard layout,
     * and Guacamole needs to know the keyboard layout of the server in order to send the proper keys when a user is typing.
     *
     * Possible values are:
     *   en-us-qwerty
     *      English (US) keyboard
     *
     *   de-de-qwertz
     *      German keyboard (qwertz)
     *
     *   fr-fr-azerty
     *      French keyboard (azerty)
     *
     *   it-it-qwerty
     *       Italian keyboard
     *
     *   sv-se-qwerty
     *      Swedish keyboard
     *
     *   failsafe
     *      Unknown keyboard - this option sends only Unicode events and should work for any keyboard,
     *      though not necessarily all RDP servers or applications.
     *
     * If your server's keyboard layout is not yet supported, this option should work in the meantime.
     *
     * @param string $server_layout
     *
     * @return \Guacamole_Config_Protocol_Rdp
     */
    public function setServerLayout($server_layout) {
        $this->server_layout = $server_layout;

        return $this;
    }

    /**
     * @return int
     */
    public function getColorDepth() {
        return $this->color_depth;
    }

    /**
     * Display settings
     *
     * The color depth to request, in bits-per-pixel.
     * This parameter is optional.
     * If specified, this must be either 8, 16, or 24. Regardless of what value is chosen here,
     * if a particular update uses less than 256 colors,
     * Guacamole will always send that update as a 256-color PNG.
     *
     * @param int $color_depth
     *
     * @throws \Guacamole_Config_Exception
     *
     * @return \Guacamole_Config_Protocol_Rdp
     */
    public function setColorDepth($color_depth) {
        if (!in_array($color_depth, array(null, 8, 16, 24, 32))) {
            throw new Guacamole_Config_Exception('Invalid color depth. Allowed are: NULL, 8, 16, 24, 32');
        }

        $this->color_depth = $color_depth;

        return $this;
    }

    /**
     * @return int
     */
    public function getWidth() {
        return $this->width;
    }

    /**
     * Display settings
     *
     * The width of the display to request, in pixels.
     * This parameter is optional.
     * If this value is not specified, the width of the connecting client display will be used instead.
     *
     * @param int $width
     *
     * @return \Guacamole_Config_Protocol_Rdp
     */
    public function setWidth($width) {
        $this->width = $width;

        return $this;
    }

    /**
     * @return int
     */
    public function getHeight() {
        return $this->height;
    }

    /**
     * Display settings
     *
     * The height of the display to request, in pixels.
     * This parameter is optional.
     * If this value is not specified, the height of the connecting client display will be used instead.
     *
     * @param int $height
     *
     * @return \Guacamole_Config_Protocol_Rdp
     */
    public function setHeight($height) {
        $this->height = $height;

        return $this;
    }

    /**
     * @return int
     */
    public function getDpi() {
        return $this->dpi;
    }

    /**
     * Display settings
     *
     * The desired effective resolution of the client display, in DPI.
     * This parameter is optional.
     * If this value is not specified,
     * the resolution and size of the client display will be used together to determine,
     * heuristically, an appropriate resolution for the RDP session.
     *
     * @param int $dpi
     *
     * @return \Guacamole_Config_Protocol_Rdp
     */
    public function setDpi($dpi) {
        $this->dpi = $dpi;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getDisableAudio() {
        return $this->disable_audio;
    }

    /**
     * Device redirection
     *
     * Audio is enabled by default in both the client and in libguac-client-rdp.
     * If you are concerned about bandwidth usage, or sound is causing problems,
     * you can explicitly disable sound by setting this parameter to "true".
     *
     * @param boolean $disable_audio
     *
     * @return \Guacamole_Config_Protocol_Rdp
     */
    public function setDisableAudio($disable_audio) {
        $this->disable_audio = $disable_audio;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getEnablePrinting() {
        return $this->enable_printing;
    }

    /**
     * Device redirection
     *
     * Printing is disabled by default, but with printing enabled,
     * RDP users can print to a virtual printer that sends a PDF containing the document printed to the Guacamole client.
     * Enable printing by setting this parameter to "true".
     *
     * Printing support requires GhostScript to be installed.
     * If guacd cannot find the gs executable when printing, the print attempt will fail.
     *
     * @param boolean $enable_printing
     *
     * @return \Guacamole_Config_Protocol_Rdp
     */
    public function setEnablePrinting($enable_printing) {
        $this->enable_printing = $enable_printing;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getEnableDrive() {
        return $this->enable_drive;
    }

    /**
     * Device redirection
     *
     * File transfer is disabled by default, but with file transfer enabled,
     * RDP users can transfer files to and from a virtual drive which persists on the Guacamole server.
     * Enable file transfer support by setting this parameter to "true".
     *
     * Files will be stored in the directory specified by the "drive-path" parameter,
     * which is required if file transfer is enabled.
     *
     * @param boolean $enable_drive
     *
     * @return \Guacamole_Config_Protocol_Rdp
     */
    public function setEnableDrive($enable_drive) {
        $this->enable_drive = $enable_drive;

        return $this;
    }

    /**
     * @return string
     */
    public function getDrivePath() {
        return $this->drive_path;
    }

    /**
     * Device redirection
     *
     * The directory on the Guacamole server in which transferred files should be stored.
     * This directory must be accessible by guacd and both readable and writable by the user that runs guacd.
     * This parameter does not refer to a directory on the RDP server.
     *
     * If file transfer is not enabled, this parameter is ignored.
     *
     * @param string $drive_path
     *
     * @return \Guacamole_Config_Protocol_Rdp
     */
    public function setDrivePath($drive_path) {
        $this->drive_path = $drive_path;

        return $this;
    }

    /**
     * If set to "true", and file transfer is enabled,
     * the directory specified by the drive-path parameter will automatically be created if it does not yet exist.
     * Only the final directory in the path will be created - if other directories earlier in the path do not exist,
     * automatic creation will fail, and an error will be logged.
     *
     * By default, the directory specified by the drive-path parameter will not automatically be created,
     * and attempts to transfer files to a non-existent directory will be logged as errors.
     *
     * If file transfer is not enabled, this parameter is ignored.
     *
     * @return boolean
     */
    public function getCreateDrivePath() {
        return $this->create_drive_path;
    }

    /**
     * Device redirection
     *
     * @param boolean $create_drive_path
     *
     * @return \Guacamole_Config_Protocol_Rdp
     */
    public function setCreateDrivePath($create_drive_path) {
        $this->create_drive_path = $create_drive_path;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getConsoleAudio() {
        return $this->console_audio;
    }

    /**
     * Device redirection
     *
     * If set to "true", audio will be explicitly enabled in the console (admin) session of the RDP server.
     * Setting this option to "true" only makes sense if the console parameter is also set to "true".
     *
     * @param boolean $console_audio
     *
     * @return \Guacamole_Config_Protocol_Rdp
     */
    public function setConsoleAudio($console_audio) {
        $this->console_audio = $console_audio;

        return $this;
    }

    /**
     * @return string
     */
    public function getStaticChannels() {
        return $this->static_channels;
    }

    /**
     * Device redirection
     *
     * A comma-separated list of static channel names to open and expose as pipes.
     * If you wish to communicate between an application running on the remote desktop and JavaScript,
     * this is the best way to do it. Guacamole will open an outbound pipe with the name of the static channel.
     * If JavaScript needs to communicate back in the other direction,
     * it should respond by opening another pipe with the same name.
     *
     * Guacamole allows any number of static channels to be opened,
     * but protocol restrictions of RDP limit the size of each channel name to 7 characters.
     *
     * @param string $static_channels
     *
     * @return \Guacamole_Config_Protocol_Rdp
     */
    public function setStaticChannels($static_channels) {
        $this->static_channels = $static_channels;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getEnableWallpaper() {
        return $this->enable_wallpaper;
    }

    /**
     * Performance flags
     *
     * If set to "true", enables rendering of the desktop wallpaper.
     * By default, wallpaper will be disabled,
     * such that unnecessary bandwidth need not be spent redrawing the desktop.
     *
     * @param boolean $enable_wallpaper
     *
     * @return \Guacamole_Config_Protocol_Rdp
     */
    public function setEnableWallpaper($enable_wallpaper) {
        $this->enable_wallpaper = $enable_wallpaper;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getEnableTheming() {
        return $this->enable_theming;
    }

    /**
     * Performance flags
     *
     * If set to "true",
     * enables use of theming of windows and controls.
     * By default, theming within RDP sessions is disabled.
     *
     * @param boolean $enable_theming
     *
     * @return \Guacamole_Config_Protocol_Rdp
     */
    public function setEnableTheming($enable_theming) {
        $this->enable_theming = $enable_theming;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getEnableFontSmoothing() {
        return $this->enable_font_smoothing;
    }

    /**
     * Performance flags
     *
     * If set to "true", text will be rendered with smooth edges.
     * Text over RDP is rendered with rough edges by default,
     * as this reduces the number of colors used by text,
     * and thus reduces the bandwidth required for the connection.
     *
     * @param boolean $enable_font_smoothing
     *
     * @return \Guacamole_Config_Protocol_Rdp
     */
    public function setEnableFontSmoothing($enable_font_smoothing) {
        $this->enable_font_smoothing = $enable_font_smoothing;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getEnableFullWindowDrag() {
        return $this->enable_full_window_drag;
    }

    /**
     * Performance flags
     *
     * If set to "true", the contents of windows will be displayed as windows are moved.
     * By default, the RDP server will only draw the window border while windows are being dragged.
     *
     * @param boolean $enable_full_window_drag
     *
     * @return \Guacamole_Config_Protocol_Rdp
     */
    public function setEnableFullWindowDrag($enable_full_window_drag) {
        $this->enable_full_window_drag = $enable_full_window_drag;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getEnableDesktopComposition() {
        return $this->enable_desktop_composition;
    }

    /**
     * Performance flags
     *
     * If set to "true", graphical effects such as transparent windows and shadows will be allowed.
     * By default, such effects, if available, are disabled.
     *
     * @param boolean $enable_desktop_composition
     *
     * @return \Guacamole_Config_Protocol_Rdp
     */
    public function setEnableDesktopComposition($enable_desktop_composition) {
        $this->enable_desktop_composition = $enable_desktop_composition;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getEnableMenuAnimations() {
        return $this->enable_menu_animations;
    }

    /**
     * Performance flags
     *
     * If set to "true", menu open and close animations will be allowed.
     * Menu animations are disabled by default.
     *
     * @param boolean $enable_menu_animations
     *
     * @return \Guacamole_Config_Protocol_Rdp
     */
    public function setEnableMenuAnimations($enable_menu_animations) {
        $this->enable_menu_animations = $enable_menu_animations;

        return $this;
    }

    /**
     * @return string
     */
    public function getRemoteApp() {
        return $this->remote_app;
    }

    /**
     * RemoteApp
     *
     * Specifies the RemoteApp to start on the remote desktop.
     * If supported by your remote desktop server,
     * this application, and only this application, will be visible to the user.
     *
     * Windows requires a special notation for the names of remote applications.
     * The names of remote applications must be prefixed with two vertical bars.
     * For example, if you have created a remote application on your server for notepad.exe
     * and have assigned it the name "notepad", you would set this parameter to: "||notepad".
     *
     * @param string $remote_app
     *
     * @return \Guacamole_Config_Protocol_Rdp
     */
    public function setRemoteApp($remote_app) {
        $this->remote_app = $remote_app;

        return $this;
    }

    /**
     * @return string
     */
    public function getRemoteAppDir() {
        return $this->remote_app_dir;
    }

    /**
     * RemoteApp
     *
     * The working directory, if any, for the remote application.
     * This parameter has no effect if RemoteApp is not in use.
     *
     * @param string $remote_app_dir
     *
     * @return \Guacamole_Config_Protocol_Rdp
     */
    public function setRemoteAppDir($remote_app_dir) {
        $this->remote_app_dir = $remote_app_dir;

        return $this;
    }

    /**
     * @return string
     */
    public function getRemoteAppArgs() {
        return $this->remote_app_args;
    }

    /**
     * RemoteApp
     *
     * The command-line arguments, if any, for the remote application.
     * This parameter has no effect if RemoteApp is not in use.
     *
     * @param string $remote_app_args
     *
     * @return \Guacamole_Config_Protocol_Rdp
     */
    public function setRemoteAppArgs($remote_app_args) {
        $this->remote_app_args = $remote_app_args;

        return $this;
    }

}