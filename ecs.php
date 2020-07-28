<?php

declare(strict_types=1);

use PHP_CodeSniffer\Standards\PSR2\Sniffs\Methods\MethodDeclarationSniff;
use PhpCsFixer\Fixer\Import\GlobalNamespaceImportFixer;
use PhpCsFixer\Fixer\Operator\UnaryOperatorSpacesFixer;
use PhpCsFixer\Fixer\Phpdoc\GeneralPhpdocAnnotationRemoveFixer;
use PhpCsFixer\Fixer\Phpdoc\NoSuperfluousPhpdocTagsFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocTypesFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitStrictFixer;
use PhpCsFixer\Fixer\Strict\StrictComparisonFixer;
use SlevomatCodingStandard\Sniffs\Commenting\DisallowCommentAfterCodeSniff;
use SlevomatCodingStandard\Sniffs\ControlStructures\AssignmentInConditionSniff;
use SlevomatCodingStandard\Sniffs\Namespaces\ReferenceUsedNamesOnlySniff;
use SlevomatCodingStandard\Sniffs\Namespaces\UseSpacingSniff;
use SlevomatCodingStandard\Sniffs\Variables\UnusedVariableSniff;
use SlevomatCodingStandard\Sniffs\Whitespaces\DuplicateSpacesSniff;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\CodingStandard\Fixer\ArrayNotation\StandaloneLineInMultilineArrayFixer;
use Symplify\CodingStandard\Fixer\LineLength\LineLengthFixer;
use Symplify\CodingStandard\Sniffs\Debug\CommentedOutCodeSniff;
use Symplify\EasyCodingStandard\Configuration\Option;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(DisallowCommentAfterCodeSniff::class);

    $services->set(DuplicateSpacesSniff::class);

    $services->set(UseSpacingSniff::class);

    $services->set(StandaloneLineInMultilineArrayFixer::class);

    $services->set(LineLengthFixer::class);

    $services->set(GeneralPhpdocAnnotationRemoveFixer::class)
        ->call('configure', [
            [
                'annotations' => ['throws', 'author', 'package', 'group'],
            ],
        ]);

    $services->set(LineLengthFixer::class);

    $services->set(NoSuperfluousPhpdocTagsFixer::class)
        ->call('configure', [[
            'allow_mixed' => true,
        ]]);

    $parameters = $containerConfigurator->parameters();

    $parameters->set(Option::PATHS, [
        __DIR__ . '/bin',
        __DIR__ . '/src',
        __DIR__ . '/packages',
        __DIR__ . '/rules',
        __DIR__ . '/tests',
        __DIR__ . '/utils',
        __DIR__ . '/compiler',
        __DIR__ . '/config',
        __DIR__ . '/ecs.php',
        __DIR__ . '/rector.php',
        __DIR__ . '/rector-ci.php',
        __DIR__ . '/config/set',
    ]);

    $parameters->set(Option::SETS, ['psr12', 'php70', 'php71', 'symplify', 'common', 'clean-code']);

    $parameters->set(Option::EXCLUDE_PATHS, [
        '*/Source/*',
        '*/Fixture/*', '*/Expected/*',
        # generated from /vendor
        __DIR__ . '/packages/doctrine-annotation-generated/src/ConstantPreservingDocParser.php',
        __DIR__ . '/packages/doctrine-annotation-generated/src/ConstantPreservingAnnotationReader.php',
        // template files
        __DIR__ . '/packages/rector-generator/templates/*',
    ]);

    $parameters->set(Option::SKIP, [
        GlobalNamespaceImportFixer::class => null,
        MethodDeclarationSniff::class . '.Underscore' => null,
        PhpdocTypesFixer::class => [__DIR__ . '/rules/php74/src/Rector/Double/RealToFloatTypeCastRector.php'],
        AssignmentInConditionSniff::class . '.FoundInWhileCondition' => null,
        UnusedVariableSniff::class . '.' . UnusedVariableSniff::CODE_UNUSED_VARIABLE => [
            __DIR__ . '/rules/php-office/src/Rector/MethodCall/IncreaseColumnIndexRector.php',
        ],
        CommentedOutCodeSniff::class . '.Found' => [
            __DIR__ . '/rules/php72/src/Rector/Each/ListEachRector.php',
            __DIR__ . '/rules/dead-code/src/Rector/ClassMethod/RemoveOverriddenValuesRector.php',
            __DIR__ . '/rules/php-spec-to-phpunit/src/Rector/MethodCall/PhpSpecPromisesToPHPUnitAssertRector.php',
        ],
        PhpUnitStrictFixer::class => [
            __DIR__ . '/packages/better-php-doc-parser/tests/PhpDocInfo/PhpDocInfo/PhpDocInfoTest.php',
            __DIR__ . '/tests/PhpParser/Node/NodeFactoryTest.php',
            '*TypeResolverTest.php',
        ],
        UnaryOperatorSpacesFixer::class => null,
        StrictComparisonFixer::class => [__DIR__ . '/packages/polyfill/src/ConditionEvaluator.php'],
        ReferenceUsedNamesOnlySniff::class . '.' . ReferenceUsedNamesOnlySniff::CODE_PARTIAL_USE => null,
        ReferenceUsedNamesOnlySniff::class . '.' . ReferenceUsedNamesOnlySniff::CODE_REFERENCE_VIA_FULLY_QUALIFIED_NAME => [
            __DIR__ . '/packages/better-php-doc-parser/src/PhpDocNodeFactory/Doctrine/Property_/DoctrineTargetEntityPhpDocNodeFactory.php',
            __DIR__ . '/packages/better-php-doc-parser/src/PhpDocNodeFactory/Doctrine/Property_/JoinTablePhpDocNodeFactory.php',
            __DIR__ . '/packages/better-php-doc-parser/src/PhpDocNodeFactory/Doctrine/Class_/TablePhpDocNodeFactory.php',
            __DIR__ . '/packages/better-php-doc-parser/src/PhpDocNodeFactory/JMS/JMSInjectPhpDocNodeFactory.php',
        ],
    ]);

    $parameters->set('line_ending', "\n");
};
