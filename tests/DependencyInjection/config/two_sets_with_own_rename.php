<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Symfony\Set\TwigSetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(PHPUnitSetList::PHPUNIT_60);
    $rectorConfig->import(TwigSetList::TWIG_20);

    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        'Old' => 'New',
    ]);
};
