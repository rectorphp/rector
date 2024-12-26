<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Scalar\InterpolatedString;
use PHPStan\Type\BooleanType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NeverType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use Rector\PHPUnit\NodeAnalyzer\IdentifierManipulator;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\CodeQuality\Rector\MethodCall\AssertEqualsToSameRector\AssertEqualsToSameRectorTest
 */
final class AssertEqualsToSameRector extends AbstractRector
{
    /**
     * @readonly
     */
    private IdentifierManipulator $identifierManipulator;
    /**
     * @readonly
     */
    private TestsNodeAnalyzer $testsNodeAnalyzer;
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
        if ($node->isFirstClassCallable()) {
            return null;
        }
        $args = $node->getArgs();
        if (!isset($args[0], $args[1])) {
            return null;
        }
        $firstArgValue = $args[0]->value;
        if (!$this->isScalarOrEnumValue($firstArgValue)) {
            return null;
        }
        if ($this->shouldSkipConstantArrayType($firstArgValue)) {
            return null;
        }
        if ($this->shouldSkipLooseComparison($args)) {
            return null;
        }
        $hasChanged = $this->identifierManipulator->renameNodeWithMap($node, self::RENAME_METHODS_MAP);
        return $hasChanged ? $node : null;
    }
    /**
     * @param Arg[] $args
     */
    private function shouldSkipLooseComparison(array $args) : bool
    {
        $firstArgType = $this->nodeTypeResolver->getNativeType($args[0]->value);
        $secondArgType = TypeCombinator::removeNull($this->nodeTypeResolver->getNativeType($args[1]->value));
        // loose comparison
        if ($firstArgType instanceof IntegerType && ($secondArgType instanceof FloatType || $secondArgType instanceof StringType)) {
            return \true;
        }
        if ($firstArgType instanceof FloatType && ($secondArgType instanceof IntegerType || $secondArgType instanceof StringType)) {
            return \true;
        }
        if ($firstArgType instanceof StringType && $secondArgType instanceof ObjectType && $this->isObjectType($args[1]->value, new ObjectType('Stringable'))) {
            return \true;
        }
        // compare to mixed type is can be anything
        if ($secondArgType instanceof MixedType) {
            return \true;
        }
        // can happen with magic process
        return $secondArgType instanceof NeverType;
    }
    private function shouldSkipConstantArrayType(Expr $expr) : bool
    {
        $type = $this->nodeTypeResolver->getNativeType($expr);
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
        if ($valueNodeType instanceof ConstantBooleanType) {
            return \false;
        }
        return $valueNodeType instanceof BooleanType;
    }
    private function isScalarOrEnumValue(Expr $expr) : bool
    {
        if ($expr instanceof ClassConstFetch) {
            return \true;
        }
        if ($expr instanceof InterpolatedString) {
            return \true;
        }
        $valueNodeType = $this->nodeTypeResolver->getNativeType($expr);
        return $this->isScalarType($valueNodeType);
    }
}
