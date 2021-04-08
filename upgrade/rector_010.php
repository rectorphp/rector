<?php

declare(strict_types=1);

use Rector\Renaming\Rector\ClassConstFetch\RenameClassConstFetchRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\RenameClassAndConstFetch;
use Rector\Renaming\ValueObject\RenameClassConstFetch;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    // @todo add wrap argument on method call
    // nodeTypeResolver -> isMethodStaticCallOrClassMethodObjectType -> 2nd argument to ObjectType
    // nodeTypeResolver -> isObjectType -> 2nd argument to ObjectType

    $services->set(RenameClassRector::class)
        ->call('configure', [[
            'Rector\Core\Console\Command\AbstractCommand' => 'Symfony\Component\Console\Command\Command',
            'Rector\Testing\PHPUnit\AbstractCommunityRectorTestCase' => 'Rector\Testing\PHPUnit\AbstractRectorTestCase',
            'Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwareParamTagValueNode' => 'Rector\BetterPhpDocParser\ValueObject\PhpDoc\VariadicAwareParamTagValueNode',
            // ...
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
