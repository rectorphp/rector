<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\PHPUnit\SpecificMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\LNumber;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\NodeChanger\MethodNameChanger;
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
final class AssertSameCountToSpecificMethodRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private $renameMethodsMap = [
        'assertSame' => 'assertCount',
        'assertNotSame' => 'assertNotCount',
        'assertEquals' => 'assertCount',
        'assertNotEquals' => 'assertNotCount',
    ];

    /**
     * @var MethodCallAnalyzer
     */
    private $methodCallAnalyzer;

    /**
     * @var MethodNameChanger
     */
    private $methodNameChanger;

    public function __construct(MethodCallAnalyzer $methodCallAnalyzer, MethodNameChanger $methodNameChanger)
    {
        $this->methodCallAnalyzer = $methodCallAnalyzer;
        $this->methodNameChanger = $methodNameChanger;
    }

    public function isCandidate(Node $node): bool
    {
        if (! $this->methodCallAnalyzer->isTypesAndMethods(
            $node,
            ['PHPUnit\Framework\TestCase', 'PHPUnit_Framework_TestCase'],
            array_keys($this->renameMethodsMap)
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

        return in_array($coutableMethod, ['count', 'sizeof'], true);
    }

    /**
     * @param MethodCall $methodCallNode
     */
    public function refactor(Node $methodCallNode): ?Node
    {
        $this->methodNameChanger->renameNode($methodCallNode, $this->renameMethodsMap);

        /** @var FuncCall $secondArgument */
        $secondArgument = $methodCallNode->args[1]->value;

        $methodCallNode->args[1] = $secondArgument->args[0];

        return $methodCallNode;
    }
}
