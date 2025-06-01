<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, ['Symfony\\Component\\Validator\\Constraints\\Collection\\Optional' => 'Symfony\\Component\\Validator\\Constraints\\Optional', 'Symfony\\Component\\Validator\\Constraints\\Collection\\Required' => 'Symfony\\Component\\Validator\\Constraints\\Required', 'Symfony\\Component\\Validator\\MetadataInterface' => 'Symfony\\Component\\Validator\\Mapping\\MetadataInterface', 'Symfony\\Component\\Validator\\PropertyMetadataInterface' => 'Symfony\\Component\\Validator\\Mapping\\PropertyMetadataInterface', 'Symfony\\Component\\Validator\\PropertyMetadataContainerInterface' => 'Symfony\\Component\\Validator\\Mapping\\ClassMetadataInterface', 'Symfony\\Component\\Validator\\ClassBasedInterface' => 'Symfony\\Component\\Validator\\Mapping\\ClassMetadataInterface', 'Symfony\\Component\\Validator\\Mapping\\ElementMetadata' => 'Symfony\\Component\\Validator\\Mapping\\GenericMetadata', 'Symfony\\Component\\Validator\\ExecutionContextInterface' => 'Symfony\\Component\\Validator\\Context\\ExecutionContextInterface', 'Symfony\\Component\\Validator\\Mapping\\ClassMetadataFactory' => 'Symfony\\Component\\Validator\\Mapping\\Factory\\LazyLoadingMetadataFactory', 'Symfony\\Component\\Validator\\Mapping\\MetadataFactoryInterface' => 'Symfony\\Component\\Validator\\Mapping\\Factory\\MetadataFactoryInterface']);
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [new MethodCallRename('Symfony\\Component\\Validator\\ConstraintViolationInterface', 'getMessageParameters', 'getParameters'), new MethodCallRename('Symfony\\Component\\Validator\\ConstraintViolationInterface', 'getMessagePluralization', 'getPlural'), new MethodCallRename('Symfony\\Component\\Validator\\ConstraintViolation', 'getMessageParameters', 'getParameters'), new MethodCallRename('Symfony\\Component\\Validator\\ConstraintViolation', 'getMessagePluralization', 'getPlural')]);
};
