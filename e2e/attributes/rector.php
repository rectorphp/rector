<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersion;
use Rector\Symfony\Set\SymfonySetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->sets([SymfonySetList::SYMFONY_52]);

    $rectorConfig->phpVersion(PhpVersion::PHP_80);
    $rectorConfig->paths([__DIR__ . '/src']);
};
