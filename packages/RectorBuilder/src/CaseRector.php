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

        dump($this->methodName);

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

    public function setMethodCallType(string $methodCallType): void
    {
        $this->methodCallType = $methodCallType;
    }

    public function setMethodName(string $methodName): void
    {
        $this->methodName = $methodName;
    }

    public function setNewMethodName(string $newMethodName): void
    {
        $this->newMethodName = $newMethodName;
    }

    /**
     * @param mixed $value
     */
    public function addNewArgument(int $position, $value): void
    {
        $this->newArguments[$position] = $value;
    }
}
