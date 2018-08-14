<?php declare(strict_types=1);

namespace Rector\PHPUnit\Rector\SpecificMethod;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar;
use Rector\Builder\IdentifierRenamer;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\Rector\AbstractPHPUnitRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class AssertComparisonToSpecificMethodRector extends AbstractPHPUnitRector
{
    /**
     * @var string[][]|false[][]
     */
    private $defaultOldToNewMethods = [
        '===' => ['assertSame', 'assertNotSame'],
        '!==' => ['assertNotSame', 'assertSame'],
        '==' => ['assertEquals', 'assertNotEquals'],
        '!=' => ['assertNotEquals', 'assertEquals'],
        '<>' => ['assertNotEquals', 'assertEquals'],
        '>' => ['assertGreaterThan', 'assertLessThan'],
        '<' => ['assertLessThan', 'assertGreaterThan'],
        '>=' => ['assertGreaterThanOrEqual', 'assertLessThanOrEqual'],
        '<=' => ['assertLessThanOrEqual', 'assertGreaterThanOrEqual'],
    ];

    /**
     * @var MethodCallAnalyzer
     */
    private $methodCallAnalyzer;

    /**
     * @var IdentifierRenamer
     */
    private $identifierRenamer;

    /**
     * @var string|null
     */
    private $activeOpSignal;

    public function __construct(MethodCallAnalyzer $methodCallAnalyzer, IdentifierRenamer $identifierRenamer)
    {
        $this->methodCallAnalyzer = $methodCallAnalyzer;
        $this->identifierRenamer = $identifierRenamer;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Turns comparison operations to their method name alternatives in PHPUnit TestCase',
            [
                new CodeSample(
                    '$this->assertTrue($foo === $bar, "message");',
                    '$this->assertSame($bar, $foo, "message");'
                ),
                new CodeSample(
                    '$this->assertFalse($foo >= $bar, "message");',
                    '$this->assertLessThanOrEqual($bar, $foo, "message");'
                ),
            ]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $methodCallNode
     */
    public function refactor(Node $methodCallNode): ?Node
    {
        if (! $this->isInTestClass($methodCallNode)) {
            return null;
        }
        if (! $this->methodCallAnalyzer->isMethods($methodCallNode, ['assertTrue', 'assertFalse'])) {
            return null;
        }
        $firstArgumentValue = $methodCallNode->args[0]->value;
        if (! $firstArgumentValue instanceof BinaryOp) {
            return null;
        }
        $opCallSignal = $firstArgumentValue->getOperatorSigil();
        if (! isset($this->defaultOldToNewMethods[$opCallSignal])) {
            return null;
        }
        $this->activeOpSignal = $opCallSignal;

        $this->renameMethod($methodCallNode);
        $this->changeOrderArguments($methodCallNode);

        return $methodCallNode;
    }

    public function changeOrderArguments(MethodCall $methodCallNode): void
    {
        $oldArguments = $methodCallNode->args;
        /** @var BinaryOp $expression */
        $expression = $oldArguments[0]->value;

        if ($this->isConstantValue($expression->left)) {
            $firstArgument = new Arg($expression->left);
            $secondArgument = new Arg($expression->right);
        } else {
            $firstArgument = new Arg($expression->right);
            $secondArgument = new Arg($expression->left);
        }

        unset($oldArguments[0]);

        $methodCallNode->args = array_merge([$firstArgument, $secondArgument], $oldArguments);
    }

    private function renameMethod(MethodCall $methodCallNode): void
    {
        [$trueMethodName, $falseMethodName] = $this->defaultOldToNewMethods[$this->activeOpSignal];

        $this->identifierRenamer->renameNodeWithMap($methodCallNode, [
            'assertTrue' => $trueMethodName,
            'assertFalse' => $falseMethodName,
        ]);
    }

    private function isConstantValue(Node $node): bool
    {
        return in_array(get_class($node), [Array_::class, ConstFetch::class], true)
              || is_subclass_of($node, Scalar::class)
              || $node instanceof Variable && stripos($node->name, 'exp') === 0;
    }
}
