<?php

declare (strict_types=1);
namespace RectorPrefix20220501;

use Rector\Php80\Rector\Class_\AnnotationToAttributeRector;
use Rector\Php80\ValueObject\AnnotationToAttribute;
// @see https://github.com/apitte/core/pull/161
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(\Rector\Php80\Rector\Class_\AnnotationToAttributeRector::class, [new \Rector\Php80\ValueObject\AnnotationToAttribute('Apitte\\Core\\Annotation\\Controller\\Id'), new \Rector\Php80\ValueObject\AnnotationToAttribute('Apitte\\Core\\Annotation\\Controller\\Method'), new \Rector\Php80\ValueObject\AnnotationToAttribute('Apitte\\Core\\Annotation\\Controller\\Negotiation'), new \Rector\Php80\ValueObject\AnnotationToAttribute('Apitte\\Core\\Annotation\\Controller\\OpenApi'), new \Rector\Php80\ValueObject\AnnotationToAttribute('Apitte\\Core\\Annotation\\Controller\\Path'), new \Rector\Php80\ValueObject\AnnotationToAttribute('Apitte\\Core\\Annotation\\Controller\\RequestBody'), new \Rector\Php80\ValueObject\AnnotationToAttribute('Apitte\\Core\\Annotation\\Controller\\RequestParameter'), new \Rector\Php80\ValueObject\AnnotationToAttribute('Apitte\\Core\\Annotation\\Controller\\Response'), new \Rector\Php80\ValueObject\AnnotationToAttribute('Apitte\\Core\\Annotation\\Controller\\Tag')]);
};
