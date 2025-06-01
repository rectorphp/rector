<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NeverType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use RectorPrefix202506\PHPUnit\Framework\MockObject\Builder\InvocationMocker;
use Rector\Enum\ClassName;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\PHPUnit\CodeQuality\NodeAnalyser\SetUpAssignedMockTypesResolver;
use Rector\PHPUnit\CodeQuality\Reflection\MethodParametersAndReturnTypesResolver;
use Rector\PHPUnit\CodeQuality\ValueObject\ParamTypesAndReturnType;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\CodeQuality\Rector\Class_\TypeWillReturnCallableArrowFunctionRector\TypeWillReturnCallableArrowFunctionRectorTest
 */
final class TypeWillReturnCallableArrowFunctionRector extends AbstractRector
{
    /**
     * @readonly
     */
    private TestsNodeAnalyzer $testsNodeAnalyzer;
    /**
     * @readonly
     */
    private StaticTypeMapper $staticTypeMapper;
    /**
     * @readonly
     */
    private SetUpAssignedMockTypesResolver $setUpAssignedMockTypesResolver;
    /**
     * @readonly
     */
    private MethodParametersAndReturnTypesResolver $methodParametersAndReturnTypesResolver;
    /**
     * @var string
     */
    private const WILL_RETURN_CALLBACK = 'willReturnCallback';
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer, StaticTypeMapper $staticTypeMapper, SetUpAssignedMockTypesResolver $setUpAssignedMockTypesResolver, MethodParametersAndReturnTypesResolver $methodParametersAndReturnTypesResolver)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->setUpAssignedMockTypesResolver = $setUpAssignedMockTypesResolver;
        $this->methodParametersAndReturnTypesResolver = $methodParametersAndReturnTypesResolver;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Decorate callbacks and arrow functions in willReturnCallback() with known param/return types based on reflection method', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    public function testSomething()
    {
        $this->createMock(SomeClass::class)
            ->method('someMethod')
            ->willReturnCallback(function ($arg) {
                return $arg;
            });
    }
}

final class SomeClass
{
    public function someMethod(string $arg): string
    {
        return $arg . ' !';
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    public function testSomething()
    {
        $this->createMock(SomeClass::class)
            ->method('someMethod')
            ->willReturnCallback(
                function (string $arg): string {
                    return $arg;
                }
            );
    }
}

final class SomeClass
{
    public function someMethod(string $arg): string
    {
        return $arg . ' !';
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
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Class_
    {
        if (!$this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }
        $hasChanged = \false;
        $propertyNameToMockedTypes = $this->setUpAssignedMockTypesResolver->resolveFromClass($node);
        $this->traverseNodesWithCallable($node->getMethods(), function (Node $node) use(&$hasChanged, $propertyNameToMockedTypes) {
            if (!$node instanceof MethodCall || $node->isFirstClassCallable()) {
                return null;
            }
            if (!$this->isName($node->name, self::WILL_RETURN_CALLBACK)) {
                return null;
            }
            $innerArg = $node->getArgs()[0]->value;
            if (!$innerArg instanceof ArrowFunction && !$innerArg instanceof Closure) {
                return null;
            }
            if (!$node->var instanceof MethodCall) {
                return null;
            }
            $parentMethodCall = $node->var;
            if (!$this->isName($parentMethodCall->name, 'method')) {
                return null;
            }
            $methodNameExpr = $parentMethodCall->getArgs()[0]->value;
            if (!$methodNameExpr instanceof String_) {
                return null;
            }
            $methodName = $methodNameExpr->value;
            $callerType = $this->getType($parentMethodCall->var);
            if ($callerType instanceof ObjectType && $callerType->getClassName() === InvocationMocker::class) {
                $parentMethodCall = $parentMethodCall->var;
                if ($parentMethodCall instanceof MethodCall) {
                    $callerType = $this->getType($parentMethodCall->var);
                }
            }
            $callerType = $this->fallbackMockedObjectInSetUp($callerType, $parentMethodCall, $propertyNameToMockedTypes);
            // we need mocks
            if (!$callerType instanceof IntersectionType) {
                return null;
            }
            $hasChanged = \false;
            $parameterTypesAndReturnType = $this->methodParametersAndReturnTypesResolver->resolveFromReflection($callerType, $methodName);
            if (!$parameterTypesAndReturnType instanceof ParamTypesAndReturnType) {
                return null;
            }
            foreach ($innerArg->params as $key => $param) {
                // avoid typing variadic parameters
                if ($param->variadic) {
                    continue;
                }
                // already filled, lets skip it
                if ($param->type instanceof Node) {
                    continue;
                }
                $nativeParameterType = $parameterTypesAndReturnType->getParamTypes()[$key] ?? null;
                // we need specific non-mixed type
                if ($nativeParameterType === null) {
                    continue;
                }
                if ($nativeParameterType instanceof MixedType) {
                    continue;
                }
                $parameterTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($nativeParameterType, TypeKind::PARAM);
                if (!$parameterTypeNode instanceof Node) {
                    continue;
                }
                $param->type = $parameterTypeNode;
                $hasChanged = \true;
            }
            if (!$innerArg->returnType instanceof Node) {
                $returnTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($parameterTypesAndReturnType->getReturnType(), TypeKind::RETURN);
                if ($returnTypeNode instanceof Node) {
                    $innerArg->returnType = $returnTypeNode;
                    $hasChanged = \true;
                }
            }
            $hasChanged = \true;
        });
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
    /**
     * @param array<string, string> $propertyNameToMockedTypes
     * @return mixed
     */
    private function fallbackMockedObjectInSetUp(Type $callerType, Expr $expr, array $propertyNameToMockedTypes)
    {
        if (!$callerType instanceof ObjectType && !$callerType instanceof NeverType) {
            return $callerType;
        }
        if (!$expr instanceof MethodCall) {
            return $callerType;
        }
        if ($callerType instanceof ObjectType && $callerType->getClassName() !== ClassName::MOCK_OBJECT) {
            return $callerType;
        }
        // type is missing, because of "final" keyword on mocked class
        // resolve from constructor instead
        if (!$expr->var instanceof PropertyFetch && !$expr->var instanceof Variable) {
            return $callerType;
        }
        if ($expr->var instanceof Variable) {
            $propertyOrVariableName = $this->getName($expr->var);
        } else {
            $propertyOrVariableName = $this->getName($expr->var->name);
        }
        if (isset($propertyNameToMockedTypes[$propertyOrVariableName])) {
            $mockedType = $propertyNameToMockedTypes[$propertyOrVariableName];
            return new IntersectionType([$callerType, new ObjectType($mockedType)]);
        }
        return $callerType;
    }
}
