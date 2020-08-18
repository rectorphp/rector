<?php

declare(strict_types=1);

use Rector\PHPUnit\Rector\Class_\RemoveDataProviderTestPrefixRector;
use Rector\Renaming\Rector\ClassMethod\RenameAnnotationRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->import(__DIR__ . '/phpunit-exception.php');

    $services = $containerConfigurator->services();

    $services->set(RenameAnnotationRector::class)
        ->call('configure', [[
            RenameAnnotationRector::CLASS_TO_ANNOTATION_MAP => [
                'PHPUnit\Framework\TestCase' => [
                    'scenario' => 'test',
                ],
            ],
        ]]);

    $services->set(RemoveDataProviderTestPrefixRector::class);
};
