<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php80\Rector\Class_\DoctrineAnnotationClassToAttributeRector;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(DoctrineAnnotationClassToAttributeRector::class)
        ->configure([
            DoctrineAnnotationClassToAttributeRector::REMOVE_ANNOTATIONS => false,
        ]);
};
