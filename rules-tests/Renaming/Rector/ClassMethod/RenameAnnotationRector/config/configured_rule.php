<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\ClassMethod\RenameAnnotationRector;
use Rector\Renaming\ValueObject\RenameAnnotationByType;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(RenameAnnotationRector::class)
        ->configure([new RenameAnnotationByType('PHPUnit\Framework\TestCase', 'scenario', 'test')]);
};
