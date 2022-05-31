<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\FileProcessor\TypoScript\Rector;

use RectorPrefix20220531\Helmich\TypoScriptParser\Parser\AST\Operator\ObjectCreation;
use RectorPrefix20220531\Helmich\TypoScriptParser\Parser\AST\Scalar;
use Helmich\TypoScriptParser\Parser\AST\Statement;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/11.0/Breaking-91562-CObjectTEMPLATERemoved.html
 * @see \Ssch\TYPO3Rector\Tests\FileProcessor\TypoScript\TypoScriptProcessorTest
 */
final class TemplateToFluidTemplateTypoScriptRector extends \Ssch\TYPO3Rector\FileProcessor\TypoScript\Rector\AbstractTypoScriptRector
{
    public function enterNode(\Helmich\TypoScriptParser\Parser\AST\Statement $statement) : void
    {
        if (!$statement instanceof \RectorPrefix20220531\Helmich\TypoScriptParser\Parser\AST\Operator\ObjectCreation) {
            return;
        }
        if (!$statement->value instanceof \RectorPrefix20220531\Helmich\TypoScriptParser\Parser\AST\Scalar) {
            return;
        }
        if ('TEMPLATE' !== $statement->value->value) {
            return;
        }
        $statement->value->value = 'FLUIDTEMPLATE';
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Convert TEMPLATE to FLUIDTEMPLATE', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
page.10 = TEMPLATE
CODE_SAMPLE
, <<<'CODE_SAMPLE'
page.10 = FLUIDTEMPLATE
CODE_SAMPLE
)]);
    }
}
