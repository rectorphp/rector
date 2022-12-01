<?php

declare (strict_types=1);
namespace RectorPrefix202212;

use Rector\Config\RectorConfig;
use Rector\Php80\Rector\Class_\AnnotationToAttributeRector;
use Rector\Php80\ValueObject\AnnotationToAttribute;
return static function (RectorConfig $rectorConfig) : void {
    // @see https://github.com/sensiolabs/SensioFrameworkExtraBundle/pull/707
    $rectorConfig->ruleWithConfiguration(AnnotationToAttributeRector::class, [new AnnotationToAttribute('Sensio\\Bundle\\FrameworkExtraBundle\\Configuration\\Cache'), new AnnotationToAttribute('Sensio\\Bundle\\FrameworkExtraBundle\\Configuration\\Entity'), new AnnotationToAttribute('Sensio\\Bundle\\FrameworkExtraBundle\\Configuration\\IsGranted'), new AnnotationToAttribute('Sensio\\Bundle\\FrameworkExtraBundle\\Configuration\\ParamConverter'), new AnnotationToAttribute('Sensio\\Bundle\\FrameworkExtraBundle\\Configuration\\Security'), new AnnotationToAttribute('Sensio\\Bundle\\FrameworkExtraBundle\\Configuration\\Template')]);
};
