<?php

declare(strict_types=1);

namespace Rector\Nette\Rector\Identical;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\Variable;
use Rector\Core\Rector\AbstractRector;

abstract class AbstractWithFunctionToNetteUtilsStringsRector extends AbstractRector
{
    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Identical::class, NotIdentical::class];
    }

    /**
     * @param Identical|NotIdentical $node
     */
    public function refactor(Node $node): ?Node
    {
        $contentAndNeedle = null;

        if ($node->left instanceof Variable) {
            $contentAndNeedle = $this->matchContentAndNeedleOfSubstrOfVariableLength($node->right, $node->left);
        }

        if ($node->right instanceof Variable) {
            $contentAndNeedle = $this->matchContentAndNeedleOfSubstrOfVariableLength($node->left, $node->right);
        }

        if ($contentAndNeedle === null) {
            return null;
        }

        [$contentNode, $needleNode] = $contentAndNeedle;

        $staticCall = $this->createStaticCall('Nette\Utils\Strings', $this->getMethodName(), [
            $contentNode,
            $needleNode,
        ]);

        if ($node instanceof NotIdentical) {
            return new BooleanNot($staticCall);
        }

        return $staticCall;
    }

    abstract protected function getMethodName(): string;

    /**
     * @return Expr[]|null
     */
    abstract protected function matchContentAndNeedleOfSubstrOfVariableLength(
        Node $node,
        Variable $variable
    ): ?array;
}
