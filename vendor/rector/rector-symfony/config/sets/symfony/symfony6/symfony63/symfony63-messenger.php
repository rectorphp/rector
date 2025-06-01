<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Name\RenameClassRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        // @see https://github.com/symfony/symfony/commit/9415b438b75204c72ff66b838307b73646393cbf
        'Symfony\\Component\\Messenger\\EventListener\\StopWorkerOnSigtermSignalListener' => 'Symfony\\Component\\Messenger\\EventListener\\StopWorkerOnSignalsListener',
        // @see https://github.com/symfony/symfony/commit/a7926b2d83f35fe53c41a28d8055490cc1955928
        'Symfony\\Component\\Messenger\\Transport\\InMemoryTransport' => 'Symfony\\Component\\Messenger\\Transport\\InMemory\\InMemoryTransport',
        'Symfony\\Component\\Messenger\\Transport\\InMemoryTransportFactory' => 'Symfony\\Component\\Messenger\\Transport\\InMemory\\InMemoryTransportFactory',
    ]);
};
