<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Core\Configuration\Option;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\Php80\Rector\Class_\AnnotationToAttributeRector;
use Rector\Php80\ValueObject\AnnotationToAttribute;
use Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\GenericAnnotation;
use Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\SourcePhp81\All;
use Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\SourcePhp81\Length;
use Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\SourcePhp81\NotNull;
use Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\SourcePhp81\NotNumber;

return static function (RectorConfig $rectorConfig): void {
    // covers https://wiki.php.net/rfc/new_in_initializers#nested_attributes
    $parameters = $rectorConfig->parameters();
    $parameters->set(Option::PHP_VERSION_FEATURES, PhpVersionFeature::NEW_INITIALIZERS);

    $services = $rectorConfig->services();
    $services->set(AnnotationToAttributeRector::class)
        ->configure([
            new AnnotationToAttribute(All::class),
            new AnnotationToAttribute(Length::class),
            new AnnotationToAttribute(NotNull::class),
            new AnnotationToAttribute(NotNumber::class),
            new AnnotationToAttribute(GenericAnnotation::class),
            new AnnotationToAttribute('Doctrine\ORM\Mapping\Table'),
            new AnnotationToAttribute('Doctrine\ORM\Mapping\Index'),
            new AnnotationToAttribute('Doctrine\ORM\Mapping\UniqueConstraint'),
        ]);
};
