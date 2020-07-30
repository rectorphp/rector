<?php

declare(strict_types=1);

use Rector\Renaming\Rector\Class_\RenameClassRector;
use Rector\Symfony\Rector\DependencyInjection\ContainerBuilderCompileEnvArgumentRector;
use Rector\Symfony\Rector\Form\FormIsValidRector;
use Rector\Symfony\Rector\Process\ProcessBuilderGetProcessRector;
use Rector\Symfony\Rector\Process\ProcessBuilderInstanceRector;
use Rector\Symfony\Rector\Validator\ConstraintUrlOptionRector;
use Rector\Symfony\Rector\VarDumper\VarDumperTestTraitMethodArgsRector;
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
