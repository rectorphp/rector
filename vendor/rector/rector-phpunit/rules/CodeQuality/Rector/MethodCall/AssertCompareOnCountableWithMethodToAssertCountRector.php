<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PHPStan\Type\ObjectType;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\CodeQuality\Rector\MethodCall\AssertCompareOnCountableWithMethodToAssertCountRector\AssertCompareOnCountableWithMethodToAssertCountRectorTest
 */
class AssertCompareOnCountableWithMethodToAssertCountRector extends AbstractRector
{
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
        return new RuleDefinition('Replaces use of assertSame and assertEquals on Countable objects with count method', [new CodeSample(<<<'CODE_SAMPLE'
$this->assertSame(1, $countable->count());
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$this->assertCount(1, $countable);
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
        if (!$this->testsNodeAnalyzer->isPHPUnitMethodCallNames($node, ['assertSame', 'assertEquals'])) {
            return null;
        }
        if (\count($node->getArgs()) < 2) {
            return null;
        }
        $right = $node->getArgs()[1]->value;
        if ($right instanceof MethodCall && $right->name instanceof Identifier && $right->name->toLowerString() === 'count' && $right->getArgs() === []) {
            $type = $this->getType($right->var);
            if ((new ObjectType('Countable'))->isSuperTypeOf($type)->yes()) {
                $args = $node->getArgs();
                $args[1] = new Arg($right->var);
                $node->args = $args;
                $node->name = new Identifier('assertCount');
            }
        }
        return null;
    }
}
