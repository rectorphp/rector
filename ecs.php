<?php

declare(strict_types=1);

use PHP_CodeSniffer\Standards\Generic\Sniffs\CodeAnalysis\AssignmentInConditionSniff;
use PhpCsFixer\Fixer\FunctionNotation\FunctionTypehintSpaceFixer;
use PhpCsFixer\Fixer\Phpdoc\GeneralPhpdocAnnotationRemoveFixer;
use PhpCsFixer\Fixer\Phpdoc\NoSuperfluousPhpdocTagsFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocTypesFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitStrictFixer;
use Symplify\CodingStandard\Fixer\LineLength\DocBlockLineLengthFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return static function (ECSConfig $ecsConfig): void {
    $ecsConfig->sets([SetList::PSR_12, SetList::SYMPLIFY, SetList::COMMON, SetList::CLEAN_CODE]);

    $ecsConfig->paths([
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
        __DIR__ . '/easy-ci.php',
        __DIR__ . '/rector.php',
        __DIR__ . '/scoper.php',
    ]);

    $ecsConfig->ruleWithConfiguration(NoSuperfluousPhpdocTagsFixer::class, [
        'allow_mixed' => true,
    ]);

    $ecsConfig->ruleWithConfiguration(GeneralPhpdocAnnotationRemoveFixer::class, [
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
    ]);

    $ecsConfig->skip([
        '*/Source/*',
        '*/Fixture/*',
        '*/Expected/*',

        // buggy - @todo fix on Symplify master
        DocBlockLineLengthFixer::class,

        // double to Double false positive
        PhpdocTypesFixer::class => [__DIR__ . '/rules/Php74/Rector/Double/RealToFloatTypeCastRector.php'],

        // breaking and handled better by Rector PHPUnit code quality set, removed in symplify dev-main
        PhpUnitStrictFixer::class,

        // skip add space on &$variable
        FunctionTypehintSpaceFixer::class => [
            __DIR__ . '/src/PhpParser/Printer/BetterStandardPrinter.php',
            __DIR__ . '/src/DependencyInjection/Loader/Configurator/RectorServiceConfigurator.php',
            __DIR__ . '/rules/Php70/EregToPcreTransformer.php',
        ],

        AssignmentInConditionSniff::class . '.FoundInWhileCondition',
    ]);
};
