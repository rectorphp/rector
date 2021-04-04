<?php

declare(strict_types=1);

use Rector\Php80\Rector\Class_\AnnotationToAttributeRector;
use Rector\Php80\ValueObject\AnnotationToAttribute;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(AnnotationToAttributeRector::class)
        ->call('configure', [[
            AnnotationToAttributeRector::ANNOTATION_TO_ATTRIBUTE => ValueObjectInliner::inline([
                // nette 3.0+, see https://github.com/nette/application/commit/d2471134ed909210de8a3e8559931902b1bee67b#diff-457507a8bdc046dd4f3a4aa1ca51794543fbb1e06f03825ab69ee864549a570c
                new AnnotationToAttribute('inject', 'Nette\DI\Attributes\Inject'),
                new AnnotationToAttribute('persistent', 'Nette\Application\Attributes\Persistent'),
                new AnnotationToAttribute('crossOrigin', 'Nette\Application\Attributes\CrossOrigin'),

                // symfony
                new AnnotationToAttribute('required', 'Symfony\Contracts\Service\Attribute\Required'),
                new AnnotationToAttribute(
                    'Symfony\Component\Routing\Annotation\Route',
                    'Symfony\Component\Routing\Annotation\Route'
                ),

                // symfony/validation
                new AnnotationToAttribute(
                    'Symfony\Component\Validator\Constraints\Email',
                    'Symfony\Component\Validator\Constraints\Email'
                ),
                new AnnotationToAttribute(
                    'Symfony\Component\Validator\Constraints\Range',
                    'Symfony\Component\Validator\Constraints\Range'
                ),
            ]),
        ]]);
};
