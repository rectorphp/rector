<?php declare(strict_types=1);

namespace Rector\Rector\Dynamic;

use PhpParser\BuilderHelpers;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\Rector\Dynamic\ArgumentWrapper\MethodChange;

/**
 * @todo collect cases and prepare tests for them
 *
 * Possible options so far:
 * - argument removed
 * - new default value for argument
 */
final class MethodArgumentChangerRector extends AbstractRector
{
    /**
     * @var MethodChange[]
     */
    private $methodChanges = [];

    /**
     * @var MethodCallAnalyzer
     */
    private $methodCallAnalyzer;

    /**
     * @var MethodChange|null
     */
    private $activeMethodChange;

    /**
     * @param mixed[] $methodChanges
     */
    public function __construct(array $methodChanges, MethodCallAnalyzer $methodCallAnalyzer)
    {
        foreach ($methodChanges as $methodChange) {
            $this->methodChanges[] = MethodChange::createFromMethodChange($methodChange);
        }

        $this->methodCallAnalyzer = $methodCallAnalyzer;
    }

    public function isCandidate(Node $node): bool
    {
        $this->activeMethodChange = $this->resolveMethodChangeForNode($node);
        if ($this->activeMethodChange === null) {
            return false;
        }

        /** @var MethodCall $node */
        if ($this->activeMethodChange->getType() === MethodChange::TYPE_ADDED) {
            $argumentCount = count($node->args);

            if ($argumentCount < $this->activeMethodChange->getPosition() + 1) {
                return true;
            }
        }

        // @todo: other types

        return false;
    }

    /**
     * @param MethodCall $methodCallNode
     */
    public function refactor(Node $methodCallNode): ?Node
    {
        $arguments = $methodCallNode->args;

        if ($this->activeMethodChange->getType() === MethodChange::TYPE_ADDED) {
            if (count($arguments) < $this->activeMethodChange->getPosition() + 1) {
                $defaultValue = $this->activeMethodChange->getDefaultValue();
                $defaultValueNode = BuilderHelpers::normalizeValue($defaultValue);

                $arguments[$this->activeMethodChange->getPosition()] = $defaultValueNode;
            }
        }

        // @todo: other types

        $methodCallNode->args = $arguments;

        return $methodCallNode;
    }

    private function resolveMethodChangeForNode(Node $node): ?MethodChange
    {
        if (! $node instanceof MethodCall) {
            return null;
        }

        foreach ($this->methodChanges as $methodChange) {
            if ($this->matchesMethodChange($node, $methodChange)) {
                return $methodChange;
            }
        }

        return null;
    }

    private function matchesMethodChange(Node $node, MethodChange $methodChange): bool
    {
        return $this->methodCallAnalyzer->isMethodCallTypeAndMethod(
            $node,
            $methodChange->getClass(),
            $methodChange->getMethod()
        );
    }
}
