<?php declare(strict_types=1);

namespace Rector\PHPUnit\Rector\SpecificMethod;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use Rector\PhpParser\Node\Maintainer\IdentifierMaintainer;
use Rector\Rector\AbstractPHPUnitRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class AssertNotOperatorRector extends AbstractPHPUnitRector
{
    /**
     * @var string[]
     */
    private $renameMethodsMap = [
        'assertTrue' => 'assertFalse',
        'assertFalse' => 'assertTrue',
    ];

    /**
     * @var IdentifierMaintainer
     */
    private $IdentifierMaintainer;

    public function __construct(IdentifierMaintainer $IdentifierMaintainer)
    {
        $this->IdentifierMaintainer = $IdentifierMaintainer;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
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
        if (! $this->isInTestClass($node)) {
            return null;
        }

        if (! $this->isNames($node, array_keys($this->renameMethodsMap))) {
            return null;
        }

        $firstArgumentValue = $node->args[0]->value;
        if (! $firstArgumentValue instanceof BooleanNot) {
            return null;
        }

        $this->IdentifierMaintainer->renameNodeWithMap($node, $this->renameMethodsMap);

        $oldArguments = $node->args;
        /** @var BooleanNot $negation */
        $negation = $oldArguments[0]->value;

        $expression = $negation->expr;

        unset($oldArguments[0]);

        $node->args = array_merge([new Arg($expression)], $oldArguments);

        return $node;
    }
}
