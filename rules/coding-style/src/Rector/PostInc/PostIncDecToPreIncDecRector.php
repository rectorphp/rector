<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Rector\PostInc;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\PostDec;
use PhpParser\Node\Expr\PostInc;
use PhpParser\Node\Expr\PreDec;
use PhpParser\Node\Expr\PreInc;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\CodingStyle\Tests\Rector\PostInc\PostIncDecToPreIncDecRector\PostIncDecToPreIncDecRectorTest
 */
final class PostIncDecToPreIncDecRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Use ++$value or --$value  instead of `$value++` or `$value--`',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($value = 1)
    {
        $value++; echo $value;
        $value--; echo $value;
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($value = 1)
    {
        ++$value; echo $value;
        --$value; echo $value;
    }
}
CODE_SAMPLE
                ),
            ]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [PostInc::class, PostDec::class];
    }

    /**
     * @param PostInc|PostDec $node
     */
    public function refactor(Node $node): ?Node
    {
        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
        if ($this->isInsideExpression($parentNode)) {
            return $this->processPre($node);
        }

        if ($parentNode instanceof ArrayDimFetch && $this->areNodesEqual($parentNode->dim, $node)) {
            return $this->processPreArray($node, $parentNode);
        }

        return null;
    }

    private function processPreArray(Node $node, ArrayDimFetch $arrayDimFetch)
    {
        $parentOfArrayDimFetch = $arrayDimFetch->getAttribute(AttributeKey::PARENT_NODE);
        if (! $this->isInsideExpression($parentOfArrayDimFetch)) {
            return null;
        }

        $arrayDimFetch->dim = $node->var;
        $this->addNodeAfterNode($this->processPre($node), $arrayDimFetch);

        return $arrayDimFetch->dim;
    }

    private function processPre(Node $node): Node
    {
        if ($node instanceof PostInc) {
            return new PreInc($node->var);
        }

        return new PreDec($node->var);
    }

    private function isInsideExpression(Node $node): bool
    {
        return $node instanceof Expression;
    }
}
