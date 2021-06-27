<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\FileProcessor\TypoScript\Visitors;

use RectorPrefix20210627\Helmich\TypoScriptParser\Parser\AST\Builder;
use RectorPrefix20210627\Helmich\TypoScriptParser\Parser\AST\Operator\Assignment;
use RectorPrefix20210627\Helmich\TypoScriptParser\Parser\AST\Statement;
use RectorPrefix20210627\Nette\Utils\Strings;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/m/typo3/reference-typoscript/master/en-us/Setup/Config/Index.html#additionalheaders
 */
final class AdditionalHeadersToArrayVisitor extends \Ssch\TYPO3Rector\FileProcessor\TypoScript\Visitors\AbstractVisitor
{
    /**
     * @var \Helmich\TypoScriptParser\Parser\AST\Builder
     */
    private $builder;
    public function __construct(\RectorPrefix20210627\Helmich\TypoScriptParser\Parser\AST\Builder $builder)
    {
        $this->builder = $builder;
    }
    public function enterNode(\RectorPrefix20210627\Helmich\TypoScriptParser\Parser\AST\Statement $statement) : void
    {
        if (!$statement instanceof \RectorPrefix20210627\Helmich\TypoScriptParser\Parser\AST\Operator\Assignment) {
            return;
        }
        if (!\RectorPrefix20210627\Nette\Utils\Strings::startsWith($statement->object->relativeName, 'additionalHeaders')) {
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
