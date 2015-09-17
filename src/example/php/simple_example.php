<?php
require_once __DIR__ . '/library/Guacamole/Config.php';

$configs = new Guacamole_Config();
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

echo "\n\nLink:\n";
echo $configs->getLink('1337', 'mst_henh');
echo "\n";