<?php

return static function (
    \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator
): void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\Renaming\Rector\ClassMethod\RenameAnnotationRector::class)->call('configure', [[
        \Rector\Renaming\Rector\ClassMethod\RenameAnnotationRector::RENAMED_ANNOTATIONS_IN_TYPES => \Symplify\SymfonyPhpConfig\ValueObjectInliner::inline([
            

















            new \Rector\Renaming\ValueObject\RenameAnnotation(
                'PHPUnit\Framework\TestCase',
                'scenario',
                'test'
            ),
























            
        ]),
    ]]);
};
