<?php declare(strict_types=1);

namespace Rector\RectorBuilder;

use PhpParser\Node;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\Rector\AbstractRector;

final class CaseRector extends AbstractRector
{
    /**
     * @var MethodCallAnalyzer
     */
    private $methodCallAnalyzer;

    /**
     * @var string|null
     */
    private $methodCallType;

    /**
     * @var string|null
     */
    private $methodName;

    /**
     * @var string|null
     */
    private $newMethodName;

    /**
     * @var mixed[]
     */
    private $newArguments = [];

    public function __construct(MethodCallAnalyzer $methodCallAnalyzer)
    {
        $this->methodCallAnalyzer = $methodCallAnalyzer;
    }

    public function isCandidate(Node $node): bool
    {
        if ($this->methodCallType) {
            if (! $this->methodCallAnalyzer->isType($node, $this->methodCallType)) {
                return false;
            }
        }

        if ($this->methodName) {
            if (! $this->methodCallAnalyzer->isMethod($node, $this->methodName)) {
                return false;
            }
        }

        dump('EE');
        die;
        // TODO: Implement isCandidate() method.
    }

    public function refactor(Node $node): ?Node
    {
        dump('refactor');
        die;
    }

    public function matchMethodCallByType(string $methodCallType): self
    {
        $this->methodCallType = $methodCallType;
        return $this;
    }

    public function matchMethodName(string $methodName): self
    {
        $this->methodName = $methodName;
        return $this;
    }

    public function changeMethodNameTo(string $newMethodName): self
    {
        $this->newMethodName = $newMethodName;
        return $this;
    }

    /**
     * @param mixed $value
     */
    public function addArgument(int $position, $value): self
    {
        $this->newArguments[$position] = $value;
        return $this;
    }
}
