<?php

declare(strict_types=1);

use Rector\DowngradePhp80\Rector\Class_\DowngradeAttributeToAnnotationRector;
use Rector\DowngradePhp80\ValueObject\DowngradeAttributeToAnnotation;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(DowngradeAttributeToAnnotationRector::class)
        ->call('configure', [[
            DowngradeAttributeToAnnotationRector::ATTRIBUTE_TO_ANNOTATION => ValueObjectInliner::inline([
                new DowngradeAttributeToAnnotation(
                    'Symfony\Component\Routing\Annotation\Route',
                    'Symfony\Component\Routing\Annotation\Route'
                ),
                new DowngradeAttributeToAnnotation('Symfony\Contracts\Service\Attribute\Required', 'required'),
            ]),
        ]]);
};
