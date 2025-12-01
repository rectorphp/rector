<?php

declare (strict_types=1);
namespace RectorPrefix202512;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Name\RenameClassRector;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        // @see https://github.com/symfony/symfony/blob/7.4/UPGRADE-7.2.md#mailer
        'Symfony\Component\Mailer\Test\TransportFactoryTestCase' => 'Symfony\Component\Mailer\Test\AbstractTransportFactoryTestCase',
    ]);
};
