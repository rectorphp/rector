<?php

declare (strict_types=1);
namespace Rector\PHPUnit\Rector\MethodCall;

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
 * @see \Rector\PHPUnit\Tests\Rector\MethodCall\DelegateExceptionArgumentsRector\DelegateExceptionArgumentsRectorTest
 */
final class DelegateExceptionArgumentsRector extends AbstractRector
{
    /**
     * @var array<string, string>
     */
    private const OLD_TO_NEW_METHOD = ['setExpectedException' => 'expectExceptionMessage', 'setExpectedExceptionRegExp' => 'expectExceptionMessageRegExp'];
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
    public function __construct(AssertCallFactory $assertCallFactory, TestsNodeAnalyzer $testsNodeAnalyzer)
    {
        $this->assertCallFactory = $assertCallFactory;
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Takes `setExpectedException()` 2nd and next arguments to own methods in PHPUnit.', [new CodeSample('$this->setExpectedException(Exception::class, "Message", "CODE");', <<<'CODE_SAMPLE'
$this->setExpectedException(Exception::class);
$this->expectExceptionMessage('Message');
$this->expectExceptionCode('CODE');
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
        $stmts = (array) $node->stmts;
        if ($stmts === []) {
            return null;
        }
        $hasChanged = \false;
        $oldMethodNames = \array_keys(self::OLD_TO_NEW_METHOD);
        foreach ($stmts as $key => $stmt) {
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
            $extraStmts = [];
            if (isset($call->args[1])) {
                /** @var Identifier $identifierNode */
                $identifierNode = $call->name;
                $oldMethodName = $identifierNode->name;
                $extraCall = $this->assertCallFactory->createCallWithName($call, self::OLD_TO_NEW_METHOD[$oldMethodName]);
                $extraCall->args[] = $call->args[1];
                $extraStmts[] = new Expression($extraCall);
                unset($call->args[1]);
                // add exception code method call
                if (isset($call->args[2])) {
                    $extraCall = $this->assertCallFactory->createCallWithName($call, 'expectExceptionCode');
                    $extraCall->args[] = $call->args[2];
                    $extraStmts[] = new Expression($extraCall);
                    unset($call->args[2]);
                }
            }
            $hasChanged = \true;
            $call->name = new Identifier('expectException');
            $extraStmts = \array_merge($extraStmts, [new Expression($call)]);
            \array_splice($stmts, $key, 1, $extraStmts);
        }
        if ($hasChanged) {
            $node->stmts = $stmts;
            return $node;
        }
        return null;
    }
}
