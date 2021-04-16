<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Rector\Assign;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Core\PhpParser\Node\AssignAndBinaryMap;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Tests\CodeQuality\Rector\Assign\CombinedAssignRector\CombinedAssignRectorTest
 */
final class CombinedAssignRector extends AbstractRector
{
    /**
     * @var AssignAndBinaryMap
     */
    private $assignAndBinaryMap;

    /**
     * @var ReflectionProvider
     */
    private $reflectionProvider;

    public function __construct(AssignAndBinaryMap $assignAndBinaryMap, ReflectionProvider $reflectionProvider)
    {
        $this->reflectionProvider = $reflectionProvider;
        $this->assignAndBinaryMap = $assignAndBinaryMap;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Simplify $value = $value + 5; assignments to shorter ones',
            [new CodeSample('$value = $value + 5;', '$value += 5;')]
        );
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Assign::class];
    }

    /**
     * @param Assign $node
     */
    public function refactor(Node $node): ?Node
    {
        try {
            $class = $this->reflectionProvider->getClass("ClassWhichNeedsShouldTriggerAutoloading");
        } catch (\Throwable $e) {
            var_dump($e->getMessage());
        }

        var_dump($class);exit();

        if (! $node->expr instanceof BinaryOp) {
            return null;
        }

        /** @var BinaryOp $binaryNode */
        $binaryNode = $node->expr;

        if (! $this->nodeComparator->areNodesEqual($node->var, $binaryNode->left)) {
            return null;
        }

        $assignClass = $this->assignAndBinaryMap->getAlternative($binaryNode);
        if ($assignClass === null) {
            return null;
        }

        return new $assignClass($node->var, $binaryNode->right);
    }
}
