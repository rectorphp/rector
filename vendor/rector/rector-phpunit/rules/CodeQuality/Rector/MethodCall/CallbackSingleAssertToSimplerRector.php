<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\CodeQuality\Rector\MethodCall\CallbackSingleAssertToSimplerRector\CallbackSingleAssertToSimplerRectorTest
 */
final class CallbackSingleAssertToSimplerRector extends AbstractRector
{
    /**
     * @readonly
     */
    private TestsNodeAnalyzer $testsNodeAnalyzer;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Replaces use of with, callback and sole assertSame() to simple equalTo() matcher', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeClass extends TestCase
{
    public function test()
    {
        $builder->expects($this->exactly(2))
            ->method('add')
            ->with($this->callback(function ($type): bool {
                $this->assertSame(TextType::class, $type);

                return true;
            }));
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeClass extends TestCase
{
    public function test()
    {
        $builder->expects($this->exactly(2))
            ->method('add')
            ->with($this->equalTo(TextType::class));
    }
}
CODE_SAMPLE
)]);
    }
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?\PhpParser\Node\Expr\MethodCall
    {
        if (!$this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }
        if ($node->isFirstClassCallable()) {
            return null;
        }
        if (!$this->isName($node->name, 'with')) {
            return null;
        }
        $hasChanged = \false;
        foreach ($node->getArgs() as $arg) {
            $expectedExpr = $this->matchCallbackSoleAssertSameExpected($arg->value);
            if (!$expectedExpr instanceof Expr) {
                continue;
            }
            $arg->value = $this->nodeFactory->createMethodCall('this', 'equalTo', [$expectedExpr]);
            $hasChanged = \true;
        }
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
    private function matchCallbackSoleAssertSameExpected(Expr $expr): ?Expr
    {
        if (!$expr instanceof MethodCall || !$this->isName($expr->name, 'callback')) {
            return null;
        }
        $callbackArgs = $expr->getArgs();
        if ($callbackArgs === []) {
            return null;
        }
        $innerClosure = $callbackArgs[0]->value;
        if (!$innerClosure instanceof Closure) {
            return null;
        }
        // skip closures capturing by reference, as the referenced value is likely modified above
        foreach ($innerClosure->uses as $use) {
            if ($use->byRef) {
                return null;
            }
        }
        // exactly assertSame() expression + "return true;"
        $closureStmts = $innerClosure->getStmts();
        if (count($closureStmts) !== 2) {
            return null;
        }
        [$firstStmt, $secondStmt] = $closureStmts;
        if (!$firstStmt instanceof Expression) {
            return null;
        }
        $firstStmtExpr = $firstStmt->expr;
        if (!$firstStmtExpr instanceof MethodCall || !$this->isName($firstStmtExpr->name, 'assertSame')) {
            return null;
        }
        if (!$secondStmt instanceof Return_ || !$this->isTrueReturn($secondStmt)) {
            return null;
        }
        return $firstStmtExpr->getArgs()[0]->value;
    }
    private function isTrueReturn(Return_ $return): bool
    {
        return $return->expr instanceof ConstFetch && $this->isName($return->expr, 'true');
    }
}
