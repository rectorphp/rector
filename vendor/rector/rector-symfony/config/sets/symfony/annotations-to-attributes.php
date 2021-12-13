<?php

declare (strict_types=1);
namespace RectorPrefix20211213;

use Rector\Php80\Rector\Class_\AnnotationToAttributeRector;
use Rector\Php80\ValueObject\AnnotationToAttribute;
use Rector\Symfony\Set\SymfonySetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
// @see https://symfony.com/blog/new-in-symfony-5-2-constraints-as-php-attributes
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $containerConfigurator->import(\Rector\Symfony\Set\SymfonySetList::SYMFONY_52_VALIDATOR_ATTRIBUTES);
    $services = $containerConfigurator->services();
    $services->set(\Rector\Php80\Rector\Class_\AnnotationToAttributeRector::class)->configure([
        // @see https://symfony.com/blog/new-in-symfony-5-2-php-8-attributes
        new \Rector\Php80\ValueObject\AnnotationToAttribute('required', 'Symfony\\Contracts\\Service\\Attribute\\Required'),
        new \Rector\Php80\ValueObject\AnnotationToAttribute('Symfony\\Component\\Routing\\Annotation\\Route'),
    ]);
};
