<?php

declare (strict_types=1);
namespace RectorPrefix202411;

use Rector\Config\RectorConfig;
use Rector\Php80\Rector\Class_\AnnotationToAttributeRector;
use Rector\Php80\ValueObject\AnnotationToAttribute;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(AnnotationToAttributeRector::class, [
        // @see https://github.com/FriendsOfSymfony/FOSRestBundle/pull/2325
        new AnnotationToAttribute('FOS\\RestBundle\\Controller\\Annotations\\Copy'),
        new AnnotationToAttribute('FOS\\RestBundle\\Controller\\Annotations\\Delete'),
        new AnnotationToAttribute('FOS\\RestBundle\\Controller\\Annotations\\Get'),
        new AnnotationToAttribute('FOS\\RestBundle\\Controller\\Annotations\\Head'),
        new AnnotationToAttribute('FOS\\RestBundle\\Controller\\Annotations\\Link'),
        new AnnotationToAttribute('FOS\\RestBundle\\Controller\\Annotations\\Lock'),
        new AnnotationToAttribute('FOS\\RestBundle\\Controller\\Annotations\\Mkcol'),
        new AnnotationToAttribute('FOS\\RestBundle\\Controller\\Annotations\\Move'),
        new AnnotationToAttribute('FOS\\RestBundle\\Controller\\Annotations\\Options'),
        new AnnotationToAttribute('FOS\\RestBundle\\Controller\\Annotations\\Patch'),
        new AnnotationToAttribute('FOS\\RestBundle\\Controller\\Annotations\\Post'),
        new AnnotationToAttribute('FOS\\RestBundle\\Controller\\Annotations\\PropFind'),
        new AnnotationToAttribute('FOS\\RestBundle\\Controller\\Annotations\\PropPatch'),
        new AnnotationToAttribute('FOS\\RestBundle\\Controller\\Annotations\\Put'),
        new AnnotationToAttribute('FOS\\RestBundle\\Controller\\Annotations\\Route'),
        new AnnotationToAttribute('FOS\\RestBundle\\Controller\\Annotations\\Unlink'),
        new AnnotationToAttribute('FOS\\RestBundle\\Controller\\Annotations\\Unlock'),
        // @see https://github.com/FriendsOfSymfony/FOSRestBundle/pull/2326
        new AnnotationToAttribute('FOS\\RestBundle\\Controller\\Annotations\\View'),
        // @see https://github.com/FriendsOfSymfony/FOSRestBundle/pull/2327
        new AnnotationToAttribute('FOS\\RestBundle\\Controller\\Annotations\\FileParam'),
        new AnnotationToAttribute('FOS\\RestBundle\\Controller\\Annotations\\QueryParam'),
        new AnnotationToAttribute('FOS\\RestBundle\\Controller\\Annotations\\RequestParam'),
    ]);
};
