<?php

declare(strict_types=1);

namespace Rector\PHPUnit\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use Rector\Core\Rector\AbstractRector;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Renaming\NodeManipulator\IdentifierManipulator;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\PHPUnit\Tests\Rector\MethodCall\AssertNotOperatorRector\AssertNotOperatorRectorTest
 */
final class AssertNotOperatorRector extends AbstractRector
{
    /**
     * @var array<string, string>
     */
    private const RENAME_METHODS_MAP = [
        'assertTrue' => 'assertFalse',
        'assertFalse' => 'assertTrue',
    ];

    /**
     * @var IdentifierManipulator
     */
    private $identifierManipulator;

    /**
     * @var TestsNodeAnalyzer
     */
    private $testsNodeAnalyzer;

    public function __construct(IdentifierManipulator $identifierManipulator, TestsNodeAnalyzer $testsNodeAnalyzer)
    {
        $this->identifierManipulator = $identifierManipulator;
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Turns not-operator comparisons to their method name alternatives in PHPUnit TestCase',
            [
                new CodeSample('$this->assertTrue(!$foo, "message");', '$this->assertFalse($foo, "message");'),
                new CodeSample('$this->assertFalse(!$foo, "message");', '$this->assertTrue($foo, "message");'),
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
        $oldMethodNames = array_keys(self::RENAME_METHODS_MAP);
        if (! $this->testsNodeAnalyzer->isPHPUnitMethodNames($node, $oldMethodNames)) {
            return null;
        }

        $firstArgumentValue = $node->args[0]->value;
        if (! $firstArgumentValue instanceof BooleanNot) {
            return null;
        }

        $this->identifierManipulator->renameNodeWithMap($node, self::RENAME_METHODS_MAP);

        $oldArguments = $node->args;
        /** @var BooleanNot $negation */
        $negation = $oldArguments[0]->value;

        $expression = $negation->expr;

        unset($oldArguments[0]);

        $node->args = array_merge([new Arg($expression)], $oldArguments);

        return $node;
    }
}
