<?php

declare (strict_types=1);
namespace RectorPrefix202503;

use PHPStan\Type\StringType;
use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector;
use Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration;
# https://github.com/symfony/symfony/blob/5.0/UPGRADE-5.0.md
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->import(__DIR__ . '/symfony50-types.php');
    $rectorConfig->ruleWithConfiguration(AddParamTypeDeclarationRector::class, [new AddParamTypeDeclaration('Symfony\\Component\\Security\\Core\\User\\UserProviderInterface', 'loadUserByUsername', 0, new StringType()), new AddParamTypeDeclaration('Symfony\\Component\\Security\\Core\\User\\UserProviderInterface', 'supportsClass', 0, new StringType()), new AddParamTypeDeclaration('Symfony\\Bridge\\Doctrine\\Security\\User\\EntityUserProvider', 'loadUserByUsername', 0, new StringType()), new AddParamTypeDeclaration('Symfony\\Bridge\\Doctrine\\Security\\User\\EntityUserProvider', 'supportsClass', 0, new StringType())]);
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, ['Symfony\\Component\\Debug\\Debug' => 'Symfony\\Component\\ErrorHandler\\Debug']);
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [new MethodCallRename('Symfony\\Component\\Console\\Application', 'renderException', 'renderThrowable'), new MethodCallRename('Symfony\\Component\\Console\\Application', 'doRenderException', 'doRenderThrowable')]);
};
