<?php

declare (strict_types=1);
namespace Rector\PHPUnit\PHPUnit60\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\Contract\PhpParser\Node\StmtsAwareInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\PHPUnit\NodeFactory\AssertCallFactory;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\PHPUnit60\Rector\MethodCall\DelegateExceptionArgumentsRector\DelegateExceptionArgumentsRectorTest
 */
final class DelegateExceptionArgumentsRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\PHPUnit\NodeFactory\AssertCallFactory
     */
    private $assertCallFactory;
    /**
     * @readonly
     * @var \Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer
     */
    private $testsNodeAnalyzer;
    /**
     * @var array<string, string>
     */
    private const OLD_TO_NEW_METHOD = ['setExpectedException' => 'expectExceptionMessage', 'setExpectedExceptionRegExp' => 'expectExceptionMessageRegExp'];
    public function __construct(AssertCallFactory $assertCallFactory, TestsNodeAnalyzer $testsNodeAnalyzer)
    {
        $this->assertCallFactory = $assertCallFactory;
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Takes `setExpectedException()` 2nd and next arguments to own methods in PHPUnit.', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

class SomeTest extends TestCase
{
    public function test()
    {
        $this->setExpectedException(SomeException::class, "Message", "CODE");
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

class SomeTest extends TestCase
{
    public function test()
    {
        $this->setExpectedException(SomeException::class);
        $this->expectExceptionMessage('Message');
        $this->expectExceptionCode('CODE');
    }
}
CODE_SAMPLE
)]);
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
        if ($node->stmts === null || $node->stmts === []) {
            return null;
        }
        if (!$this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }
        $hasChanged = \false;
        $oldMethodNames = \array_keys(self::OLD_TO_NEW_METHOD);
        foreach ($node->stmts as $stmt) {
            if (!$stmt instanceof Expression) {
                continue;
            }
            if (!$stmt->expr instanceof StaticCall && !$stmt->expr instanceof MethodCall) {
                continue;
            }
            $call = $stmt->expr;
            if (!$this->testsNodeAnalyzer->isPHPUnitMethodCallNames($call, $oldMethodNames)) {
                continue;
            }
            if (isset($call->args[1])) {
                $extraCall = $this->createFirstArgExtraMethodCall($call);
                $node->stmts[] = new Expression($extraCall);
                unset($call->args[1]);
            }
            // add exception code method call
            if (isset($call->args[2])) {
                $extraCall = $this->assertCallFactory->createCallWithName($call, 'expectExceptionCode');
                $extraCall->args[] = $call->args[2];
                $node->stmts[] = new Expression($extraCall);
                unset($call->args[2]);
            }
            $hasChanged = \true;
            $call->name = new Identifier('expectException');
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    /**
     * @param \PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Expr\MethodCall $call
     * @return \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall
     */
    private function createFirstArgExtraMethodCall($call)
    {
        /** @var Identifier $identifierNode */
        $identifierNode = $call->name;
        $oldMethodName = $identifierNode->name;
        $extraCall = $this->assertCallFactory->createCallWithName($call, self::OLD_TO_NEW_METHOD[$oldMethodName]);
        $extraCall->args[] = $call->args[1];
        return $extraCall;
    }
}
