<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use Rector\PHPUnit\NodeAnalyzer\IdentifierManipulator;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\CodeQuality\Rector\MethodCall\AssertFalseStrposToContainsRector\AssertFalseStrposToContainsRectorTest
 */
final class AssertFalseStrposToContainsRector extends AbstractRector
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
    private const RENAME_METHODS_MAP = ['assertFalse' => 'assertStringNotContainsString', 'assertNotFalse' => 'assertStringContainsString'];
    public function __construct(IdentifierManipulator $identifierManipulator, TestsNodeAnalyzer $testsNodeAnalyzer)
    {
        $this->identifierManipulator = $identifierManipulator;
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Turns `strpos`/`stripos` comparisons to their method name alternatives in PHPUnit TestCase', [new CodeSample('$this->assertFalse(strpos($anything, "foo"), "message");', '$this->assertNotContains("foo", $anything, "message");')]);
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
        $oldMethodName = \array_keys(self::RENAME_METHODS_MAP);
        if (!$this->testsNodeAnalyzer->isPHPUnitMethodCallNames($node, $oldMethodName)) {
            return null;
        }
        if ($node->isFirstClassCallable()) {
            return null;
        }
        $firstArgumentValue = $node->getArgs()[0]->value;
        if ($firstArgumentValue instanceof StaticCall) {
            return null;
        }
        if ($firstArgumentValue instanceof MethodCall) {
            return null;
        }
        if (!$this->isNames($firstArgumentValue, ['strpos', 'stripos'])) {
            return null;
        }
        $this->identifierManipulator->renameNodeWithMap($node, self::RENAME_METHODS_MAP);
        return $this->changeArgumentsOrder($node);
    }
    /**
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $node
     * @return \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall|null
     */
    private function changeArgumentsOrder($node)
    {
        $oldArguments = $node->getArgs();
        $strposFuncCallNode = $oldArguments[0]->value;
        if (!$strposFuncCallNode instanceof FuncCall) {
            return null;
        }
        $firstArgument = $strposFuncCallNode->getArgs()[1];
        $secondArgument = $strposFuncCallNode->getArgs()[0];
        unset($oldArguments[0]);
        $newArgs = [$firstArgument, $secondArgument];
        $node->args = \array_merge($newArgs, $oldArguments);
        return $node;
    }
}
