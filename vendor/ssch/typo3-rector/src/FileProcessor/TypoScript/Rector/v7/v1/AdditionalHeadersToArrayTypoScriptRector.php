<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Ssch\TYPO3Rector\FileProcessor\TypoScript\Rector\v7\v1;

use RectorPrefix20220606\Helmich\TypoScriptParser\Parser\AST\Operator\Assignment;
use Helmich\TypoScriptParser\Parser\AST\Statement;
use RectorPrefix20220606\Ssch\TYPO3Rector\FileProcessor\TypoScript\Rector\AbstractTypoScriptRector;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/7.1/Feature-56236-Multiple-HTTP-Headers-In-Frontend.html
 */
final class AdditionalHeadersToArrayTypoScriptRector extends AbstractTypoScriptRector
{
    public function enterNode(Statement $statement) : void
    {
        if (!$statement instanceof Assignment) {
            return;
        }
        if (\substr_compare($statement->object->relativeName, 'additionalHeaders', -\strlen('additionalHeaders')) !== 0) {
            return;
        }
        $this->hasChanged = \true;
        $statement->object->relativeName = 'additionalHeaders.10.header';
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Use array syntax for additionalHeaders', [new CodeSample(<<<'CODE_SAMPLE'
config.additionalHeaders = Content-type:application/json
CODE_SAMPLE
, <<<'CODE_SAMPLE'
config.additionalHeaders.10.header = Content-type:application/json
CODE_SAMPLE
)]);
    }
}
