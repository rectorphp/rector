<?php

declare (strict_types=1);
namespace RectorPrefix20220531;

use Rector\Config\RectorConfig;
use Rector\Php80\Rector\Class_\AnnotationToAttributeRector;
use Rector\Php80\ValueObject\AnnotationToAttribute;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(\Rector\Php80\Rector\Class_\AnnotationToAttributeRector::class, [
        // nette 3.0+, see https://github.com/nette/application/commit/d2471134ed909210de8a3e8559931902b1bee67b#diff-457507a8bdc046dd4f3a4aa1ca51794543fbb1e06f03825ab69ee864549a570c
        new \Rector\Php80\ValueObject\AnnotationToAttribute('inject', 'Nette\\DI\\Attributes\\Inject'),
        new \Rector\Php80\ValueObject\AnnotationToAttribute('persistent', 'Nette\\Application\\Attributes\\Persistent'),
        new \Rector\Php80\ValueObject\AnnotationToAttribute('crossOrigin', 'Nette\\Application\\Attributes\\CrossOrigin'),
    ]);
};
