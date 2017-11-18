<?php declare(strict_types=1);

namespace Rector\Rector\MagicDisclosure;

use PhpParser\Node\Identifier;
use PhpParser\Node;
use PhpParser\Node\Expr\Cast\String_;
use PhpParser\Node\Expr\MethodCall;
use Rector\Node\Attribute;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\Rector\AbstractRector;

/**
 * __toString specific call
 *
 * Example - from:
 * - $result = (string) $someValue;
 * - $result = $someValue->__toString();
 *
 * To
 * - $result = $someValue->someMethod();
 * - $result = $someValue->someMethod();
 */
final class ToStringToMethodCallRector extends AbstractRector
{
    /**
     * @var string[][]
     */
    private $typeToMethodCalls = [];

    /**
     * @var string
     */
    private $activeTransformation;

    /**
     * @var MethodCallAnalyzer
     */
    private $methodCallAnalyzer;

    /**
     * Type to method call()
     *
     * @param string[][] $typeToMethodCalls
     */
    public function __construct(array $typeToMethodCalls, MethodCallAnalyzer $methodCallAnalyzer)
    {
        $this->typeToMethodCalls = $typeToMethodCalls;
        $this->methodCallAnalyzer = $methodCallAnalyzer;
    }

    public function isCandidate(Node $node): bool
    {
        if ($node instanceof String_ && $node->expr) {
            return $this->processStringCandidate($node);
        }

        if ($node instanceof MethodCall) {
            return $this->processMethodCallCandidate($node);
        }

        return false;
    }

    /**
     * @param String_|MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof String_) {
            return new MethodCall($node->expr, $this->activeTransformation);
        }

        $node->name = new Identifier($this->activeTransformation);

        return $node;
    }

    private function processStringCandidate(String_ $stringNode): bool
    {
        $nodeTypes = $stringNode->expr->getAttribute(Attribute::TYPES);

        foreach ($this->typeToMethodCalls as $type => $transformation) {
            if (in_array($type, $nodeTypes, true)) {
                $this->activeTransformation = $transformation['toString'];

                return true;
            }
        }

        return false;
    }

    private function processMethodCallCandidate(MethodCall $methodCallNode): bool
    {
        foreach ($this->typeToMethodCalls as $type => $transformation) {
            if ($this->methodCallAnalyzer->isTypeAndMethod($methodCallNode, $type, '__toString')) {
                $this->activeTransformation = $transformation['toString'];

                return true;
            }
        }

        return false;
    }
}
