<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Name\RenameClassRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        // @see https://github.com/symfony/symfony/pull/44271
        'Symfony\\Component\\Notifier\\Bridge\\Nexmo\\NexmoTransportFactory' => 'Symfony\\Component\\Notifier\\Bridge\\Vonage\\VonageTransportFactory',
        'Symfony\\Component\\Notifier\\Bridge\\Nexmo\\NexmoTransport' => 'Symfony\\Component\\Notifier\\Bridge\\Vonage\\VonageTransport',
    ]);
};
