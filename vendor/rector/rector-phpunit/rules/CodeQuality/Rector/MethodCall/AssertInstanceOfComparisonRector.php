<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\PHPUnit\NodeAnalyzer\IdentifierManipulator;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\CodeQuality\Rector\MethodCall\AssertInstanceOfComparisonRector\AssertInstanceOfComparisonRectorTest
 */
final class AssertInstanceOfComparisonRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\PHPUnit\NodeAnalyzer\IdentifierManipulator
     */
    private $identifierManipulator;
    /**
     * @readonly
     * @var \Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer
     */
    private $testsNodeAnalyzer;
    /**
     * @var array<string, string>
     */
    private const RENAME_METHODS_MAP = ['assertTrue' => 'assertInstanceOf', 'assertFalse' => 'assertNotInstanceOf'];
    public function __construct(IdentifierManipulator $identifierManipulator, TestsNodeAnalyzer $testsNodeAnalyzer)
    {
        $this->identifierManipulator = $identifierManipulator;
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Turns instanceof comparisons to their method name alternatives in PHPUnit TestCase', [new CodeSample('$this->assertTrue($foo instanceof Foo, "message");', '$this->assertInstanceOf("Foo", $foo, "message");'), new CodeSample('$this->assertFalse($foo instanceof Foo, "message");', '$this->assertNotInstanceOf("Foo", $foo, "message");')]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [MethodCall::class, StaticCall::class];
    }
    /**
     * @param MethodCall|StaticCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        $oldMethodNames = \array_keys(self::RENAME_METHODS_MAP);
        if (!$this->testsNodeAnalyzer->isPHPUnitMethodCallNames($node, $oldMethodNames)) {
            return null;
        }
        if ($node->isFirstClassCallable()) {
            return null;
        }
        $firstArgumentValue = $node->getArgs()[0]->value;
        if (!$firstArgumentValue instanceof Instanceof_) {
            return null;
        }
        $this->identifierManipulator->renameNodeWithMap($node, self::RENAME_METHODS_MAP);
        $this->changeArgumentsOrder($node);
        return $node;
    }
    /**
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $node
     */
    private function changeArgumentsOrder($node) : void
    {
        $oldArguments = $node->getArgs();
        /** @var Instanceof_ $comparison */
        $comparison = $oldArguments[0]->value;
        $argument = $comparison->expr;
        unset($oldArguments[0]);
        $className = $this->getName($comparison->class);
        if ($className === null) {
            throw new ShouldNotHappenException();
        }
        $node->args = \array_merge([new Arg($this->nodeFactory->createClassConstReference($className)), new Arg($argument)], $oldArguments);
    }
}
