<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Name\RenameClassRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        // @see https://github.com/symfony/symfony/pull/42965
        'Symfony\\Component\\Cache\\Adapter\\DoctrineAdapter' => 'Doctrine\\Common\\Cache\\Psr6\\CacheAdapter',
    ]);
};
