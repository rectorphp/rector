<?php declare(strict_types=1);

namespace Rector\PHPUnit\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use Rector\Builder\IdentifierRenamer;
use Rector\Rector\AbstractPHPUnitRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class GetMockRector extends AbstractPHPUnitRector
{
    /**
     * @var IdentifierRenamer
     */
    private $identifierRenamer;

    public function __construct(IdentifierRenamer $identifierRenamer)
    {
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

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isInTestClass($node)) {
            return null;
        }

        if (! $this->isNames($node, ['getMock', 'getMockWithoutInvokingTheOriginalConstructor'])) {
            return null;
        }

        if (count($node->args) !== 1) {
            return null;
        }

        $this->identifierRenamer->renameNode($node, 'createMock');

        return $node;
    }
}
