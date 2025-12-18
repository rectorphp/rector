<?php

declare (strict_types=1);
namespace Rector\Php81\Rector\Array_;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\VariadicPlaceholder;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use Rector\NodeCollector\NodeAnalyzer\ArrayCallableMethodMatcher;
use Rector\NodeCollector\ValueObject\ArrayCallable;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PHPStan\ScopeFetcher;
use Rector\Rector\AbstractRector;
use Rector\Reflection\ReflectionResolver;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Rector\ValueObject\PhpVersion;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see RFC https://wiki.php.net/rfc/first_class_callable_syntax
 * @see \Rector\Tests\Php81\Rector\Array_\ArrayToFirstClassCallableRector\ArrayToFirstClassCallableRectorTest
 */
class ArrayToFirstClassCallableRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private ArrayCallableMethodMatcher $arrayCallableMethodMatcher;
    /**
     * @readonly
     */
    private ReflectionProvider $reflectionProvider;
    /**
     * @readonly
     */
    private ReflectionResolver $reflectionResolver;
    public function __construct(ArrayCallableMethodMatcher $arrayCallableMethodMatcher, ReflectionProvider $reflectionProvider, ReflectionResolver $reflectionResolver)
    {
        $this->arrayCallableMethodMatcher = $arrayCallableMethodMatcher;
        $this->reflectionProvider = $reflectionProvider;
        $this->reflectionResolver = $reflectionResolver;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Upgrade array callable to first class callable', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function run()
    {
        $name = [$this, 'name'];
    }

    public function name()
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run()
    {
        $name = $this->name(...);
    }

    public function name()
    {
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
        return [Array_::class];
    }
    /**
     * @param Array_ $node
     * @return \PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Expr\MethodCall|null
     */
    public function refactor(Node $node)
    {
        if ($node->getAttribute(AttributeKey::IS_INSIDE_SYMFONY_PHP_CLOSURE)) {
            return null;
        }
        $scope = ScopeFetcher::fetch($node);
        $arrayCallable = $this->arrayCallableMethodMatcher->match($node, $scope);
        if (!$arrayCallable instanceof ArrayCallable) {
            return null;
        }
        $callerExpr = $arrayCallable->getCallerExpr();
        if (!$callerExpr instanceof Variable && !$callerExpr instanceof PropertyFetch && !$callerExpr instanceof ClassConstFetch) {
            return null;
        }
        if ($node->getAttribute(AttributeKey::IS_CLASS_CONST_VALUE)) {
            return null;
        }
        if ($node->getAttribute(AttributeKey::IS_DEFAULT_PROPERTY_VALUE)) {
            return null;
        }
        if ($node->getAttribute(AttributeKey::IS_PARAM_DEFAULT)) {
            return null;
        }
        $args = [new VariadicPlaceholder()];
        if ($callerExpr instanceof ClassConstFetch) {
            $type = $this->getType($callerExpr->class);
            if ($type instanceof FullyQualifiedObjectType && $this->isNonStaticOtherObject($type, $arrayCallable, $scope)) {
                return null;
            }
            return new StaticCall($callerExpr->class, $arrayCallable->getMethod(), $args);
        }
        $methodName = $arrayCallable->getMethod();
        $methodCall = new MethodCall($callerExpr, $methodName, $args);
        if ($this->isReferenceToNonPublicMethodOutsideOwningScope($methodCall, $methodName)) {
            return null;
        }
        return $methodCall;
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersion::PHP_81;
    }
    private function isNonStaticOtherObject(FullyQualifiedObjectType $fullyQualifiedObjectType, ArrayCallable $arrayCallable, Scope $scope): bool
    {
        $classReflection = $scope->getClassReflection();
        if ($classReflection instanceof ClassReflection && $classReflection->getName() === $fullyQualifiedObjectType->getClassName()) {
            return \false;
        }
        $arrayClassReflection = $this->reflectionProvider->getClass($arrayCallable->getClass());
        // we're unable to find it
        if (!$arrayClassReflection->hasMethod($arrayCallable->getMethod())) {
            return \false;
        }
        $extendedMethodReflection = $arrayClassReflection->getMethod($arrayCallable->getMethod(), $scope);
        if (!$extendedMethodReflection->isStatic()) {
            return \true;
        }
        return !$extendedMethodReflection->isPublic();
    }
    private function isReferenceToNonPublicMethodOutsideOwningScope(MethodCall $methodCall, string $methodName): bool
    {
        if ($methodCall->var instanceof Variable && $methodCall->var->name === 'this') {
            // If the callable is scoped to `$this` then it can be converted even if it is protected / private
            return \false;
        }
        // If the callable is scoped to another object / variable then it should only be converted if it is public
        // https://github.com/rectorphp/rector/issues/8659
        $classReflection = $this->reflectionResolver->resolveClassReflectionSourceObject($methodCall);
        if ($classReflection instanceof ClassReflection && $classReflection->hasNativeMethod($methodName)) {
            $method = $classReflection->getNativeMethod($methodName);
            if (!$method->isPublic()) {
                return \true;
            }
        }
        return \false;
    }
}
