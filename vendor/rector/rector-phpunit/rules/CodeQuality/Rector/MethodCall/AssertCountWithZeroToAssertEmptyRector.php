<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PHPStan\Type\UnionType;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\CodeQuality\Rector\MethodCall\AssertCountWithZeroToAssertEmptyRector\AssertCountWithZeroToAssertEmptyRectorTest
 */
final class AssertCountWithZeroToAssertEmptyRector extends AbstractRector
{
    /**
     * @readonly
     */
    private TestsNodeAnalyzer $testsNodeAnalyzer;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change $this->assertCount(0, ...) to $this->assertEmpty(...)', [new CodeSample(<<<'CODE_SAMPLE'
$this->assertCount(0, $countable);
$this->assertNotCount(0, $countable);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$this->assertEmpty($countable);
$this->assertNotEmpty($countable);
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<MethodCall|StaticCall>>
     */
    public function getNodeTypes() : array
    {
        return [MethodCall::class, StaticCall::class];
    }
    /**
     * @param MethodCall|StaticCall $node
     * @return \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall|null
     */
    public function refactor(Node $node)
    {
        if (!$this->testsNodeAnalyzer->isPHPUnitMethodCallNames($node, ['assertCount', 'assertNotCount'])) {
            return null;
        }
        if ($node->isFirstClassCallable()) {
            return null;
        }
        if (\count($node->getArgs()) < 2) {
            return null;
        }
        $type = $this->getType($node->getArgs()[0]->value);
        if ($type instanceof UnionType) {
            return null;
        }
        $value = $type->getConstantScalarValues()[0] ?? null;
        if ($value === 0) {
            $args = $node->getArgs();
            if ($this->isName($node->name, 'assertNotCount')) {
                $node->name = new Name('assertNotEmpty');
            } else {
                $node->name = new Name('assertEmpty');
            }
            \array_shift($args);
            $node->args = $args;
            return $node;
        }
        return null;
    }
}
