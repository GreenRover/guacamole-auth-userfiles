<?php
require_once __DIR__ . '/library/Guacamole/Config.php';

$configs = new Guacamole_Config();
$configs
    ->addConfig(
        $configs->newProtocolRdp('TestVm RDP', '192.168.0.130')
            ->setPassword("ThisIsAPassword")
    )
    ->addConfig(
        $configs->newProtocolVnc('TestVm VNC', '192.168.0.140')
                ->setPassword("ThisIsAPassword")
    );
echo (string)$configs;