<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\ArrowFunction;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Variable;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodingStyle\Rector\ArrowFunction\StaticArrowFunctionRector\StaticArrowFunctionRectorTest
 */
final class StaticArrowFunctionRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Changes ArrowFunction to be static when possible', [new CodeSample(<<<'CODE_SAMPLE'
fn (): string => 'test';
CODE_SAMPLE
, <<<'CODE_SAMPLE'
static fn (): string => 'test';
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [ArrowFunction::class];
    }
    /**
     * @param ArrowFunction $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        $node->static = \true;
        return $node;
    }
    private function shouldSkip(ArrowFunction $arrowFunction) : bool
    {
        if ($arrowFunction->static) {
            return \true;
        }
        return (bool) $this->betterNodeFinder->findFirst($arrowFunction->expr, static function (Node $subNode) : bool {
            return $subNode instanceof Variable && $subNode->name === 'this';
        });
    }
}
