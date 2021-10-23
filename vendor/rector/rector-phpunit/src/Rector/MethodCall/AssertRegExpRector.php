<?php

declare (strict_types=1);
namespace Rector\PHPUnit\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\LNumber;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\Rector\MethodCall\AssertRegExpRector\AssertRegExpRectorTest
 */
final class AssertRegExpRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var string
     */
    private const ASSERT_SAME = 'assertSame';
    /**
     * @var string
     */
    private const ASSERT_EQUALS = 'assertEquals';
    /**
     * @var string
     */
    private const ASSERT_NOT_SAME = 'assertNotSame';
    /**
     * @var string
     */
    private const ASSERT_NOT_EQUALS = 'assertNotEquals';
    /**
     * @var \Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer
     */
    private $testsNodeAnalyzer;
    public function __construct(\Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer $testsNodeAnalyzer)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Turns `preg_match` comparisons to their method name alternatives in PHPUnit TestCase', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample('$this->assertSame(1, preg_match("/^Message for ".*"\\.$/", $string), $message);', '$this->assertRegExp("/^Message for ".*"\\.$/", $string, $message);'), new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample('$this->assertEquals(false, preg_match("/^Message for ".*"\\.$/", $string), $message);', '$this->assertNotRegExp("/^Message for ".*"\\.$/", $string, $message);')]);
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
        if (!$this->testsNodeAnalyzer->isPHPUnitMethodCallNames($node, [self::ASSERT_SAME, self::ASSERT_EQUALS, self::ASSERT_NOT_SAME, self::ASSERT_NOT_EQUALS])) {
            return null;
        }
        /** @var FuncCall|Node $secondArgumentValue */
        $secondArgumentValue = $node->args[1]->value;
        if (!$secondArgumentValue instanceof \PhpParser\Node\Expr\FuncCall) {
            return null;
        }
        if (!$this->isName($secondArgumentValue, 'preg_match')) {
            return null;
        }
        $oldMethodName = $this->getName($node->name);
        if ($oldMethodName === null) {
            return null;
        }
        $oldFirstArgument = $node->args[0]->value;
        $oldCondition = $this->resolveOldCondition($oldFirstArgument);
        $this->renameMethod($node, $oldMethodName, $oldCondition);
        $this->moveFunctionArgumentsUp($node);
        return $node;
    }
    private function resolveOldCondition(\PhpParser\Node\Expr $expr) : int
    {
        if ($expr instanceof \PhpParser\Node\Scalar\LNumber) {
            return $expr->value;
        }
        if ($expr instanceof \PhpParser\Node\Expr\ConstFetch) {
            return $this->valueResolver->isTrue($expr) ? 1 : 0;
        }
        throw new \Rector\Core\Exception\ShouldNotHappenException();
    }
    /**
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $node
     */
    private function renameMethod($node, string $oldMethodName, int $oldCondition) : void
    {
        if (\in_array($oldMethodName, [self::ASSERT_SAME, self::ASSERT_EQUALS], \true) && $oldCondition === 1 || \in_array($oldMethodName, [self::ASSERT_NOT_SAME, self::ASSERT_NOT_EQUALS], \true) && $oldCondition === 0) {
            $node->name = new \PhpParser\Node\Identifier('assertRegExp');
        }
        if (\in_array($oldMethodName, [self::ASSERT_SAME, self::ASSERT_EQUALS], \true) && $oldCondition === 0 || \in_array($oldMethodName, [self::ASSERT_NOT_SAME, self::ASSERT_NOT_EQUALS], \true) && $oldCondition === 1) {
            $node->name = new \PhpParser\Node\Identifier('assertNotRegExp');
        }
    }
    /**
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $node
     */
    private function moveFunctionArgumentsUp($node) : void
    {
        $oldArguments = $node->args;
        /** @var FuncCall $pregMatchFunction */
        $pregMatchFunction = $oldArguments[1]->value;
        $regex = $pregMatchFunction->args[0];
        $variable = $pregMatchFunction->args[1];
        unset($oldArguments[0], $oldArguments[1]);
        $node->args = \array_merge([$regex, $variable], $oldArguments);
    }
}
