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
                new AnnotationToAttribute('inject', 'Nette\DI\Attributes\Inject'),

                new AnnotationToAttribute('Symfony\Component\Routing\Annotation\Route'),
                new AnnotationToAttribute('Symfony\Component\Validator\Constraints\Choice'),

                new AnnotationToAttribute('Doctrine\ORM\Mapping\Id'),
                new AnnotationToAttribute('Doctrine\ORM\Mapping\Column'),
                new AnnotationToAttribute('ApiPlatform\Core\Annotation\ApiResource'),
            ]),
        ]]);
};
