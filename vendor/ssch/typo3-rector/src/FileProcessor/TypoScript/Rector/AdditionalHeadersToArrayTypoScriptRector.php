<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\FileProcessor\TypoScript\Rector;

use RectorPrefix20220527\Helmich\TypoScriptParser\Parser\AST\Operator\Assignment;
use Helmich\TypoScriptParser\Parser\AST\Statement;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/m/typo3/reference-typoscript/master/en-us/Setup/Config/Index.html#additionalheaders
 */
final class AdditionalHeadersToArrayTypoScriptRector extends \Ssch\TYPO3Rector\FileProcessor\TypoScript\Rector\AbstractTypoScriptRector
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
