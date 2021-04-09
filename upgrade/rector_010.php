<?php

declare(strict_types=1);

use Rector\Arguments\Rector\MethodCall\ValueObjectWrapArgRector;
use Rector\Arguments\ValueObject\ValueObjectWrapArg;
use Rector\Renaming\Rector\ClassConstFetch\RenameClassConstFetchRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\RenameClassAndConstFetch;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ValueObjectWrapArgRector::class)
        ->call('configure', [[
            ValueObjectWrapArgRector::VALUE_OBJECT_WRAP_ARGS => ValueObjectInliner::inline([
                new ValueObjectWrapArg(
                    'Rector\NodeTypeResolver\NodeTypeResolver',
                    'isMethodStaticCallOrClassMethodObjectType',
                    1,
                    'PHPStan\Type\ObjectType'
                ),
                new ValueObjectWrapArg(
                    'Rector\NodeTypeResolver\NodeTypeResolver',
                    'isObjectType',
                    1,
                    'PHPStan\Type\ObjectType'
                ),
                new ValueObjectWrapArg(
                    'Rector\NodeTypeResolver\NodeTypeResolver',
                    'isObjectTypes',
                    1,
                    'PHPStan\Type\ObjectType'
                ),
                new ValueObjectWrapArg(
                    'Rector\Core\Rector\AbstractRector',
                    'isObjectType',
                    1,
                    'PHPStan\Type\ObjectType'
                ),
            ])
        ]]);

    $services->set(RenameClassRector::class)
        ->call('configure', [[
            'Rector\Core\Console\Command\AbstractCommand' => 'Symfony\Component\Console\Command\Command',
            'Rector\Testing\PHPUnit\AbstractCommunityRectorTestCase' => 'Rector\Testing\PHPUnit\AbstractRectorTestCase',
            'Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwareParamTagValueNode' => 'Rector\BetterPhpDocParser\ValueObject\PhpDoc\VariadicAwareParamTagValueNode',
            // @see https://github.com/rectorphp/rector/pull/5841
            'Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwarePhpDocTagNode' => 'PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode',
            'Rector\AttributeAwarePhpDoc\Ast\Type\AttributeAwareGenericTypeNode' => 'PHPStan\PhpDocParser\Ast\Type\GenericTypeNode',
            // etc.
        ]]);

    $services->set(RenameClassConstFetchRector::class)
        ->call('configure', [[
            RenameClassConstFetchRector::CLASS_CONSTANT_RENAME => ValueObjectInliner::inline([
                new RenameClassAndConstFetch(
                    'Rector\PHPStanStaticTypeMapper\PHPStanStaticTypeMapper',
                    'KIND_PARAM',
                    'Rector\PHPStanStaticTypeMapper\ValueObject\TypeKind',
                    'KIND_PARAM',
                )
            ])
        ]]);
};
