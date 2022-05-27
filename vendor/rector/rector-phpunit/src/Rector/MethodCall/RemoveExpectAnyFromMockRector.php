<?php

declare (strict_types=1);
namespace Rector\PHPUnit\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use Rector\Core\Rector\AbstractRector;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://github.com/symfony/symfony/pull/30813/files#r270879504
 * @see \Rector\PHPUnit\Tests\Rector\MethodCall\RemoveExpectAnyFromMockRector\RemoveExpectAnyFromMockRectorTest
 */
final class RemoveExpectAnyFromMockRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer
     */
    private $testsNodeAnalyzer;
    public function __construct(\Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer $testsNodeAnalyzer)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Remove `expect($this->any())` from mocks as it has no added value', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

class SomeClass extends TestCase
{
    public function test()
    {
        $translator = $this->getMock('SomeClass');
        $translator->expects($this->any())
            ->method('trans')
            ->willReturn('translated max {{ max }}!');
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

class SomeClass extends TestCase
{
    public function test()
    {
        $translator = $this->getMock('SomeClass');
        $translator->method('trans')
            ->willReturn('translated max {{ max }}!');
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
        return [\PhpParser\Node\Expr\MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }
        if (!$this->isName($node->name, 'expects')) {
            return null;
        }
        if (\count($node->args) !== 1) {
            return null;
        }
        $onlyArgument = $node->args[0]->value;
        if (!$this->isMethodCallOnVariableNamed($onlyArgument, 'this', 'any')) {
            return null;
        }
        return $node->var;
    }
    private function isMethodCallOnVariableNamed(\PhpParser\Node\Expr $expr, string $variableName, string $methodName) : bool
    {
        if (!$expr instanceof \PhpParser\Node\Expr\MethodCall) {
            return \false;
        }
        if (!$this->isName($expr->var, $variableName)) {
            return \false;
        }
        return $this->isName($expr->name, $methodName);
    }
}
