<?php declare(strict_types=1);

namespace Rector\PHPUnit\Rector\SpecificMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use Rector\PhpParser\Node\Manipulator\IdentifierManipulator;
use Rector\Rector\AbstractPHPUnitRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class AssertFalseStrposToContainsRector extends AbstractPHPUnitRector
{
    /**
     * @var string[]
     */
    private $renameMethodsMap = [
        'assertFalse' => 'assertNotContains',
        'assertNotFalse' => 'assertContains',
    ];

    /**
     * @var IdentifierManipulator
     */
    private $identifierManipulator;

    public function __construct(IdentifierManipulator $identifierManipulator)
    {
        $this->identifierManipulator = $identifierManipulator;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Turns `strpos`/`stripos` comparisons to their method name alternatives in PHPUnit TestCase',
            [
                new CodeSample(
                    '$this->assertFalse(strpos($anything, "foo"), "message");',
                    '$this->assertNotContains("foo", $anything, "message");'
                ),
                new CodeSample(
                    '$this->assertNotFalse(stripos($anything, "foo"), "message");',
                    '$this->assertContains("foo", $anything, "message");'
                ),
            ]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class, StaticCall::class];
    }

    /**
     * @param MethodCall|StaticCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isPHPUnitMethodNames($node, array_keys($this->renameMethodsMap))) {
            return null;
        }

        $firstArgumentValue = $node->args[0]->value;
        if (! $this->isNames($firstArgumentValue, ['strpos', 'stripos'])) {
            return null;
        }

        $this->identifierManipulator->renameNodeWithMap($node, $this->renameMethodsMap);
        $this->changeOrderArguments($node);

        return $node;
    }

    /**
     * @param MethodCall|StaticCall $node
     */
    public function changeOrderArguments(Node $node): void
    {
        $oldArguments = $node->args;

        /** @var Identifier $oldArguments */
        $strposFuncCallNode = $oldArguments[0]->value;

        if (! $strposFuncCallNode instanceof FuncCall) {
            return;
        }

        $firstArgument = $strposFuncCallNode->args[1];
        $secondArgument = $strposFuncCallNode->args[0];

        unset($oldArguments[0]);

        $node->args = array_merge([$firstArgument, $secondArgument], $oldArguments);
    }
}
