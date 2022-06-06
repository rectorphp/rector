<?php

declare (strict_types=1);
namespace RectorPrefix20220606;

use RectorPrefix20220606\Rector\Config\RectorConfig;
use RectorPrefix20220606\Rector\Php80\Rector\Class_\AnnotationToAttributeRector;
use RectorPrefix20220606\Rector\Php80\ValueObject\AnnotationToAttribute;
use RectorPrefix20220606\Rector\Symfony\Set\SymfonySetList;
// @see https://symfony.com/blog/new-in-symfony-5-2-constraints-as-php-attributes
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->sets([SymfonySetList::SYMFONY_52_VALIDATOR_ATTRIBUTES]);
    $rectorConfig->ruleWithConfiguration(AnnotationToAttributeRector::class, [
        // @see https://symfony.com/blog/new-in-symfony-5-2-php-8-attributes
        new AnnotationToAttribute('required', 'Symfony\\Contracts\\Service\\Attribute\\Required'),
        new AnnotationToAttribute('Symfony\\Component\\Routing\\Annotation\\Route'),
        // see Symfony 5.2+ https://github.com/symfony/doctrine-bridge/commit/02d2cf4743331e6b69ffd1d68e09b7e2dc417201#diff-1a16e2739e51eab000116d0542bd0226cea59a6d64711740ed7ce14769f95d1b
        new AnnotationToAttribute('Symfony\\Bridge\\Doctrine\\Validator\\Constraints\\UniqueEntity'),
    ]);
};
