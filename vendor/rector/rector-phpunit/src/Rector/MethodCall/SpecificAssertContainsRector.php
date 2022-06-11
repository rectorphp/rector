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
 * @chna https://github.com/sebastianbergmann/phpunit/blob/master/ChangeLog-8.0.md
 *
 * @see \Rector\PHPUnit\Tests\Rector\MethodCall\SpecificAssertContainsRector\SpecificAssertContainsRectorTest
 */
final class SpecificAssertContainsRector extends AbstractRector
{
    /**
     * @var array<string, string>
     */
    private const OLD_TO_NEW_METHOD_NAMES = ['assertContains' => 'assertStringContainsString', 'assertNotContains' => 'assertStringNotContainsString'];
    /**
     * @readonly
     * @var \Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer
     */
    private $testsNodeAnalyzer;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change assertContains()/assertNotContains() method to new string and iterable alternatives', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [MethodCall::class, StaticCall::class];
    }
    /**
     * @param MethodCall|StaticCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->testsNodeAnalyzer->isPHPUnitMethodCallNames($node, ['assertContains', 'assertNotContains'])) {
            return null;
        }
        if (!$this->isPossiblyStringType($node->args[1]->value)) {
            return null;
        }
        $methodName = $this->getName($node->name);
        $newMethodName = self::OLD_TO_NEW_METHOD_NAMES[$methodName];
        $node->name = new Identifier($newMethodName);
        return $node;
    }
    private function isPossiblyStringType(Expr $expr) : bool
    {
        $exprType = $this->getType($expr);
        if ($exprType instanceof UnionType) {
            foreach ($exprType->getTypes() as $unionedType) {
                if ($unionedType instanceof StringType) {
                    return \true;
                }
            }
        }
        return $exprType instanceof StringType;
    }
}
