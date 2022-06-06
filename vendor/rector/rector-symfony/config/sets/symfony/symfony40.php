<?php

declare (strict_types=1);
namespace RectorPrefix20220606;

use RectorPrefix20220606\Rector\Config\RectorConfig;
use RectorPrefix20220606\Rector\Renaming\Rector\Name\RenameClassRector;
use RectorPrefix20220606\Rector\Symfony\Rector\ConstFetch\ConstraintUrlOptionRector;
use RectorPrefix20220606\Rector\Symfony\Rector\MethodCall\ContainerBuilderCompileEnvArgumentRector;
use RectorPrefix20220606\Rector\Symfony\Rector\MethodCall\FormIsValidRector;
use RectorPrefix20220606\Rector\Symfony\Rector\MethodCall\ProcessBuilderGetProcessRector;
use RectorPrefix20220606\Rector\Symfony\Rector\MethodCall\VarDumperTestTraitMethodArgsRector;
use RectorPrefix20220606\Rector\Symfony\Rector\StaticCall\ProcessBuilderInstanceRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(ConstraintUrlOptionRector::class);
    $rectorConfig->rule(FormIsValidRector::class);
    $rectorConfig->rule(VarDumperTestTraitMethodArgsRector::class);
    $rectorConfig->rule(ContainerBuilderCompileEnvArgumentRector::class);
    $rectorConfig->rule(ProcessBuilderInstanceRector::class);
    $rectorConfig->rule(ProcessBuilderGetProcessRector::class);
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, ['Symfony\\Component\\Validator\\Tests\\Constraints\\AbstractConstraintValidatorTest' => 'Symfony\\Component\\Validator\\Test\\ConstraintValidatorTestCase', 'Symfony\\Component\\Process\\ProcessBuilder' => 'Symfony\\Component\\Process\\Process']);
};
