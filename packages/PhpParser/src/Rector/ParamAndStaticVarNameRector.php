<?php declare(strict_types=1);

namespace Rector\PhpParser\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Identifier;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class ParamAndStaticVarNameRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turns old string `var` to `var->name` sub-variable in Node of PHP-Parser', [
            new CodeSample('$paramNode->name;', '$paramNode->var->name;'),
            new CodeSample('$staticVarNode->name;', '$staticVarNode->var->name;'),
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
        if (! $this->isTypes($node->var, ['PhpParser\Node\Param', 'PhpParser\Node\Stmt\StaticVar'])) {
            return null;
        }

        if (! $this->isName($node, 'name')) {
            return null;
        }

        $node->name = new Identifier('var');

        return new PropertyFetch($node, 'name');
    }
}
