<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Restoration\Rector\Namespace_\CompleteImportForPartialAnnotationRector;
use Rector\Restoration\ValueObject\CompleteImportForPartialAnnotation;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig
        ->ruleWithConfiguration(
            CompleteImportForPartialAnnotationRector::class,
            [new CompleteImportForPartialAnnotation('Doctrine\ORM\Mapping', 'ORM')]
        );
};
