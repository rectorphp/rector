<?php

declare(strict_types=1);

use Rector\Renaming\Rector\Class_\RenameClassRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RenameMethodRector::class)
        ->arg('$oldToNewMethodsByClass', [
            'Symfony\Component\Console\Helper\TableStyle' => [
                # https://github.com/symfony/symfony/blob/master/UPGRADE-4.1.md
                # https://github.com/symfony/symfony/commit/463f986c28a497571967e37c1314e9911f1ef6ba
                'setHorizontalBorderChar' => 'setHorizontalBorderChars',
                'setVerticalBorderChar' => 'setVerticalBorderChars',
                'getVerticalBorderChar' => [
                    # special case to "getVerticalBorderChar" → "getBorderChars()[3]"
                    'name' => 'getBorderChars',
                    'array_key' => 3,
                ],
                'getHorizontalBorderChar' => [
                    'name' => 'getBorderChars',
                    'array_key' => 2,
                ],
                'setCrossingChar' => 'setDefaultCrossingChar',
            ],
            'Symfony\Component\HttpFoundation\File\UploadedFile' => [
                'getClientSize' => 'getSize',
            ],
            'Symfony\Component\Workflow\DefinitionBuilder' => [
                'reset' => 'clear',
                'add' => 'addWorkflow',
            ],
        ]);

    $services->set(RenameClassRector::class)
        ->arg('$oldToNewClasses', [
            # https://github.com/symfony/symfony/commit/07dd09db59e2f2a86a291d00d978169d9059e307
            'Symfony\Bundle\FrameworkBundle\DataCollector\RequestDataCollector' => 'Symfony\Component\HttpKernel\DataCollector\RequestDataCollector',
            'Symfony\Component\Workflow\SupportStrategy\SupportStrategyInterface' => 'Symfony\Component\Workflow\SupportStrategy\WorkflowSupportStrategyInterface',
            'Symfony\Component\Workflow\SupportStrategy\ClassInstanceSupportStrategy' => 'Symfony\Component\Workflow\SupportStrategy\InstanceOfSupportStrategy',
        ]);
};
