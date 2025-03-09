<?php

declare (strict_types=1);
namespace Rector\Php70\Rector\StaticCall;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ObjectType;
use Rector\CodingStyle\ValueObject\ObjectMagicMethods;
use Rector\Enum\ObjectReference;
use Rector\NodeCollector\ScopeResolver\ParentClassScopeResolver;
use Rector\NodeCollector\StaticAnalyzer;
use Rector\PHPStan\ScopeFetcher;
use Rector\Rector\AbstractRector;
use Rector\Reflection\ReflectionResolver;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use ReflectionMethod;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php70\Rector\StaticCall\StaticCallOnNonStaticToInstanceCallRector\StaticCallOnNonStaticToInstanceCallRectorTest
 */
final class StaticCallOnNonStaticToInstanceCallRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private StaticAnalyzer $staticAnalyzer;
    /**
     * @readonly
     */
    private ReflectionProvider $reflectionProvider;
    /**
     * @readonly
     */
    private ReflectionResolver $reflectionResolver;
    /**
     * @readonly
     */
    private ParentClassScopeResolver $parentClassScopeResolver;
    public function __construct(StaticAnalyzer $staticAnalyzer, ReflectionProvider $reflectionProvider, ReflectionResolver $reflectionResolver, ParentClassScopeResolver $parentClassScopeResolver)
    {
        $this->staticAnalyzer = $staticAnalyzer;
        $this->reflectionProvider = $reflectionProvider;
        $this->reflectionResolver = $reflectionResolver;
        $this->parentClassScopeResolver = $parentClassScopeResolver;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::INSTANCE_CALL;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Changes static call to instance call, where not useful', [new CodeSample(<<<'CODE_SAMPLE'
class Something
{
    public function doWork()
    {
    }
}

class Another
{
    public function run()
    {
        return Something::doWork();
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class Something
{
    public function doWork()
    {
    }
}

class Another
{
    public function run()
    {
        return (new Something)->doWork();
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
        return [StaticCall::class];
    }
    /**
     * @param StaticCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        $scope = ScopeFetcher::fetch($node);
        if ($node->name instanceof Expr) {
            return null;
        }
        $methodName = $this->getName($node->name);
        $className = $this->resolveStaticCallClassName($node);
        if ($methodName === null) {
            return null;
        }
        if ($className === null) {
            return null;
        }
        if ($this->shouldSkip($methodName, $className, $node, $scope)) {
            return null;
        }
        if ($this->isInstantiable($className, $scope)) {
            $new = new New_($node->class);
            return new MethodCall($new, $node->name, $node->args);
        }
        return null;
    }
    private function resolveStaticCallClassName(StaticCall $staticCall) : ?string
    {
        if ($staticCall->class instanceof PropertyFetch) {
            $objectType = $this->getType($staticCall->class);
            if ($objectType instanceof ObjectType) {
                return $objectType->getClassName();
            }
        }
        return $this->getName($staticCall->class);
    }
    private function shouldSkip(string $methodName, string $className, StaticCall $staticCall, Scope $scope) : bool
    {
        if (\in_array($methodName, ObjectMagicMethods::METHOD_NAMES, \true)) {
            return \true;
        }
        if (!$this->reflectionProvider->hasClass($className)) {
            return \true;
        }
        $classReflection = $this->reflectionProvider->getClass($className);
        if ($classReflection->isAbstract()) {
            return \true;
        }
        // does the method even exist?
        if (!$classReflection->hasMethod($methodName)) {
            return \true;
        }
        $isStaticMethod = $this->staticAnalyzer->isStaticMethod($classReflection, $methodName);
        if ($isStaticMethod) {
            return \true;
        }
        $reflection = $scope->getClassReflection();
        if ($reflection instanceof ClassReflection && $reflection->is($className)) {
            return \true;
        }
        $className = $this->getName($staticCall->class);
        if (\in_array($className, [ObjectReference::PARENT, ObjectReference::SELF, ObjectReference::STATIC], \true)) {
            return \true;
        }
        if ($className === 'class') {
            return \true;
        }
        $parentClassName = $this->parentClassScopeResolver->resolveParentClassName($scope);
        return $className === $parentClassName;
    }
    private function isInstantiable(string $className, Scope $scope) : bool
    {
        if (!$this->reflectionProvider->hasClass($className)) {
            return \false;
        }
        $methodReflection = $this->reflectionResolver->resolveMethodReflection($className, '__callStatic', $scope);
        if ($methodReflection instanceof MethodReflection) {
            return \false;
        }
        $classReflection = $this->reflectionProvider->getClass($className);
        $nativeReflection = $classReflection->getNativeReflection();
        $reflectionMethod = $nativeReflection->getConstructor();
        if (!$reflectionMethod instanceof ReflectionMethod) {
            return \true;
        }
        if (!$reflectionMethod->isPublic()) {
            return \false;
        }
        // required parameters in constructor, nothing we can do
        return !(bool) $reflectionMethod->getNumberOfRequiredParameters();
    }
}
