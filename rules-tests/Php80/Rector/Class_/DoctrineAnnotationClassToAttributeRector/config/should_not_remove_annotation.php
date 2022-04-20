<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php80\Rector\Class_\DoctrineAnnotationClassToAttributeRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig
        ->ruleWithConfiguration(DoctrineAnnotationClassToAttributeRector::class, [
            DoctrineAnnotationClassToAttributeRector::REMOVE_ANNOTATIONS => false,
        ]);
};
