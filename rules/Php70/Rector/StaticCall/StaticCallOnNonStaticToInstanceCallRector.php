<?php

declare (strict_types=1);
namespace Rector\Php70\Rector\StaticCall;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\Reflection\ReflectionResolver;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\NodeCollector\ScopeResolver\ParentClassScopeResolver;
use Rector\NodeCollector\StaticAnalyzer;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use ReflectionMethod;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://thephp.cc/news/2017/07/dont-call-instance-methods-statically https://3v4l.org/tQ32f https://3v4l.org/jB9jn
 *
 * @see \Rector\Tests\Php70\Rector\StaticCall\StaticCallOnNonStaticToInstanceCallRector\StaticCallOnNonStaticToInstanceCallRectorTest
 */
final class StaticCallOnNonStaticToInstanceCallRector extends \Rector\Core\Rector\AbstractRector implements \Rector\VersionBonding\Contract\MinPhpVersionInterface
{
    /**
     * @var \Rector\NodeCollector\StaticAnalyzer
     */
    private $staticAnalyzer;
    /**
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @var \Rector\Core\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    /**
     * @var \Rector\NodeCollector\ScopeResolver\ParentClassScopeResolver
     */
    private $parentClassScopeResolver;
    public function __construct(\Rector\NodeCollector\StaticAnalyzer $staticAnalyzer, \PHPStan\Reflection\ReflectionProvider $reflectionProvider, \Rector\Core\Reflection\ReflectionResolver $reflectionResolver, \Rector\NodeCollector\ScopeResolver\ParentClassScopeResolver $parentClassScopeResolver)
    {
        $this->staticAnalyzer = $staticAnalyzer;
        $this->reflectionProvider = $reflectionProvider;
        $this->reflectionResolver = $reflectionResolver;
        $this->parentClassScopeResolver = $parentClassScopeResolver;
    }
    public function provideMinPhpVersion() : int
    {
        return \Rector\Core\ValueObject\PhpVersionFeature::INSTANCE_CALL;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Changes static call to instance call, where not useful', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
        return [\PhpParser\Node\Expr\StaticCall::class];
    }
    /**
     * @param StaticCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($node->name instanceof \PhpParser\Node\Expr) {
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
        if ($this->shouldSkip($methodName, $className, $node)) {
            return null;
        }
        if ($this->isInstantiable($className)) {
            $new = new \PhpParser\Node\Expr\New_($node->class);
            return new \PhpParser\Node\Expr\MethodCall($new, $node->name, $node->args);
        }
        return null;
    }
    private function resolveStaticCallClassName(\PhpParser\Node\Expr\StaticCall $staticCall) : ?string
    {
        if ($staticCall->class instanceof \PhpParser\Node\Expr\PropertyFetch) {
            $objectType = $this->getType($staticCall->class);
            if ($objectType instanceof \PHPStan\Type\ObjectType) {
                return $objectType->getClassName();
            }
        }
        return $this->getName($staticCall->class);
    }
    private function shouldSkip(string $methodName, string $className, \PhpParser\Node\Expr\StaticCall $staticCall) : bool
    {
        $isStaticMethod = $this->staticAnalyzer->isStaticMethod($methodName, $className);
        if ($isStaticMethod) {
            return \true;
        }
        if ($this->isNames($staticCall->class, ['self', 'parent', 'static', 'class'])) {
            return \true;
        }
        $parentClassName = $this->parentClassScopeResolver->resolveParentClassName($staticCall);
        return $className === $parentClassName;
    }
    private function isInstantiable(string $className) : bool
    {
        if (!$this->reflectionProvider->hasClass($className)) {
            return \false;
        }
        $methodReflection = $this->reflectionResolver->resolveMethodReflection($className, '__callStatic', null);
        if ($methodReflection instanceof \PHPStan\Reflection\MethodReflection) {
            return \false;
        }
        $classReflection = $this->reflectionProvider->getClass($className);
        $reflectionClass = $classReflection->getNativeReflection();
        $reflectionMethod = $reflectionClass->getConstructor();
        if (!$reflectionMethod instanceof \ReflectionMethod) {
            return \true;
        }
        if (!$reflectionMethod->isPublic()) {
            return \false;
        }
        // required parameters in constructor, nothing we can do
        return !(bool) $reflectionMethod->getNumberOfRequiredParameters();
    }
}
