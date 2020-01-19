<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Rector\For_;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\For_;
use PHPStan\Analyser\Scope;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\CodeQuality\Tests\Rector\For_\ForRepeatedCountToOwnVariableRector\ForRepeatedCountToOwnVariableRectorTest
 */
final class ForRepeatedCountToOwnVariableRector extends AbstractRector
{
    /**
     * @var string
     */
    private const DEFAULT_VARIABLE_COUNT_NAME = 'itemsCount';

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
        $countedValueName = null;
        $for = $node;

        $this->traverseNodesWithCallable($node->cond, function (Node $node) use (
            &$countInCond,
            &$countedValueName,
            $for
        ) {
            if (! $node instanceof FuncCall) {
                return null;
            }

            if (! $this->isName($node, 'count')) {
                return null;
            }

            $countInCond = $node;
            $valueName = $this->getName($node->args[0]->value);
            $countedValueName = $this->createCountedValueName($valueName, $for->getAttribute(AttributeKey::SCOPE));

            return new Variable($countedValueName);
        });

        if ($countInCond === null || $countedValueName === null) {
            return null;
        }

        $countAssign = new Assign(new Variable($countedValueName), $countInCond);
        $this->addNodeBeforeNode($countAssign, $node);

        return $node;
    }

    protected function createCountedValueName(?string $valueName, ?Scope $scope): string
    {
        $countedValueName = $valueName === null ? self::DEFAULT_VARIABLE_COUNT_NAME : $valueName . 'Count';

        return parent::createCountedValueName($countedValueName, $scope);
    }
}
