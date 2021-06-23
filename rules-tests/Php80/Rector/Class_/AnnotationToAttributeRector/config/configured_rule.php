<?php

declare(strict_types=1);

use Rector\Php80\Rector\Class_\AnnotationToAttributeRector;
use Rector\Php80\ValueObject\AnnotationToAttribute;
use Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\GenericAnnotation;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(AnnotationToAttributeRector::class)
        ->call('configure', [[
            AnnotationToAttributeRector::ANNOTATION_TO_ATTRIBUTE => ValueObjectInliner::inline([
                // use always this annotation to test inner part of annotation - arguments, arrays, calls...
                new AnnotationToAttribute(GenericAnnotation::class),

                new AnnotationToAttribute('inject', 'Nette\DI\Attributes\Inject'),

                new AnnotationToAttribute('Symfony\Component\Routing\Annotation\Route'),
                new AnnotationToAttribute('Symfony\Component\Validator\Constraints\Choice'),
            ]),
        ]]);
};
