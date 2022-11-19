<?php

declare (strict_types=1);
namespace Rector\Renaming\Rector\MethodCall;

use PhpParser\BuilderHelpers;
use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\NodeManipulator\ClassManipulator;
use Rector\Core\Rector\AbstractScopeAwareRector;
use Rector\Core\Reflection\ReflectionResolver;
use Rector\Renaming\Collector\MethodCallRenameCollector;
use Rector\Renaming\Contract\MethodCallRenameInterface;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Renaming\ValueObject\MethodCallRenameWithArrayKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202211\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Renaming\Rector\MethodCall\RenameMethodRector\RenameMethodRectorTest
 */
final class RenameMethodRector extends AbstractScopeAwareRector implements ConfigurableRectorInterface
{
    /**
     * @var MethodCallRenameInterface[]
     */
    private $methodCallRenames = [];
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\ClassManipulator
     */
    private $classManipulator;
    /**
     * @readonly
     * @var \Rector\Renaming\Collector\MethodCallRenameCollector
     */
    private $methodCallRenameCollector;
    /**
     * @readonly
     * @var \Rector\Core\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(ClassManipulator $classManipulator, MethodCallRenameCollector $methodCallRenameCollector, ReflectionResolver $reflectionResolver, ReflectionProvider $reflectionProvider)
    {
        $this->classManipulator = $classManipulator;
        $this->methodCallRenameCollector = $methodCallRenameCollector;
        $this->reflectionResolver = $reflectionResolver;
        $this->reflectionProvider = $reflectionProvider;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Turns method names to new ones.', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
$someObject = new SomeExampleClass;
$someObject->oldMethod();
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$someObject = new SomeExampleClass;
$someObject->newMethod();
CODE_SAMPLE
, [new MethodCallRename('SomeExampleClass', 'oldMethod', 'newMethod')])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [MethodCall::class, StaticCall::class, ClassMethod::class];
    }
    /**
     * @param MethodCall|StaticCall|ClassMethod $node
     */
    public function refactorWithScope(Node $node, Scope $scope) : ?Node
    {
        $classReflection = $scope->getClassReflection();
        foreach ($this->methodCallRenames as $methodCallRename) {
            if (!$this->isName($node->name, $methodCallRename->getOldMethod())) {
                continue;
            }
            if ($this->shouldKeepForParentInterface($methodCallRename, $node, $classReflection)) {
                continue;
            }
            if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, $methodCallRename->getObjectType())) {
                continue;
            }
            if ($this->shouldSkipClassMethod($node, $methodCallRename)) {
                continue;
            }
            $node->name = new Identifier($methodCallRename->getNewMethod());
            if ($methodCallRename instanceof MethodCallRenameWithArrayKey && !$node instanceof ClassMethod) {
                return new ArrayDimFetch($node, BuilderHelpers::normalizeValue($methodCallRename->getArrayKey()));
            }
            return $node;
        }
        return null;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        Assert::allIsAOf($configuration, MethodCallRenameInterface::class);
        $this->methodCallRenames = $configuration;
        $this->methodCallRenameCollector->addMethodCallRenames($configuration);
    }
    /**
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Stmt\ClassMethod $node
     */
    private function shouldSkipClassMethod($node, MethodCallRenameInterface $methodCallRename) : bool
    {
        if (!$node instanceof ClassMethod) {
            $classReflection = $this->reflectionResolver->resolveClassReflectionSourceObject($node);
            if (!$classReflection instanceof ClassReflection) {
                return \false;
            }
            $targetClass = $methodCallRename->getClass();
            if (!$this->reflectionProvider->hasClass($targetClass)) {
                return \false;
            }
            $targetClassReflection = $this->reflectionProvider->getClass($targetClass);
            if ($classReflection->getName() === $targetClassReflection->getName()) {
                return \false;
            }
            // different with configured ClassLike source? it is a child, which may has old and new exists
            if (!$classReflection->hasMethod($methodCallRename->getOldMethod())) {
                return \false;
            }
            return $classReflection->hasMethod($methodCallRename->getNewMethod());
        }
        return $this->shouldSkipForAlreadyExistingClassMethod($node, $methodCallRename);
    }
    private function shouldSkipForAlreadyExistingClassMethod(ClassMethod $classMethod, MethodCallRenameInterface $methodCallRename) : bool
    {
        $classLike = $this->betterNodeFinder->findParentType($classMethod, ClassLike::class);
        if (!$classLike instanceof ClassLike) {
            return \false;
        }
        return (bool) $classLike->getMethod($methodCallRename->getNewMethod());
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Expr\MethodCall $node
     */
    private function shouldKeepForParentInterface(MethodCallRenameInterface $methodCallRename, $node, ?ClassReflection $classReflection) : bool
    {
        if (!$node instanceof ClassMethod) {
            return \false;
        }
        if (!$classReflection instanceof ClassReflection) {
            return \false;
        }
        // interface can change current method, as parent contract is still valid
        if (!$classReflection->isInterface()) {
            return \false;
        }
        return $this->classManipulator->hasParentMethodOrInterface($methodCallRename->getObjectType(), $methodCallRename->getOldMethod(), $methodCallRename->getNewMethod());
    }
}
