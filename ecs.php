<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\Import\NoUnusedImportsFixer;
use PhpCsFixer\Fixer\Operator\UnaryOperatorSpacesFixer;
use PhpCsFixer\Fixer\Phpdoc\GeneralPhpdocAnnotationRemoveFixer;
use PhpCsFixer\Fixer\Phpdoc\NoSuperfluousPhpdocTagsFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocTypesFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitStrictFixer;
use PhpCsFixer\Fixer\ReturnNotation\ReturnAssignmentFixer;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\CodingStandard\Fixer\Commenting\ParamReturnAndVarTagMalformsFixer;
use Symplify\CodingStandard\Fixer\LineLength\DocBlockLineLengthFixer;
use Symplify\EasyCodingStandard\ValueObject\Option;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(GeneralPhpdocAnnotationRemoveFixer::class)
        ->call('configure', [[
            'annotations' => [
                'throw',
                'throws',
                'author',
                'authors',
                'package',
                'group',
                'required',
                'phpstan-ignore-line',
                'phpstan-ignore-next-line',
            ],
        ]]);

    $services->set(NoSuperfluousPhpdocTagsFixer::class)
        ->call('configure', [[
            'allow_mixed' => true,
        ]]);

    $parameters = $containerConfigurator->parameters();

    // bleeding edge 16x faster speed
    $parameters->set(Option::PARALLEL, true);

    $parameters->set(Option::PATHS, [
        __DIR__ . '/bin',
        __DIR__ . '/src',
        __DIR__ . '/packages',
        __DIR__ . '/packages-tests',
        __DIR__ . '/rules',
        __DIR__ . '/rules-tests',
        __DIR__ . '/tests',
        __DIR__ . '/utils',
        __DIR__ . '/config',
        __DIR__ . '/ecs.php',
        __DIR__ . '/rector.php',
        __DIR__ . '/scoper.php',
    ]);

    $parameters->set(Option::SKIP, [
        '*/Source/*',
        '*/Fixture/*',
        '*/Expected/*',

        // fixed in master
        ParamReturnAndVarTagMalformsFixer::class,

        GeneralPhpdocAnnotationRemoveFixer::class => [
            __DIR__ . '/src/Rector/AbstractRector.php',
            '*TypeInferer*',
            '*TypeResolver*',
            '*NameResolver*',
            '*Mapper*',
            // allowed @required
            __DIR__ . '/packages/StaticTypeMapper/Naming/NameScopeFactory.php',
            __DIR__ . '/packages/NodeTypeResolver/NodeTypeResolver.php',
            __DIR__ . '/packages/BetterPhpDocParser/PhpDocParser/StaticDoctrineAnnotationParser/PlainValueParser.php',
        ],

        UnaryOperatorSpacesFixer::class,

        // buggy - @todo fix on Symplify master
        DocBlockLineLengthFixer::class,

        // buggy on closure in interface union
        NoUnusedImportsFixer::class => [__DIR__ . '/rules/Naming/Contract/RenameParamValueObjectInterface.php'],

        // breaks on-purpose annotated variables
        ReturnAssignmentFixer::class,

        PhpdocTypesFixer::class => [__DIR__ . '/rules/Php74/Rector/Double/RealToFloatTypeCastRector.php'],

        // buggy on "Float" class
        PhpUnitStrictFixer::class => [
            __DIR__ . '/packages-tests/BetterPhpDocParser/PhpDocInfo/PhpDocInfo/PhpDocInfoTest.php',
            __DIR__ . '/tests/PhpParser/Node/NodeFactoryTest.php',
            __DIR__ . '/packages-tests/BetterPhpDocParser/PhpDocParser/StaticDoctrineAnnotationParser/StaticDoctrineAnnotationParserTest.php',
            '*TypeResolverTest.php',
        ],
    ]);

    // import SetList here on purpose to avoid overridden by existing Skip Option in current config
    $containerConfigurator->import(SetList::PSR_12);
    $containerConfigurator->import(SetList::SYMPLIFY);
    $containerConfigurator->import(SetList::COMMON);
    $containerConfigurator->import(SetList::CLEAN_CODE);

    $parameters->set(Option::LINE_ENDING, "\n");
};
