<?php declare(strict_types=1);

namespace Rector\Rector\MagicDisclosure;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Cast\String_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Expression;
use Rector\Node\Attribute;
use Rector\Node\MethodCallNodeFactory;
use Rector\NodeAnalyzer\ExpressionAnalyzer;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\NodeAnalyzer\PropertyFetchAnalyzer;
use Rector\Rector\AbstractRector;

/**
 * __get/__set to specific call
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
    public function __construct(array $typeToMethodCalls, MethodCallAnalyzer $methodCallAnalyzer) {
        $this->typeToMethodCalls = $typeToMethodCalls;
        $this->methodCallAnalyzer = $methodCallAnalyzer;
    }

    public function isCandidate(Node $node): bool
    {
        if ($node instanceof String_ && $node->expr) {
            $nodeTypes = $node->expr->getAttribute(Attribute::TYPES);

            foreach ($this->typeToMethodCalls as $type => $transformation) {
                if (in_array($type, $nodeTypes, true)) {
                    $this->activeTransformation = $transformation['toString'];

                    return true;
                }
            }
        }

        if ($node instanceof MethodCall) {
            foreach ($this->typeToMethodCalls as $type => $transformation) {
                if ($this->methodCallAnalyzer->isTypeAndMethod($node, $type, '__toString')) {
                    $this->activeTransformation = $transformation['toString'];

                    return true;
                }
            }
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

        $node->name = $this->activeTransformation;

        return $node;
    }
}
