<?php

declare (strict_types=1);
namespace RectorPrefix202308;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Symfony\Symfony40\Rector\ConstFetch\ConstraintUrlOptionRector;
use Rector\Symfony\Symfony40\Rector\MethodCall\ContainerBuilderCompileEnvArgumentRector;
use Rector\Symfony\Symfony40\Rector\MethodCall\FormIsValidRector;
use Rector\Symfony\Symfony40\Rector\MethodCall\ProcessBuilderGetProcessRector;
use Rector\Symfony\Symfony40\Rector\MethodCall\VarDumperTestTraitMethodArgsRector;
use Rector\Symfony\Symfony40\Rector\StaticCall\ProcessBuilderInstanceRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rules([ConstraintUrlOptionRector::class, FormIsValidRector::class, VarDumperTestTraitMethodArgsRector::class, ContainerBuilderCompileEnvArgumentRector::class, ProcessBuilderInstanceRector::class, ProcessBuilderGetProcessRector::class]);
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, ['Symfony\\Component\\Validator\\Tests\\Constraints\\AbstractConstraintValidatorTest' => 'Symfony\\Component\\Validator\\Test\\ConstraintValidatorTestCase', 'Symfony\\Component\\Process\\ProcessBuilder' => 'Symfony\\Component\\Process\\Process']);
};
