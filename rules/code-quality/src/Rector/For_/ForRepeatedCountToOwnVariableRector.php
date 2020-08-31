<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Rector\For_;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\For_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NetteKdyby\Naming\VariableNaming;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\CodeQuality\Tests\Rector\For_\ForRepeatedCountToOwnVariableRector\ForRepeatedCountToOwnVariableRectorTest
 */
final class ForRepeatedCountToOwnVariableRector extends AbstractRector
{
    /**
     * @var VariableNaming
     */
    private $variableNaming;

    public function __construct(VariableNaming $variableNaming)
    {
        $this->variableNaming = $variableNaming;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change count() in for function to own variable', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function run($items)
    {
        for ($i = 5; $i <= count($items); $i++) {
            echo $items[$i];
        }
    }
}
PHP
,
                <<<'PHP'
class SomeClass
{
    public function run($items)
    {
        $itemsCount = count($items);
        for ($i = 5; $i <= $itemsCount; $i++) {
            echo $items[$i];
        }
    }
}
PHP
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [For_::class];
    }

    /**
     * @param For_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $countInCond = null;
        $variableName = null;

        $forScope = $node->getAttribute(AttributeKey::SCOPE);

        $this->traverseNodesWithCallable($node->cond, function (Node $node) use (
            &$countInCond,
            &$variableName,
            $forScope
        ): ?Variable {
            if (! $node instanceof FuncCall) {
                return null;
            }

            if (! $this->isName($node, 'count')) {
                return null;
            }

            $countInCond = $node;

            $variableName = $this->variableNaming->resolveFromFuncCallFirstArgumentWithSuffix(
                $node,
                'Count',
                'itemsCount',
                $forScope
            );

            return new Variable($variableName);
        });

        if ($countInCond === null || $variableName === null) {
            return null;
        }

        $countAssign = new Assign(new Variable($variableName), $countInCond);
        $this->addNodeBeforeNode($countAssign, $node);

        return $node;
    }
}
