<?php
require_once __DIR__ . '/library/Guacamole/Config.php';

$configs = new Guacamole_Config(
        'http://localhost:8080/guacamole/',
        '/etc/guacamole'
    );
$configs
    ->addConfig(
        $configs->newProtocolRdp('TestVm RDP', '192.168.0.130')
            ->setPassword('ThisIsAPassword')
            ->setDomain('WORKGROUP')
            ->setSecurity(Guacamole_Config_Protocol_Rdp::RDP_SECURETY_NLA)
            ->setEnableDrive(true)
            ->setDrivePath('/home/guacamole/drive/')
            ->setCreateDrivePath(false)
    )
    ->addConfig(
        $configs->newProtocolVnc('TestVm VNC', '192.168.0.140')
            ->setPassword('ThisIsAPassword')
    )
    ->setDelete(true) // Delete after first usage.
    ->setValidTo(time()+(60*5)); // This file should be valid up to 5min

echo "Config object:\n";
echo (string)$configs;
/*
Config object:
<configs delete="true" valid_to="2015-09-17T14:39:01+02:00">
  <config name="TestVm RDP" protocol="rdp">
    <param name="password" value="ThisIsAPassword"/>
    <param name="domain" value="WORKGROUP"/>
    <param name="security" value="nla"/>
    <param name="enable-drive" value="true"/>
    <param name="drive-path" value="/home/guacamole/drive/"/>
    <param name="create-drive-path" value="false"/>
    <param name="hostname" value="192.168.0.130"/>
  </config>
  <config name="TestVm VNC" protocol="vnc">
    <param name="hostname" value="192.168.0.140"/>
    <param name="password" value="ThisIsAPassword"/>
  </config>
</configs>
 */

echo "\n\nLink:\n";

// Creates file: "/etc/guacamole/mst_henh_1337_noauth-config.xml" and return link to guacamole home.
echo $configs->getLink('1337', 'mst_henh');
echo "\n";
/*
Link:
http://localhost:8080/guacamole/#/?username=mst_henh&ident=133
 */

// Creates file: "/etc/guacamole/mst_henh_1337_noauth-config.xml" and return link to guacamole link.
echo $configs->getLink('1337', 'mst_henh', true, 'TestVm RDP');
echo "\n";
/*
Link:
http://localhost:8080/guacamole/#/?username=mst_henh&ident=1337http://localhost:8080/guacamole/e/#/client/VGVzdFZtIFJEUABjAHVzZXJmaWxlc2F1dGg=?username=mst_henh&ident=1337
*/
