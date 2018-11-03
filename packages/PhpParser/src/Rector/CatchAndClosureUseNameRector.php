<?php declare(strict_types=1);

namespace Rector\PhpParser\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class CatchAndClosureUseNameRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turns `$catchNode->var` to its new `name` property in php-parser', [
            new CodeSample('$catchNode->var;', '$catchNode->var->name'),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [PropertyFetch::class];
    }

    /**
     * @param PropertyFetch $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isTypes($node, ['PhpParser\Node\Stmt\Catch_', 'PhpParser\Node\Expr\ClosureUse'])) {
            return null;
        }

        if (! $this->isName($node, 'var')) {
            return null;
        }

        $parentNode = $node->getAttribute(Attribute::PARENT_NODE);
        if ($parentNode instanceof PropertyFetch) {
            return null;
        }

        /** @var Variable $variableNode */
        $variableNode = $node->var;

        $node->var = $this->createPropertyFetch($this->getName($variableNode), 'var');
        $node->name = new Identifier('name');

        return $node;
    }
}
