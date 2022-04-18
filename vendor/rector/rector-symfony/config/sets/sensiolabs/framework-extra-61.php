<?php

declare (strict_types=1);
namespace RectorPrefix20220418;

use Rector\Config\RectorConfig;
use Rector\Php80\Rector\Class_\AnnotationToAttributeRector;
use Rector\Php80\ValueObject\AnnotationToAttribute;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $services = $rectorConfig->services();
    // @see https://github.com/sensiolabs/SensioFrameworkExtraBundle/pull/707
    $services->set(\Rector\Php80\Rector\Class_\AnnotationToAttributeRector::class)->configure([new \Rector\Php80\ValueObject\AnnotationToAttribute('Sensio\\Bundle\\FrameworkExtraBundle\\Configuration\\Cache'), new \Rector\Php80\ValueObject\AnnotationToAttribute('Sensio\\Bundle\\FrameworkExtraBundle\\Configuration\\Entity'), new \Rector\Php80\ValueObject\AnnotationToAttribute('Sensio\\Bundle\\FrameworkExtraBundle\\Configuration\\IsGranted'), new \Rector\Php80\ValueObject\AnnotationToAttribute('Sensio\\Bundle\\FrameworkExtraBundle\\Configuration\\ParamConverter'), new \Rector\Php80\ValueObject\AnnotationToAttribute('Sensio\\Bundle\\FrameworkExtraBundle\\Configuration\\Security'), new \Rector\Php80\ValueObject\AnnotationToAttribute('Sensio\\Bundle\\FrameworkExtraBundle\\Configuration\\Template')]);
};
