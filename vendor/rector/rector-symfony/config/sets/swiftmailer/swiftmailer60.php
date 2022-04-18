<?php

declare (strict_types=1);
namespace RectorPrefix20220418;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Name\RenameClassRector;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $services = $rectorConfig->services();
    $services->set(\Rector\Renaming\Rector\Name\RenameClassRector::class)->configure([
        # swiftmailer 60
        'Swift_Mime_Message' => 'Swift_Mime_SimpleMessage',
    ]);
};
