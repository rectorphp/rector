<?php

declare (strict_types=1);
namespace RectorPrefix20220531;

use PHPStan\Type\VoidType;
use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration;
# https://github.com/doctrine/dbal/blob/master/UPGRADE.md#bc-break-changes-in-handling-string-and-binary-columns
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(\Rector\Renaming\Rector\MethodCall\RenameMethodRector::class, [new \Rector\Renaming\ValueObject\MethodCallRename('Doctrine\\DBAL\\Platforms\\AbstractPlatform', 'getVarcharTypeDeclarationSQL', 'getStringTypeDeclarationSQL'), new \Rector\Renaming\ValueObject\MethodCallRename('Doctrine\\DBAL\\Driver\\DriverException', 'getErrorCode', 'getCode')]);
    $rectorConfig->ruleWithConfiguration(\Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector::class, [new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('Doctrine\\DBAL\\Connection', 'ping', new \PHPStan\Type\VoidType())]);
    # https://github.com/doctrine/dbal/blob/master/UPGRADE.md#deprecated-abstractionresult
    $rectorConfig->ruleWithConfiguration(\Rector\Renaming\Rector\Name\RenameClassRector::class, ['Doctrine\\DBAL\\Abstraction\\Result' => 'Doctrine\\DBAL\\Result']);
};
