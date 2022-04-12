<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Restoration\Rector\Namespace_\CompleteImportForPartialAnnotationRector;
use Rector\Restoration\ValueObject\CompleteImportForPartialAnnotation;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(CompleteImportForPartialAnnotationRector::class)
        ->configure([new CompleteImportForPartialAnnotation('Doctrine\ORM\Mapping', 'ORM')]);
};
