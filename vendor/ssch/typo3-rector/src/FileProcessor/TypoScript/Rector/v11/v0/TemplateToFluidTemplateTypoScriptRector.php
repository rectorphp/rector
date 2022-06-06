<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Ssch\TYPO3Rector\FileProcessor\TypoScript\Rector\v11\v0;

use RectorPrefix20220606\Helmich\TypoScriptParser\Parser\AST\Operator\ObjectCreation;
use RectorPrefix20220606\Helmich\TypoScriptParser\Parser\AST\Scalar;
use Helmich\TypoScriptParser\Parser\AST\Statement;
use RectorPrefix20220606\Ssch\TYPO3Rector\FileProcessor\TypoScript\Rector\AbstractTypoScriptRector;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/11.0/Breaking-91562-CObjectTEMPLATERemoved.html
 * @see \Ssch\TYPO3Rector\Tests\FileProcessor\TypoScript\TypoScriptProcessorTest
 */
final class TemplateToFluidTemplateTypoScriptRector extends AbstractTypoScriptRector
{
    public function enterNode(Statement $statement) : void
    {
        if (!$statement instanceof ObjectCreation) {
            return;
        }
        if (!$statement->value instanceof Scalar) {
            return;
        }
        if ('TEMPLATE' !== $statement->value->value) {
            return;
        }
        $statement->value->value = 'FLUIDTEMPLATE';
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Convert TEMPLATE to FLUIDTEMPLATE', [new CodeSample(<<<'CODE_SAMPLE'
page.10 = TEMPLATE
CODE_SAMPLE
, <<<'CODE_SAMPLE'
page.10 = FLUIDTEMPLATE
CODE_SAMPLE
)]);
    }
}
