<?php

use Rector\Renaming\Rector\ClassMethod\RenameAnnotationRector;
use Rector\Renaming\ValueObject\RenameAnnotation;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(RenameAnnotationRector::class)->call('configure', [[
        RenameAnnotationRector::RENAMED_ANNOTATIONS_IN_TYPES => ValueObjectInliner::inline([

            new RenameAnnotation('PHPUnit\Framework\TestCase', 'scenario', 'test'),

        ]),
    ]]);
};
