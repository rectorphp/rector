<?php declare(strict_types=1);

namespace Rector\Rector\Dynamic;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\Rector\Dynamic\ArgumentWrapper\MethodChange;

/**
 * @todo collect cases and prepare tests for them
 *
 * Possible options so far:
 * - new argument
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
        if (! $node instanceof MethodCall) {
            return false;
        }

        foreach ($this->methodChanges as $methodChange) {
            if (! $this->methodCallAnalyzer->isMethodCallTypeAndMethod(
                $node,
                $methodChange->getClass(),
                $methodChange->getMethod()
            )) {
                continue;
            }
        }

        die;

        // is method call...

        return true;
    }

    public function refactor(Node $node): ?Node
    {
        return null;
    }
}
