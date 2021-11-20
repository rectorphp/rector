<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\Php80\Rector\Class_\AnnotationToAttributeRector;
use Rector\Php80\ValueObject\AnnotationToAttribute;
use Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\SourcePhp81\All;
use Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\SourcePhp81\Length;
use Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\SourcePhp81\NotNull;
use Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\SourcePhp81\NotNumber;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    // covers https://wiki.php.net/rfc/new_in_initializers#nested_attributes
    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::PHP_VERSION_FEATURES, PhpVersionFeature::NEW_INITIALIZERS);

    $services = $containerConfigurator->services();
    $services->set(AnnotationToAttributeRector::class)
        ->configure([
            AnnotationToAttributeRector::ANNOTATION_TO_ATTRIBUTE => [
                new AnnotationToAttribute(All::class),
                new AnnotationToAttribute(Length::class),
                new AnnotationToAttribute(NotNull::class),
                new AnnotationToAttribute(NotNumber::class),
            ],
        ]);
};
