<?php declare(strict_types=1);

namespace Rector\DeadCode\Rector\Stmt;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Cast;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Expression;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class RemoveDeadStmtRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Removes dead code statements', [
            new CodeSample(
                <<<'CODE_SAMPLE'
$value = 5;
$value;
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
$value = 5;
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Variable::class, PropertyFetch::class, ArrayDimFetch::class, Cast::class];
    }

    /**
     * @param Variable $node
     */
    public function refactor(Node $node): ?Node
    {
        $parentNode = $node->getAttribute(Attribute::PARENT_NODE);
        if (! $parentNode instanceof Expression) {
            return null;
        }

        if ($node->getComments() !== []) {
            return null;
        }

        $this->removeNode($node);

        return null;
    }
}
