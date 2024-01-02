<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\DNumber;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\PHPUnit\NodeFactory\AssertCallFactory;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersion;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see  \Rector\PHPUnit\Tests\CodeQuality\Rector\MethodCall\AssertEqualsOrAssertSameFloatParameterToSpecificMethodsTypeRector\AssertEqualsOrAssertSameFloatParameterToSpecificMethodsTypeRectorTest
 */
final class AssertEqualsOrAssertSameFloatParameterToSpecificMethodsTypeRector extends AbstractRector implements MinPhpVersionInterface
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
    public function __construct(AssertCallFactory $assertCallFactory, TestsNodeAnalyzer $testsNodeAnalyzer)
    {
        $this->assertCallFactory = $assertCallFactory;
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change assertEquals()/assertSame() method using float on expected argument to new specific alternatives.', [new CodeSample(
            // code before
            <<<'CODE_SAMPLE'
$this->assertSame(10.20, $value);
$this->assertEquals(10.20, $value);
$this->assertEquals(10.200, $value);
$this->assertSame(10, $value);
CODE_SAMPLE
,
            <<<'CODE_SAMPLE'
$this->assertEqualsWithDelta(10.20, $value, PHP_FLOAT_EPSILON);
$this->assertEqualsWithDelta(10.20, $value, PHP_FLOAT_EPSILON);
$this->assertEqualsWithDelta(10.200, $value, PHP_FLOAT_EPSILON);
$this->assertSame(10, $value);
CODE_SAMPLE

        )]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->testsNodeAnalyzer->isPHPUnitMethodCallNames($node, ['assertEquals', 'assertSame'])) {
            return null;
        }
        $args = $node->getArgs();
        $firstValue = $args[0]->value;
        if (!$firstValue instanceof DNumber) {
            return null;
        }
        $newMethodCall = $this->assertCallFactory->createCallWithName($node, 'assertEqualsWithDelta');
        $newMethodCall->args[0] = $args[0];
        $newMethodCall->args[1] = $args[1];
        $newMethodCall->args[2] = new Arg(new ConstFetch(new Name('PHP_FLOAT_EPSILON')));
        return $newMethodCall;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersion::PHP_72;
    }
}
