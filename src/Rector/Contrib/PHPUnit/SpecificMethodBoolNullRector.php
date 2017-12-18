<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\PHPUnit;

use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\Rector\AbstractRector;

/**
 * Before:
 * - $this->assertSame(null, $anything);
 *
 * After:
 * - $this->assertNull($anything);
 */
final class SpecificMethodBoolNullRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private $constValueToMethodNames = [
        'null' => 'assertNull',
        'true' => 'assertTrue',
        'false' => 'assertFalse',
    ];

    /**
     * @var MethodCallAnalyzer
     */
    private $methodCallAnalyzer;

    /**
     * @var string|null
     */
    private $activeFuncCallName;

    public function __construct(MethodCallAnalyzer $methodCallAnalyzer)
    {
        $this->methodCallAnalyzer = $methodCallAnalyzer;
    }

    public function isCandidate(Node $node): bool
    {
        $this->activeFuncCallName = null;

        if (! $this->methodCallAnalyzer->isTypesAndMethods(
            $node,
            ['PHPUnit\Framework\TestCase', 'PHPUnit_Framework_TestCase'],
            ['assertSame']
        )) {
            return false;
        }

        /** @var MethodCall $methodCallNode */
        $methodCallNode = $node;

        $firstArgumentValue = $methodCallNode->args[0]->value;
        if (! $firstArgumentValue instanceof ConstFetch) {
            return false;
        }

        $costName = $firstArgumentValue->name->toString();

        return isset($this->constValueToMethodNames[$costName]);
    }

    /**
     * @param MethodCall $methodCallNode
     */
    public function refactor(Node $methodCallNode): ?Node
    {
        /** @var ConstFetch $constFetchNode */
        $constFetchNode = $methodCallNode->args[0]->value;
        $constValue = $constFetchNode->name->toString();

        $newMethodName = $this->constValueToMethodNames[$constValue];
        $methodCallNode->name = new Identifier($newMethodName);

        $methodArguments = $methodCallNode->args;
        array_shift($methodArguments);

        $methodCallNode->args = $methodArguments;

        return $methodCallNode;
    }
}
