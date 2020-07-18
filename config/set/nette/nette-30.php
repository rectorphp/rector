<?php

declare(strict_types=1);

use Rector\DeadCode\Rector\StaticCall\RemoveParentCallWithoutParentRector;
use Rector\Nette\Rector\MethodCall\AddDatePickerToDateControlRector;
use Rector\Nette\Rector\MethodCall\SetClassWithArgumentToSetFactoryRector;
use Rector\Renaming\Rector\Class_\RenameClassRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->import(__DIR__ . '/nette-30/*');

    $services = $containerConfigurator->services();

    # Control class has remove __construct(), e.g. https://github.com/Pixidos/GPWebPay/pull/16/files#diff-fdc8251950f85c5467c63c249df05786
    $services->set(RemoveParentCallWithoutParentRector::class);

    $services->set(RenameClassRector::class)
        ->arg('$oldToNewClasses', [
            # nextras/forms was split into 2 packages
            'Nextras\FormComponents\Controls\DatePicker' => 'Nextras\FormComponents\Controls\DateControl',
            # @see https://github.com/nette/di/commit/a0d361192f8ac35f1d9f82aab7eb351e4be395ea
            'Nette\DI\ServiceDefinition' => 'Nette\DI\Definitions\ServiceDefinition',
            'Nette\DI\Statement' => 'Nette\DI\Definitions\Statement',
        ]);

    $services->set(RenameMethodRector::class)
        ->arg('$oldToNewMethodsByClass', [
            'Nette\Forms\Controls\BaseControl' => [
                # see https://github.com/nette/forms/commit/b99385aa9d24d729a18f6397a414ea88eab6895a
                'setType' => 'setHtmlType',
                'setAttribute' => 'setHtmlAttribute',
            ],
            'Nette\DI\Definitions\ServiceDefinition' => [
                # see https://github.com/nette/di/commit/1705a5db431423fc610a6f339f88dead1b5dc4fb
                'setClass' => 'setType',
                'getClass' => 'getType',
            ],
            'Nette\DI\Definitions\Definition' => [
                'isAutowired' => 'getAutowired',
            ],
        ]);

    $services->set(AddDatePickerToDateControlRector::class);

    $services->set(SetClassWithArgumentToSetFactoryRector::class);
};
