<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php80\Rector\Class_\AnnotationToAttributeRector;
use Rector\Php80\ValueObject\AnnotationToAttribute;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();

    $services->set(AnnotationToAttributeRector::class)
        ->configure([new AnnotationToAttribute('Symfony\Component\Routing\Annotation\Route')]);
};
