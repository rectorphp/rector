<?php

declare (strict_types=1);
namespace RectorPrefix202208;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Symfony\Rector\ConstFetch\ConstraintUrlOptionRector;
use Rector\Symfony\Rector\MethodCall\ContainerBuilderCompileEnvArgumentRector;
use Rector\Symfony\Rector\MethodCall\FormIsValidRector;
use Rector\Symfony\Rector\MethodCall\ProcessBuilderGetProcessRector;
use Rector\Symfony\Rector\MethodCall\VarDumperTestTraitMethodArgsRector;
use Rector\Symfony\Rector\StaticCall\ProcessBuilderInstanceRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(ConstraintUrlOptionRector::class);
    $rectorConfig->rule(FormIsValidRector::class);
    $rectorConfig->rule(VarDumperTestTraitMethodArgsRector::class);
    $rectorConfig->rule(ContainerBuilderCompileEnvArgumentRector::class);
    $rectorConfig->rule(ProcessBuilderInstanceRector::class);
    $rectorConfig->rule(ProcessBuilderGetProcessRector::class);
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, ['Symfony\\Component\\Validator\\Tests\\Constraints\\AbstractConstraintValidatorTest' => 'Symfony\\Component\\Validator\\Test\\ConstraintValidatorTestCase', 'Symfony\\Component\\Process\\ProcessBuilder' => 'Symfony\\Component\\Process\\Process']);
};
