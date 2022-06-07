<?php

declare (strict_types=1);
namespace RectorPrefix20220607;

use Rector\Config\RectorConfig;
use Rector\Php80\Rector\Class_\AnnotationToAttributeRector;
use Rector\Php80\ValueObject\AnnotationToAttribute;
return static function (RectorConfig $rectorConfig) : void {
    // @see https://github.com/sensiolabs/SensioFrameworkExtraBundle/pull/707
    $rectorConfig->ruleWithConfiguration(AnnotationToAttributeRector::class, [new AnnotationToAttribute('RectorPrefix20220607\\Sensio\\Bundle\\FrameworkExtraBundle\\Configuration\\Cache'), new AnnotationToAttribute('RectorPrefix20220607\\Sensio\\Bundle\\FrameworkExtraBundle\\Configuration\\Entity'), new AnnotationToAttribute('RectorPrefix20220607\\Sensio\\Bundle\\FrameworkExtraBundle\\Configuration\\IsGranted'), new AnnotationToAttribute('RectorPrefix20220607\\Sensio\\Bundle\\FrameworkExtraBundle\\Configuration\\ParamConverter'), new AnnotationToAttribute('RectorPrefix20220607\\Sensio\\Bundle\\FrameworkExtraBundle\\Configuration\\Security'), new AnnotationToAttribute('RectorPrefix20220607\\Sensio\\Bundle\\FrameworkExtraBundle\\Configuration\\Template')]);
};
