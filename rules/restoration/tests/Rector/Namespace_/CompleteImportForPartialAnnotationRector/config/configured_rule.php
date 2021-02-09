<?php

use Rector\Restoration\Rector\Namespace_\CompleteImportForPartialAnnotationRector;
use Rector\Restoration\ValueObject\CompleteImportForPartialAnnotation;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(CompleteImportForPartialAnnotationRector::class)
        ->call('configure', [[
            CompleteImportForPartialAnnotationRector::USE_IMPORTS_TO_RESTORE => ValueObjectInliner::inline([
                new CompleteImportForPartialAnnotation('Doctrine\ORM\Mapping', 'ORM'),
            ]),
        ]]
    );
};
