<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\Rector\Expression;

use PhpParser\Node;
use PhpParser\Node\Expr\Cast\Array_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\ObjectType;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\CodeQuality\Rector\Expression\AssertArrayCastedObjectToAssertSameRector\AssertArrayCastedObjectToAssertSameRectorTest
 */
final class AssertArrayCastedObjectToAssertSameRector extends AbstractRector
{
    /**
     * @readonly
     */
    private TestsNodeAnalyzer $testsNodeAnalyzer;
    /**
     * @readonly
     */
    private ValueResolver $valueResolver;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer, ValueResolver $valueResolver)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->valueResolver = $valueResolver;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Split object casted to array to assert public properties to standalone assert calls', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestClass
{
    public function test()
    {
        $someObject = new SomeObject();

        $this->assertSame([
            'name' => 'John',
            'surname' => 'Doe',
        ], (array) $someObject));
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestClass
{
    public function test()
    {
        $someObject = new SomeObject();

        $this->assertSame('John', $someObject->name);
        $this->assertSame('Doe', $someObject->surname);
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Expression::class];
    }
    /**
     * @param Expression $node
     * @return Expression[]|null
     */
    public function refactor(Node $node): ?array
    {
        if (!$node->expr instanceof MethodCall) {
            return null;
        }
        $methodCall = $node->expr;
        if ($methodCall->isFirstClassCallable()) {
            return null;
        }
        if (!$this->isName($methodCall->name, 'assertSame')) {
            return null;
        }
        if (!$this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }
        $args = $methodCall->getArgs();
        $firstArg = $args[0];
        if (!$firstArg->value instanceof Node\Expr\Array_) {
            return null;
        }
        // we need assert to non empty arary
        $array = $firstArg->value;
        if ($array->items === []) {
            return null;
        }
        $arrayType = $this->getType($array);
        if (!$arrayType instanceof ConstantArrayType) {
            return null;
        }
        $secondArg = $args[1];
        if (!$secondArg->value instanceof Array_) {
            return null;
        }
        $castArray = $secondArg->value;
        $castedExpr = $castArray->expr;
        $castedExprType = $this->getType($castedExpr);
        if (!$castedExprType instanceof ObjectType) {
            return null;
        }
        $assertedArrayValues = $this->valueResolver->getValue($array);
        if (!$this->hasAllKeysString($assertedArrayValues)) {
            return null;
        }
        $standaloneExpressions = [];
        foreach ($assertedArrayValues as $propertyName => $expectedValue) {
            $args = $this->nodeFactory->createArgs([$expectedValue, new PropertyFetch($castedExpr, new Identifier($propertyName))]);
            $assertSameMethodCall = new MethodCall(new Variable('this'), 'assertSame', $args);
            $standaloneExpressions[] = new Expression($assertSameMethodCall);
        }
        return $standaloneExpressions;
    }
    /**
     * @param array<mixed, mixed> $assertedArrayValues
     */
    private function hasAllKeysString(array $assertedArrayValues): bool
    {
        // all keys must be string
        foreach (array_keys($assertedArrayValues) as $key) {
            if (!is_string($key)) {
                return \false;
            }
        }
        return \true;
    }
}
