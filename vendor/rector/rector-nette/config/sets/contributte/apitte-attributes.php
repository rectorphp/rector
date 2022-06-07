<?php

declare (strict_types=1);
namespace RectorPrefix20220607;

use Rector\Php80\Rector\Class_\AnnotationToAttributeRector;
use Rector\Php80\ValueObject\AnnotationToAttribute;
// @see https://github.com/apitte/core/pull/161
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(AnnotationToAttributeRector::class, [new AnnotationToAttribute('RectorPrefix20220607\\Apitte\\Core\\Annotation\\Controller\\Id'), new AnnotationToAttribute('RectorPrefix20220607\\Apitte\\Core\\Annotation\\Controller\\Method'), new AnnotationToAttribute('RectorPrefix20220607\\Apitte\\Core\\Annotation\\Controller\\Negotiation'), new AnnotationToAttribute('RectorPrefix20220607\\Apitte\\Core\\Annotation\\Controller\\OpenApi'), new AnnotationToAttribute('RectorPrefix20220607\\Apitte\\Core\\Annotation\\Controller\\Path'), new AnnotationToAttribute('RectorPrefix20220607\\Apitte\\Core\\Annotation\\Controller\\RequestBody'), new AnnotationToAttribute('RectorPrefix20220607\\Apitte\\Core\\Annotation\\Controller\\RequestParameter'), new AnnotationToAttribute('RectorPrefix20220607\\Apitte\\Core\\Annotation\\Controller\\Response'), new AnnotationToAttribute('RectorPrefix20220607\\Apitte\\Core\\Annotation\\Controller\\Tag')]);
};
