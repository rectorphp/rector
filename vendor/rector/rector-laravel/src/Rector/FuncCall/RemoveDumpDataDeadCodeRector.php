<?php

declare (strict_types=1);
namespace Rector\Laravel\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Laravel\Tests\Rector\FuncCall\RemoveDumpDataDeadCodeRector\RemoveDumpDataDeadCodeRectorTest
 */
final class RemoveDumpDataDeadCodeRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('It will removes the dump data just like dd or dump functions from the code.`', [new CodeSample(<<<'CODE_SAMPLE'
class MyController
{
    public function store()
    {
        dd('test');
        return true;
    }

    public function update()
    {
        dump('test');
        return true;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class MyController
{
    public function store()
    {
        return true;
    }

    public function update()
    {
        return true;
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->isNames($node->name, ['dd', 'dump'])) {
            return null;
        }
        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
        if (!$parentNode instanceof Expression) {
            return null;
        }
        $this->removeNode($node);
        return null;
    }
}
