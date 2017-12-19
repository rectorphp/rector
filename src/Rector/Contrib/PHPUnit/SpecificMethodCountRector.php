<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\PHPUnit;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\LNumber;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\Rector\AbstractRector;

/**
 * Before:
 * - $this->assertSame(5, count($anything));
 * - $this->assertNotSame(5, count($anything));
 * - $this->assertEquals(5, count($anything));
 * - $this->assertNotEquals(5, count($anything));
 * - $this->assertSame(5, sizeof($anything));
 * - $this->assertNotSame(5, sizeof($anything));
 * - $this->assertEquals(5, sizeof($anything));
 * - $this->assertNotEquals(5, sizeof($anything));
 *
 * After:
 * - $this->assertCount(5, $anything);
 * - $this->assertNotCount(5, $anything);
 * - $this->assertCount(5, $anything);
 * - $this->assertNotCount(5, $anything);
 * - $this->assertCount(5, $anything);
 * - $this->assertNotCount(5, $anything);
 * - $this->assertCount(5, $anything);
 * - $this->assertNotCount(5, $anything);
 */
final class SpecificMethodCountRector extends AbstractRector
{
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
            ['assertSame', 'assertEquals', 'assertNotSame', 'assertNotEquals']
        )) {
            return false;
        }

        /** @var MethodCall $methodCallNode */
        $methodCallNode = $node;

        $firstArgumentValue = $methodCallNode->args[0]->value;
        if (! $firstArgumentValue instanceof LNumber) {
            return false;
        }

        $secondArgumentValue = $methodCallNode->args[1]->value;

        if (! $secondArgumentValue instanceof FuncCall) {
            return false;
        }

        $coutableMethod = $secondArgumentValue->name->toString();

        return $coutableMethod === 'count' || $coutableMethod === 'sizeof';
    }

    /**
     * @param MethodCall $methodCallNode
     */
    public function refactor(Node $methodCallNode): ?Node
    {
        $oldMethodName = $methodCallNode->name->toString();

        if ($oldMethodName === 'assertEquals' || $oldMethodName === 'assertSame') {
            /** @var string $trueMethodName */
            $methodCallNode->name = new Identifier('assertCount');
        } elseif ($oldMethodName === 'assertNotEquals' || $oldMethodName === 'assertNotSame') {
            /** @var string $falseMethodName */
            $methodCallNode->name = new Identifier('assertNotCount');
        }

        /** @var FuncCall $secondArgument */
        $secondArgument = $methodCallNode->args[1]->value;

        $methodCallNode->args[1] = $secondArgument->args[0];

        return $methodCallNode;
    }
}
