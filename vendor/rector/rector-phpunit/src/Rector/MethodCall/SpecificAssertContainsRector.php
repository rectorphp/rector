<?php

declare (strict_types=1);
namespace Rector\PHPUnit\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PHPStan\Type\StringType;
use PHPStan\Type\UnionType;
use Rector\Core\Rector\AbstractRector;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://github.com/sebastianbergmann/phpunit/blob/master/ChangeLog-8.0.md
 *
 * @see \Rector\PHPUnit\Tests\Rector\MethodCall\SpecificAssertContainsRector\SpecificAssertContainsRectorTest
 */
final class SpecificAssertContainsRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var array<string, string>
     */
    private const OLD_TO_NEW_METHOD_NAMES = ['assertContains' => 'assertStringContainsString', 'assertNotContains' => 'assertStringNotContainsString'];
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
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change assertContains()/assertNotContains() method to new string and iterable alternatives', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
final class SomeTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->assertContains('foo', 'foo bar');
        $this->assertNotContains('foo', 'foo bar');
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->assertStringContainsString('foo', 'foo bar');
        $this->assertStringNotContainsString('foo', 'foo bar');
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
        return [\PhpParser\Node\Expr\MethodCall::class, \PhpParser\Node\Expr\StaticCall::class];
    }
    /**
     * @param MethodCall|StaticCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->testsNodeAnalyzer->isPHPUnitMethodCallNames($node, ['assertContains', 'assertNotContains'])) {
            return null;
        }
        if (!$this->isPossiblyStringType($node->args[1]->value)) {
            return null;
        }
        $methodName = $this->getName($node->name);
        $newMethodName = self::OLD_TO_NEW_METHOD_NAMES[$methodName];
        $node->name = new \PhpParser\Node\Identifier($newMethodName);
        return $node;
    }
    private function isPossiblyStringType(\PhpParser\Node\Expr $expr) : bool
    {
        $exprType = $this->getType($expr);
        if ($exprType instanceof \PHPStan\Type\UnionType) {
            foreach ($exprType->getTypes() as $unionedType) {
                if ($unionedType instanceof \PHPStan\Type\StringType) {
                    return \true;
                }
            }
        }
        return $exprType instanceof \PHPStan\Type\StringType;
    }
}
