<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\FileProcessor\TypoScript\Rector\v7\v1;

use RectorPrefix20220604\Helmich\TypoScriptParser\Parser\AST\Operator\Assignment;
use Helmich\TypoScriptParser\Parser\AST\Statement;
use Ssch\TYPO3Rector\FileProcessor\TypoScript\Rector\AbstractTypoScriptRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/7.1/Feature-56236-Multiple-HTTP-Headers-In-Frontend.html
 */
final class AdditionalHeadersToArrayTypoScriptRector extends \Ssch\TYPO3Rector\FileProcessor\TypoScript\Rector\AbstractTypoScriptRector
{
    public function enterNode(\Helmich\TypoScriptParser\Parser\AST\Statement $statement) : void
    {
        if (!$statement instanceof \RectorPrefix20220604\Helmich\TypoScriptParser\Parser\AST\Operator\Assignment) {
            return;
        }
        if (\substr_compare($statement->object->relativeName, 'additionalHeaders', -\strlen('additionalHeaders')) !== 0) {
            return;
        }
        $this->hasChanged = \true;
        $statement->object->relativeName = 'additionalHeaders.10.header';
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Use array syntax for additionalHeaders', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
config.additionalHeaders = Content-type:application/json
CODE_SAMPLE
, <<<'CODE_SAMPLE'
config.additionalHeaders.10.header = Content-type:application/json
CODE_SAMPLE
)]);
    }
}
