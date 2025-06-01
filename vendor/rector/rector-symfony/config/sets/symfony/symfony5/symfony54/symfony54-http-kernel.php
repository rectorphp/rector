<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Name\RenameClassRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        // @see https://github.com/symfony/symfony/pull/45615
        'Symfony\\Component\\HttpKernel\\EventListener\\AbstractTestSessionListener' => 'Symfony\\Component\\HttpKernel\\EventListener\\AbstractSessionListener',
        'Symfony\\Component\\HttpKernel\\EventListener\\TestSessionListener' => 'Symfony\\Component\\HttpKernel\\EventListener\\SessionListener',
    ]);
};
