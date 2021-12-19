<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\Php80\Rector\Class_\AnnotationToAttributeRector;
use Rector\Php80\ValueObject\AnnotationToAttribute;
use Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\GenericAnnotation;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::PHP_VERSION_FEATURES, PhpVersionFeature::NEW_INITIALIZERS - 1);

    $services = $containerConfigurator->services();

    $services->set(AnnotationToAttributeRector::class)
        ->configure([
            // use always this annotation to test inner part of annotation - arguments, arrays, calls...
            new AnnotationToAttribute(GenericAnnotation::class),

            new AnnotationToAttribute('inject', 'Nette\DI\Attributes\Inject'),
            new AnnotationToAttribute('Symfony\Component\Routing\Annotation\Route'),

            // doctrine
            new AnnotationToAttribute('Doctrine\ORM\Mapping\Entity'),
            new AnnotationToAttribute('Doctrine\ORM\Mapping\ManyToMany'),
            new AnnotationToAttribute('Doctrine\ORM\Mapping\Table'),
            new AnnotationToAttribute('Doctrine\ORM\Mapping\UniqueConstraint'),
            new AnnotationToAttribute('Doctrine\ORM\Mapping\JoinColumns'),
            new AnnotationToAttribute('Doctrine\ORM\Mapping\JoinColumn'),

            // validation
            new AnnotationToAttribute('Symfony\Component\Validator\Constraints\All'),
            new AnnotationToAttribute('Symfony\Component\Validator\Constraints\Length'),

            // test for alias used
            new AnnotationToAttribute(
                'Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\UseAlias\TestSmth'
            ),
            new AnnotationToAttribute(
                'Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\UseAlias\TestOther'
            ),
        ]);
};
