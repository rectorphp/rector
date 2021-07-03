<?php

declare (strict_types=1);
namespace RectorPrefix20210703;

use Rector\Arguments\Rector\MethodCall\ValueObjectWrapArgRector;
use Rector\Arguments\ValueObject\ValueObjectWrapArg;
use Rector\Renaming\Rector\ClassConstFetch\RenameClassConstFetchRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\RenameClassAndConstFetch;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\Arguments\Rector\MethodCall\ValueObjectWrapArgRector::class)->call('configure', [[\Rector\Arguments\Rector\MethodCall\ValueObjectWrapArgRector::VALUE_OBJECT_WRAP_ARGS => \Symplify\SymfonyPhpConfig\ValueObjectInliner::inline([new \Rector\Arguments\ValueObject\ValueObjectWrapArg('Rector\\NodeTypeResolver\\NodeTypeResolver', 'isMethodStaticCallOrClassMethodObjectType', 1, 'PHPStan\\Type\\ObjectType'), new \Rector\Arguments\ValueObject\ValueObjectWrapArg('Rector\\NodeTypeResolver\\NodeTypeResolver', 'isObjectType', 1, 'PHPStan\\Type\\ObjectType'), new \Rector\Arguments\ValueObject\ValueObjectWrapArg('Rector\\NodeTypeResolver\\NodeTypeResolver', 'isObjectTypes', 1, 'PHPStan\\Type\\ObjectType'), new \Rector\Arguments\ValueObject\ValueObjectWrapArg('Rector\\Core\\Rector\\AbstractRector', 'isObjectType', 1, 'PHPStan\\Type\\ObjectType')])]]);
    $services->set(\Rector\Renaming\Rector\Name\RenameClassRector::class)->call('configure', [[
        'Rector\\Core\\Console\\Command\\AbstractCommand' => 'Symfony\\Component\\Console\\Command\\Command',
        'Rector\\Testing\\PHPUnit\\AbstractCommunityRectorTestCase' => 'Rector\\Testing\\PHPUnit\\AbstractRectorTestCase',
        'Rector\\AttributeAwarePhpDoc\\Ast\\PhpDoc\\AttributeAwareParamTagValueNode' => 'Rector\\BetterPhpDocParser\\ValueObject\\PhpDoc\\VariadicAwareParamTagValueNode',
        // @see https://github.com/rectorphp/rector/pull/5841
        'Rector\\AttributeAwarePhpDoc\\Ast\\PhpDoc\\AttributeAwarePhpDocTagNode' => 'PHPStan\\PhpDocParser\\Ast\\PhpDoc\\PhpDocTagNode',
        'Rector\\AttributeAwarePhpDoc\\Ast\\Type\\AttributeAwareGenericTypeNode' => 'PHPStan\\PhpDocParser\\Ast\\Type\\GenericTypeNode',
    ]]);
    $services->set(\Rector\Renaming\Rector\ClassConstFetch\RenameClassConstFetchRector::class)->call('configure', [[\Rector\Renaming\Rector\ClassConstFetch\RenameClassConstFetchRector::CLASS_CONSTANT_RENAME => \Symplify\SymfonyPhpConfig\ValueObjectInliner::inline([new \Rector\Renaming\ValueObject\RenameClassAndConstFetch('Rector\\PHPStanStaticTypeMapper\\PHPStanStaticTypeMapper', 'KIND_PARAM', 'Rector\\PHPStanStaticTypeMapper\\ValueObject\\TypeKind', 'KIND_PARAM')])]]);
};
