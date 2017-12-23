<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\PHPUnit\SpecificMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\Rector\AbstractRector;

/**
 * Before:
 * - $this->assertSame({keyword}}, $anything);
 * - $this->assertNotSame({keyword}}, $anything);
 *
 * After:
 * - $this->assert{keyword}($anything);
 * - $this->assertNot{keyword}($anything);
 */
final class AssertSameBoolNullToSpecificMethodRector extends AbstractRector
{
    /**
     * @var string[][]|false[][]
     */
    private $constValueToNewMethodNames = [
        'null' => ['assertNull', 'assertNotNull'],
        'true' => ['assertTrue', 'assertNotTrue'],
        'false' => ['assertFalse', 'assertNotFalse'],
    ];

    /**
     * @var MethodCallAnalyzer
     */
    private $methodCallAnalyzer;

    /**
     * @var string
     */
    private $constantName;

    public function __construct(MethodCallAnalyzer $methodCallAnalyzer)
    {
        $this->methodCallAnalyzer = $methodCallAnalyzer;
    }

    public function isCandidate(Node $node): bool
    {
        if (! $this->methodCallAnalyzer->isTypesAndMethods(
            $node,
            ['PHPUnit\Framework\TestCase', 'PHPUnit_Framework_TestCase'],
            ['assertSame', 'assertNotSame']
        )) {
            return false;
        }

        /** @var MethodCall $methodCallNode */
        $methodCallNode = $node;

        $firstArgumentValue = $methodCallNode->args[0]->value;
        if (! $firstArgumentValue instanceof ConstFetch) {
            return false;
        }

        $this->constantName = $firstArgumentValue->name->toString();

        return isset($this->constValueToNewMethodNames[$this->constantName]);
    }

    /**
     * @param MethodCall $methodCallNode
     */
    public function refactor(Node $methodCallNode): ?Node
    {
        $this->renameMethod($methodCallNode);
        $this->moveArguments($methodCallNode);

        return $methodCallNode;
    }

    private function renameMethod(MethodCall $methodCallNode): void
    {
        /** @var Identifier $identifierNode */
        $identifierNode = $methodCallNode->name;
        $oldMethodName = $identifierNode->toString();

        [$sameMethodName, $notSameMethodName] = $this->constValueToNewMethodNames[$this->constantName];

        if ($oldMethodName === 'assertSame' && $sameMethodName) {
            /** @var string $sameMethodName */
            $methodCallNode->name = new Identifier($sameMethodName);
        } elseif ($oldMethodName === 'assertNotSame' && $notSameMethodName) {
            /** @var string $notSameMethodName */
            $methodCallNode->name = new Identifier($notSameMethodName);
        }
    }

    private function moveArguments(MethodCall $methodCallNode): void
    {
        $methodArguments = $methodCallNode->args;
        array_shift($methodArguments);

        $methodCallNode->args = $methodArguments;
    }
}
