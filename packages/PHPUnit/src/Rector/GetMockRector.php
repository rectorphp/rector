<?php declare(strict_types=1);

namespace Rector\PHPUnit\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use Rector\Builder\IdentifierRenamer;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\Rector\AbstractPHPUnitRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

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

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turns getMock*() methods to createMock()', [
            new CodeSample('$this->getMock("Class");', '$this->createMock("Class");'),
            new CodeSample(
                '$this->getMockWithoutInvokingTheOriginalConstructor("Class");',
                '$this->createMock("Class");'
            ),
        ]);
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

        /** @var MethodCall $methodCallNode */
        $methodCallNode = $node;

        return count($methodCallNode->args) === 1;
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
