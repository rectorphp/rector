<?php

declare(strict_types=1);

use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Symfony\Rector\ConstFetch\ConstraintUrlOptionRector;
use Rector\Symfony\Rector\MethodCall\ContainerBuilderCompileEnvArgumentRector;
use Rector\Symfony\Rector\MethodCall\FormIsValidRector;
use Rector\Symfony\Rector\MethodCall\ProcessBuilderGetProcessRector;
use Rector\Symfony\Rector\MethodCall\VarDumperTestTraitMethodArgsRector;
use Rector\Symfony\Rector\StaticCall\ProcessBuilderInstanceRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ConstraintUrlOptionRector::class);

    $services->set(FormIsValidRector::class);

    $services->set(VarDumperTestTraitMethodArgsRector::class);

    $services->set(ContainerBuilderCompileEnvArgumentRector::class);

    $services->set(ProcessBuilderInstanceRector::class);

    $services->set(ProcessBuilderGetProcessRector::class);

    $services->set(RenameClassRector::class)
        ->call('configure', [[
            RenameClassRector::OLD_TO_NEW_CLASSES => [
                'Symfony\Component\Validator\Tests\Constraints\AbstractConstraintValidatorTest' => 'Symfony\Component\Validator\Test\ConstraintValidatorTestCase',
                'Symfony\Component\Process\ProcessBuilder' => 'Symfony\Component\Process\Process',
            ],
        ]]);
};
