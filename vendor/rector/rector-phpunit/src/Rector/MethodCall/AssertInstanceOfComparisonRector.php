<?php

declare (strict_types=1);
namespace Rector\PHPUnit\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Renaming\NodeManipulator\IdentifierManipulator;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\Rector\MethodCall\AssertInstanceOfComparisonRector\AssertInstanceOfComparisonRectorTest
 */
final class AssertInstanceOfComparisonRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var array<string, string>
     */
    private const RENAME_METHODS_MAP = ['assertTrue' => 'assertInstanceOf', 'assertFalse' => 'assertNotInstanceOf'];
    /**
     * @var \Rector\Renaming\NodeManipulator\IdentifierManipulator
     */
    private $identifierManipulator;
    /**
     * @var \Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer
     */
    private $testsNodeAnalyzer;
    public function __construct(\Rector\Renaming\NodeManipulator\IdentifierManipulator $identifierManipulator, \Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer $testsNodeAnalyzer)
    {
        $this->identifierManipulator = $identifierManipulator;
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Turns instanceof comparisons to their method name alternatives in PHPUnit TestCase', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample('$this->assertTrue($foo instanceof Foo, "message");', '$this->assertInstanceOf("Foo", $foo, "message");'), new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample('$this->assertFalse($foo instanceof Foo, "message");', '$this->assertNotInstanceOf("Foo", $foo, "message");')]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\MethodCall::class, \PhpParser\Node\Expr\StaticCall::class];
    }
    /**
     * @param MethodCall|StaticCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $oldMethodNames = \array_keys(self::RENAME_METHODS_MAP);
        if (!$this->testsNodeAnalyzer->isPHPUnitMethodCallNames($node, $oldMethodNames)) {
            return null;
        }
        $firstArgumentValue = $node->args[0]->value;
        if (!$firstArgumentValue instanceof \PhpParser\Node\Expr\Instanceof_) {
            return null;
        }
        $this->identifierManipulator->renameNodeWithMap($node, self::RENAME_METHODS_MAP);
        $this->changeArgumentsOrder($node);
        return $node;
    }
    /**
     * @param MethodCall|StaticCall $node
     */
    private function changeArgumentsOrder(\PhpParser\Node $node) : void
    {
        $oldArguments = $node->args;
        /** @var Instanceof_ $comparison */
        $comparison = $oldArguments[0]->value;
        $argument = $comparison->expr;
        unset($oldArguments[0]);
        $className = $this->getName($comparison->class);
        if ($className === null) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        $node->args = \array_merge([new \PhpParser\Node\Arg($this->nodeFactory->createClassConstReference($className)), new \PhpParser\Node\Arg($argument)], $oldArguments);
    }
}
