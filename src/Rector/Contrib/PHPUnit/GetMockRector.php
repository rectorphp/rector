<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\PHPUnit;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\NodeChanger\IdentifierRenamer;
use Rector\Rector\AbstractPHPUnitRector;

/**
 * Before:
 * - $this->getMock('Class')
 * - $this->getMockWithoutInvokingTheOriginalConstructor('Class')
 *
 * After:
 * - $this->createMock('Class')
 */
final class GetMockRector extends AbstractPHPUnitRector
{
    /**
     * @var MethodCallAnalyzer
     */
    private $methodCallAnalyzer;

    /**
     * @var IdentifierRenamer
     */
    private $identifierRenamer;

    public function __construct(MethodCallAnalyzer $methodCallAnalyzer, IdentifierRenamer $identifierRenamer)
    {
        $this->methodCallAnalyzer = $methodCallAnalyzer;
        $this->identifierRenamer = $identifierRenamer;
    }

    /**
     * @param MethodCall $node
     */
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

        return count($node->args) === 1;
    }

    /**
     * @param MethodCall $methodCallNode
     */
    public function refactor(Node $methodCallNode): ?Node
    {
        $this->identifierRenamer->renameNode($methodCallNode, 'createMock');

        return $methodCallNode;
    }
}
