<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt\Expression;
use Rector\Contract\PhpParser\Node\StmtsAwareInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeManipulator\StmtsManipulator;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\CodeQuality\Rector\MethodCall\AssertRegExpRector\AssertRegExpRectorTest
 */
final class AssertRegExpRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer
     */
    private $testsNodeAnalyzer;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    /**
     * @readonly
     * @var \Rector\NodeManipulator\StmtsManipulator
     */
    private $stmtsManipulator;
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
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer, ValueResolver $valueResolver, StmtsManipulator $stmtsManipulator)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->valueResolver = $valueResolver;
        $this->stmtsManipulator = $stmtsManipulator;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Turns `preg_match` comparisons to their method name alternatives in PHPUnit TestCase', [new CodeSample('$this->assertSame(1, preg_match("/^Message for ".*"\\.$/", $string), $message);', '$this->assertRegExp("/^Message for ".*"\\.$/", $string, $message);'), new CodeSample('$this->assertEquals(false, preg_match("/^Message for ".*"\\.$/", $string), $message);', '$this->assertNotRegExp("/^Message for ".*"\\.$/", $string, $message);')]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [StmtsAwareInterface::class];
    }
    /**
     * @param StmtsAwareInterface $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node->stmts === null) {
            return null;
        }
        $hasChanged = \false;
        foreach ($node->stmts as $key => $stmt) {
            if (!$stmt instanceof Expression) {
                continue;
            }
            if (!$stmt->expr instanceof MethodCall && !$stmt->expr instanceof StaticCall) {
                continue;
            }
            if (!$this->testsNodeAnalyzer->isPHPUnitMethodCallNames($stmt->expr, [self::ASSERT_SAME, self::ASSERT_EQUALS, self::ASSERT_NOT_SAME, self::ASSERT_NOT_EQUALS])) {
                continue;
            }
            if ($stmt->expr->isFirstClassCallable()) {
                continue;
            }
            /** @var FuncCall|Node $secondArgumentValue */
            $secondArgumentValue = $stmt->expr->getArgs()[1]->value;
            if (!$secondArgumentValue instanceof FuncCall) {
                continue;
            }
            if (!$this->isName($secondArgumentValue, 'preg_match')) {
                continue;
            }
            if ($secondArgumentValue->isFirstClassCallable()) {
                continue;
            }
            $oldMethodName = $this->getName($stmt->expr->name);
            if ($oldMethodName === null) {
                continue;
            }
            $args = $secondArgumentValue->getArgs();
            if (isset($args[2]) && $args[2]->value instanceof Variable && $this->stmtsManipulator->isVariableUsedInNextStmt($node, $key + 1, (string) $this->getName($args[2]->value))) {
                continue;
            }
            $oldFirstArgument = $stmt->expr->getArgs()[0]->value;
            $oldCondition = $this->resolveOldCondition($oldFirstArgument);
            $this->renameMethod($stmt->expr, $oldMethodName, $oldCondition);
            $this->moveFunctionArgumentsUp($stmt->expr);
            $hasChanged = \true;
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    private function resolveOldCondition(Expr $expr) : int
    {
        if ($expr instanceof LNumber) {
            return $expr->value;
        }
        if ($expr instanceof ConstFetch) {
            return $this->valueResolver->isTrue($expr) ? 1 : 0;
        }
        throw new ShouldNotHappenException();
    }
    /**
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $node
     */
    private function renameMethod($node, string $oldMethodName, int $oldCondition) : void
    {
        if (\in_array($oldMethodName, [self::ASSERT_SAME, self::ASSERT_EQUALS], \true) && $oldCondition === 1 || \in_array($oldMethodName, [self::ASSERT_NOT_SAME, self::ASSERT_NOT_EQUALS], \true) && $oldCondition === 0) {
            $node->name = new Identifier('assertRegExp');
        }
        if (\in_array($oldMethodName, [self::ASSERT_SAME, self::ASSERT_EQUALS], \true) && $oldCondition === 0 || \in_array($oldMethodName, [self::ASSERT_NOT_SAME, self::ASSERT_NOT_EQUALS], \true) && $oldCondition === 1) {
            $node->name = new Identifier('assertNotRegExp');
        }
    }
    /**
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $node
     */
    private function moveFunctionArgumentsUp($node) : void
    {
        $oldArguments = $node->getArgs();
        /** @var FuncCall $pregMatchFunction */
        $pregMatchFunction = $oldArguments[1]->value;
        $regex = $pregMatchFunction->getArgs()[0];
        $variable = $pregMatchFunction->getArgs()[1];
        unset($oldArguments[0], $oldArguments[1]);
        $node->args = \array_merge([$regex, $variable], $oldArguments);
    }
}
