<?php

declare (strict_types=1);
namespace Rector\Php70\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\Php\PhpMethodReflection;
use Rector\Core\Enum\ObjectReference;
use Rector\Core\Rector\AbstractScopeAwareRector;
use Rector\Core\Reflection\ReflectionResolver;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\NodeCollector\StaticAnalyzer;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://3v4l.org/rkiSC
 * @see \Rector\Tests\Php70\Rector\MethodCall\ThisCallOnStaticMethodToStaticCallRector\ThisCallOnStaticMethodToStaticCallRectorTest
 */
final class ThisCallOnStaticMethodToStaticCallRector extends AbstractScopeAwareRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\NodeCollector\StaticAnalyzer
     */
    private $staticAnalyzer;
    /**
     * @readonly
     * @var \Rector\Core\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    public function __construct(StaticAnalyzer $staticAnalyzer, ReflectionResolver $reflectionResolver)
    {
        $this->staticAnalyzer = $staticAnalyzer;
        $this->reflectionResolver = $reflectionResolver;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::STATIC_CALL_ON_NON_STATIC;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Changes $this->call() to static method to static call', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public static function run()
    {
        $this->eat();
    }

    public static function eat()
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public static function run()
    {
        static::eat();
    }

    public static function eat()
    {
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
    public function refactorWithScope(Node $node, Scope $scope) : ?Node
    {
        if (!$scope->isInClass()) {
            return null;
        }
        $classReflection = $scope->getClassReflection();
        // skip PHPUnit calls, as they accept both self:: and $this-> formats
        if ($classReflection->isSubclassOf('PHPUnit\\Framework\\TestCase')) {
            return null;
        }
        $hasChanged = \false;
        $this->traverseNodesWithCallable($node, function (Node $subNode) use($node, $classReflection, &$hasChanged) : ?StaticCall {
            if (!$subNode instanceof MethodCall) {
                return null;
            }
            if (!$subNode->var instanceof Variable) {
                return null;
            }
            if (!$this->nodeNameResolver->isName($subNode->var, 'this')) {
                return null;
            }
            if (!$subNode->name instanceof Identifier) {
                return null;
            }
            $methodName = $this->getName($subNode->name);
            if ($methodName === null) {
                return null;
            }
            $isStaticMethod = $this->staticAnalyzer->isStaticMethod($classReflection, $methodName, $node);
            if (!$isStaticMethod) {
                return null;
            }
            if ($subNode->isFirstClassCallable()) {
                return null;
            }
            $hasChanged = \true;
            $objectReference = $this->resolveClassSelf($classReflection, $subNode);
            return $this->nodeFactory->createStaticCall($objectReference, $methodName, $subNode->args);
        });
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    /**
     * @return ObjectReference::STATIC|ObjectReference::SELF
     */
    private function resolveClassSelf(ClassReflection $classReflection, MethodCall $methodCall) : string
    {
        if ($classReflection->isFinalByKeyword()) {
            return ObjectReference::SELF;
        }
        $methodReflection = $this->reflectionResolver->resolveMethodReflectionFromMethodCall($methodCall);
        if (!$methodReflection instanceof PhpMethodReflection) {
            return ObjectReference::STATIC;
        }
        if (!$methodReflection->isPrivate()) {
            return ObjectReference::STATIC;
        }
        return ObjectReference::SELF;
    }
}
