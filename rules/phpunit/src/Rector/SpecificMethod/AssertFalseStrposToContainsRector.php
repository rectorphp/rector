<?php

declare(strict_types=1);

namespace Rector\PHPUnit\Rector\SpecificMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use Rector\Core\PhpParser\Node\Manipulator\IdentifierManipulator;
use Rector\Core\Rector\AbstractPHPUnitRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\PHPUnit\Tests\Rector\SpecificMethod\AssertFalseStrposToContainsRector\AssertFalseStrposToContainsRectorTest
 */
final class AssertFalseStrposToContainsRector extends AbstractPHPUnitRector
{
    /**
     * @var string[]
     */
    private const RENAME_METHODS_MAP = [
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
        if (! $this->isPHPUnitMethodNames($node, array_keys(self::RENAME_METHODS_MAP))) {
            return null;
        }

        $firstArgumentValue = $node->args[0]->value;
        if ($firstArgumentValue instanceof StaticCall) {
            return null;
        }
        if (! $this->isNames($firstArgumentValue, ['strpos', 'stripos'])) {
            return null;
        }

        $this->identifierManipulator->renameNodeWithMap($node, self::RENAME_METHODS_MAP);
        $this->changeArgumentsOrder($node);

        return $node;
    }

    /**
     * @param MethodCall|StaticCall $node
     */
    private function changeArgumentsOrder(Node $node): void
    {
        $oldArguments = $node->args;

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
