<?php

declare (strict_types=1);
namespace Rector\Php70\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\InterpolatedString;
use PhpParser\Node\Stmt\Class_;
use PhpParser\NodeVisitor;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\Php\PhpMethodReflection;
use Rector\Enum\ObjectReference;
use Rector\NodeCollector\StaticAnalyzer;
use Rector\PHPStan\ScopeFetcher;
use Rector\Rector\AbstractRector;
use Rector\Reflection\ReflectionResolver;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php70\Rector\MethodCall\ThisCallOnStaticMethodToStaticCallRector\ThisCallOnStaticMethodToStaticCallRectorTest
 */
final class ThisCallOnStaticMethodToStaticCallRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private StaticAnalyzer $staticAnalyzer;
    /**
     * @readonly
     */
    private ReflectionResolver $reflectionResolver;
    private bool $hasChanged = \false;
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
    public function refactor(Node $node) : ?Node
    {
        $scope = ScopeFetcher::fetch($node);
        if (!$scope->isInClass()) {
            return null;
        }
        $classReflection = $scope->getClassReflection();
        // skip PHPUnit calls, as they accept both self:: and $this-> formats
        if ($classReflection->is('PHPUnit\\Framework\\TestCase')) {
            return null;
        }
        $this->hasChanged = \false;
        $this->processThisToStatic($node, $classReflection);
        if ($this->hasChanged) {
            return $node;
        }
        return null;
    }
    private function processThisToStatic(Class_ $class, ClassReflection $classReflection) : void
    {
        $this->traverseNodesWithCallable($class, function (Node $subNode) use($class, $classReflection) {
            if ($subNode instanceof InterpolatedString) {
                return NodeVisitor::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }
            if (!$subNode instanceof MethodCall) {
                return null;
            }
            if (!$subNode->var instanceof Variable) {
                return null;
            }
            if (!$this->isName($subNode->var, 'this')) {
                return null;
            }
            if (!$subNode->name instanceof Identifier) {
                return null;
            }
            $methodName = $this->getName($subNode->name);
            if ($methodName === null) {
                return null;
            }
            $isStaticMethod = $this->staticAnalyzer->isStaticMethod($classReflection, $methodName, $class);
            if (!$isStaticMethod) {
                return null;
            }
            if ($subNode->isFirstClassCallable()) {
                return null;
            }
            $this->hasChanged = \true;
            $objectReference = $this->resolveClassSelf($classReflection, $subNode);
            return $this->nodeFactory->createStaticCall($objectReference, $methodName, $subNode->args);
        });
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
