<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\PHPUnit;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use Rector\Node\Attribute;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\NodeChanger\MethodNameChanger;
use Rector\Rector\AbstractRector;

/**
 * Before:
 * - $this->getMock('Class')
 * - $this->getMockWithoutInvokingTheOriginalConstructor('Class')
 *
 * After:
 * - $this->createMock('Class')
 */
final class GetMockRector extends AbstractRector
{
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
        if (! $this->isInTestClass($node)) {
            return false;
        }

        if (! $this->methodCallAnalyzer->isMethods(
            $node,
            ['getMock', 'getMockWithoutInvokingTheOriginalConstructor']
        )) {
            return false;
        }

        /** @var MethodCall $node */
        return count($node->args) === 1;
    }

    /**
     * @param MethodCall $methodCallNode
     */
    public function refactor(Node $methodCallNode): ?Node
    {
        return $this->methodNameChanger->renameNode($methodCallNode, 'createMock');
    }

    private function isInTestClass(Node $node): bool
    {
        $className = $node->getAttribute(Attribute::CLASS_NAME);

        return Strings::endsWith((string) $className, 'Test');
    }
}
