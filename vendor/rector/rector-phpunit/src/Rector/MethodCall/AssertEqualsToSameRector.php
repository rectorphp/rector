<?php

declare (strict_types=1);
namespace Rector\PHPUnit\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Scalar\Encapsed;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use Rector\Core\Rector\AbstractRector;
use Rector\PHPUnit\NodeAnalyzer\IdentifierManipulator;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\Rector\MethodCall\AssertEqualsToSameRector\AssertEqualsToSameRectorTest
 */
final class AssertEqualsToSameRector extends AbstractRector
{
    /**
     * @var array<string, string>
     */
    private const RENAME_METHODS_MAP = ['assertEquals' => 'assertSame', 'assertNotEquals' => 'assertNotSame'];
    /**
     * We exclude
     * - bool because this is taken care of AssertEqualsParameterToSpecificMethodsTypeRector
     * - null because this is taken care of AssertEqualsParameterToSpecificMethodsTypeRector
     *
     * @var array<class-string<Type>>
     */
    private const SCALAR_TYPES = [FloatType::class, IntegerType::class, StringType::class, ConstantArrayType::class];
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
    public function __construct(IdentifierManipulator $identifierManipulator, TestsNodeAnalyzer $testsNodeAnalyzer)
    {
        $this->identifierManipulator = $identifierManipulator;
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Turns `assertEquals()` into stricter `assertSame()` for scalar values in PHPUnit TestCase', [new CodeSample('$this->assertEquals(2, $result);', '$this->assertSame(2, $result);')]);
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
        if (!$this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }
        $methodNames = \array_keys(self::RENAME_METHODS_MAP);
        if (!$this->isNames($node->name, $methodNames)) {
            return null;
        }
        $args = $node->getArgs();
        if (!isset($args[0])) {
            return null;
        }
        $firstArgValue = $args[0]->value;
        if (!$this->isScalarValue($firstArgValue)) {
            return null;
        }
        if ($this->shouldSkipConstantArrayType($firstArgValue)) {
            return null;
        }
        $hasChanged = $this->identifierManipulator->renameNodeWithMap($node, self::RENAME_METHODS_MAP);
        return $hasChanged ? $node : null;
    }
    private function shouldSkipConstantArrayType(Expr $expr) : bool
    {
        $type = $this->getType($expr);
        if (!$type instanceof ConstantArrayType) {
            return \false;
        }
        return $this->hasNonScalarType($type);
    }
    private function hasNonScalarType(ConstantArrayType $constantArrayType) : bool
    {
        $valueTypes = $constantArrayType->getValueTypes();
        // empty array
        if ($valueTypes === []) {
            return \false;
        }
        foreach ($valueTypes as $valueType) {
            if ($valueType instanceof ConstantArrayType && $this->hasNonScalarType($valueType)) {
                return \true;
            }
            // non-scalar type can be an object or mixed, which should be skipped
            if (!$this->isScalarType($valueType)) {
                return \true;
            }
        }
        return \false;
    }
    private function isScalarType(Type $valueNodeType) : bool
    {
        foreach (self::SCALAR_TYPES as $scalarType) {
            if ($valueNodeType instanceof $scalarType) {
                return \true;
            }
        }
        return \false;
    }
    private function isScalarValue(Expr $expr) : bool
    {
        if ($expr instanceof Encapsed) {
            return \true;
        }
        $valueNodeType = $this->nodeTypeResolver->getType($expr);
        return $this->isScalarType($valueNodeType);
    }
}
