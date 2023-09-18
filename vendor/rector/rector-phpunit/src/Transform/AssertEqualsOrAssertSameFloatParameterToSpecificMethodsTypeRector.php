<?php

declare (strict_types=1);
namespace Rector\PHPUnit\Transform;

use InvalidArgumentException;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\DNumber;
use Rector\Core\Rector\AbstractRector;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\PHPUnit\NodeFactory\AssertCallFactory;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\Transform\Rector\AssertEqualsOrAssertSameFloatParameterToSpecificMethodsTypeRector\AssertEqualsOrAssertSameFloatParameterToSpecificMethodsTypeRectorTest
 */
final class AssertEqualsOrAssertSameFloatParameterToSpecificMethodsTypeRector extends AbstractRector
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
$this->assertEqualsWithDelta(10.20, $value, 0.01);
$this->assertEqualsWithDelta(10.20, $value, 0.01);
$this->assertEqualsWithDelta(10.200, $value, 0.001);
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
        $newMethodCall->args[2] = new Arg(new DNumber($this->generateDelta($firstValue)));
        return $newMethodCall;
    }
    private function generateDelta(DNumber $dNumber) : float
    {
        $rawValueNumber = $dNumber->getAttribute('rawValue');
        $countDecimals = \strrpos((string) $rawValueNumber, '.');
        if ($countDecimals === \false) {
            throw new InvalidArgumentException('First argument passed in the function is not a float.');
        }
        $countHowManyDecimals = \strlen((string) $rawValueNumber) - $countDecimals - 2;
        if ($countHowManyDecimals < 1) {
            return 0.1;
        }
        $mountFloat = \number_format(0.0, $countHowManyDecimals, '.', '0') . '1';
        return (float) $mountFloat;
    }
}
