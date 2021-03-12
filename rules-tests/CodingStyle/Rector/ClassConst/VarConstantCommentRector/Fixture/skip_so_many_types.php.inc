<?php

namespace Rector\Tests\CodingStyle\Rector\ClassConst\VarConstantCommentRector\Fixture;

use PHP_CodeSniffer\Fixer;
use PHP_CodeSniffer\Standards\Generic\Sniffs\PHP\LowerCaseConstantSniff;
use PHP_CodeSniffer\Standards\Generic\Sniffs\PHP\UpperCaseConstantSniff;
use PHP_CodeSniffer\Standards\PSR12\Sniffs\Files\FileHeaderSniff;
use PhpCsFixer\Fixer\Casing\ConstantCaseFixer;
use PhpCsFixer\Fixer\Casing\LowercaseConstantsFixer;
use PhpCsFixer\Fixer\ControlStructure\YodaStyleFixer;
use PhpCsFixer\Fixer\FixerInterface;
use PhpCsFixer\Fixer\LanguageConstruct\DeclareEqualNormalizeFixer;
use PhpCsFixer\Fixer\Phpdoc\NoBlankLinesAfterPhpdocFixer;
use PhpCsFixer\Fixer\PhpTag\BlankLineAfterOpeningTagFixer;

final class SkipSoManyTypes
{
    /**
     * These groups do the opposite of each other, e.g. Yoda vs NoYoda.
     *
     * @var array<array<class-string<FixerInterface|Fixer>|string>>
     */
    private const CONFLICTING_CHECKER_GROUPS = [
        ['SlevomatCodingStandard\Sniffs\ControlStructures\DisallowYodaComparisonSniff', YodaStyleFixer::class],
        [LowerCaseConstantSniff::class, UpperCaseConstantSniff::class],
        [LowercaseConstantsFixer::class, UpperCaseConstantSniff::class],
        [ConstantCaseFixer::class, UpperCaseConstantSniff::class],
        ['SlevomatCodingStandard\Sniffs\TypeHints\DeclareStrictTypesSniff', DeclareEqualNormalizeFixer::class],
        ['SlevomatCodingStandard\Sniffs\TypeHints\DeclareStrictTypesSniff', BlankLineAfterOpeningTagFixer::class],
        [FileHeaderSniff::class, NoBlankLinesAfterPhpdocFixer::class],
    ];
}
