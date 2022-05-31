<?php

declare (strict_types=1);
namespace RectorPrefix20220531;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Symfony\Rector\ConstFetch\ConstraintUrlOptionRector;
use Rector\Symfony\Rector\MethodCall\ContainerBuilderCompileEnvArgumentRector;
use Rector\Symfony\Rector\MethodCall\FormIsValidRector;
use Rector\Symfony\Rector\MethodCall\ProcessBuilderGetProcessRector;
use Rector\Symfony\Rector\MethodCall\VarDumperTestTraitMethodArgsRector;
use Rector\Symfony\Rector\StaticCall\ProcessBuilderInstanceRector;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(\Rector\Symfony\Rector\ConstFetch\ConstraintUrlOptionRector::class);
    $rectorConfig->rule(\Rector\Symfony\Rector\MethodCall\FormIsValidRector::class);
    $rectorConfig->rule(\Rector\Symfony\Rector\MethodCall\VarDumperTestTraitMethodArgsRector::class);
    $rectorConfig->rule(\Rector\Symfony\Rector\MethodCall\ContainerBuilderCompileEnvArgumentRector::class);
    $rectorConfig->rule(\Rector\Symfony\Rector\StaticCall\ProcessBuilderInstanceRector::class);
    $rectorConfig->rule(\Rector\Symfony\Rector\MethodCall\ProcessBuilderGetProcessRector::class);
    $rectorConfig->ruleWithConfiguration(\Rector\Renaming\Rector\Name\RenameClassRector::class, ['Symfony\\Component\\Validator\\Tests\\Constraints\\AbstractConstraintValidatorTest' => 'Symfony\\Component\\Validator\\Test\\ConstraintValidatorTestCase', 'Symfony\\Component\\Process\\ProcessBuilder' => 'Symfony\\Component\\Process\\Process']);
};
