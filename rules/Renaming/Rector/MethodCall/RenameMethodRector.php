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
use RectorPrefix20220531\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Renaming\Rector\MethodCall\RenameMethodRector\RenameMethodRectorTest
 */
final class RenameMethodRector extends \Rector\Core\Rector\AbstractScopeAwareRector implements \Rector\Core\Contract\Rector\ConfigurableRectorInterface
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
    public function __construct(\Rector\Core\NodeManipulator\ClassManipulator $classManipulator, \Rector\Renaming\Collector\MethodCallRenameCollector $methodCallRenameCollector, \Rector\Core\Reflection\ReflectionResolver $reflectionResolver, \PHPStan\Reflection\ReflectionProvider $reflectionProvider)
    {
        $this->classManipulator = $classManipulator;
        $this->methodCallRenameCollector = $methodCallRenameCollector;
        $this->reflectionResolver = $reflectionResolver;
        $this->reflectionProvider = $reflectionProvider;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Turns method names to new ones.', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(<<<'CODE_SAMPLE'
$someObject = new SomeExampleClass;
$someObject->oldMethod();
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$someObject = new SomeExampleClass;
$someObject->newMethod();
CODE_SAMPLE
, [new \Rector\Renaming\ValueObject\MethodCallRename('SomeExampleClass', 'oldMethod', 'newMethod')])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\MethodCall::class, \PhpParser\Node\Expr\StaticCall::class, \PhpParser\Node\Stmt\ClassMethod::class];
    }
    /**
     * @param MethodCall|StaticCall|ClassMethod $node
     */
    public function refactorWithScope(\PhpParser\Node $node, \PHPStan\Analyser\Scope $scope) : ?\PhpParser\Node
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
            $node->name = new \PhpParser\Node\Identifier($methodCallRename->getNewMethod());
            if ($methodCallRename instanceof \Rector\Renaming\ValueObject\MethodCallRenameWithArrayKey && !$node instanceof \PhpParser\Node\Stmt\ClassMethod) {
                return new \PhpParser\Node\Expr\ArrayDimFetch($node, \PhpParser\BuilderHelpers::normalizeValue($methodCallRename->getArrayKey()));
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
        \RectorPrefix20220531\Webmozart\Assert\Assert::allIsAOf($configuration, \Rector\Renaming\Contract\MethodCallRenameInterface::class);
        $this->methodCallRenames = $configuration;
        $this->methodCallRenameCollector->addMethodCallRenames($configuration);
    }
    /**
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Stmt\ClassMethod $node
     */
    private function shouldSkipClassMethod($node, \Rector\Renaming\Contract\MethodCallRenameInterface $methodCallRename) : bool
    {
        if (!$node instanceof \PhpParser\Node\Stmt\ClassMethod) {
            $classReflection = $this->reflectionResolver->resolveClassReflectionSourceObject($node);
            if (!$classReflection instanceof \PHPStan\Reflection\ClassReflection) {
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
    private function shouldSkipForAlreadyExistingClassMethod(\PhpParser\Node\Stmt\ClassMethod $classMethod, \Rector\Renaming\Contract\MethodCallRenameInterface $methodCallRename) : bool
    {
        $classLike = $this->betterNodeFinder->findParentType($classMethod, \PhpParser\Node\Stmt\ClassLike::class);
        if (!$classLike instanceof \PhpParser\Node\Stmt\ClassLike) {
            return \false;
        }
        return (bool) $classLike->getMethod($methodCallRename->getNewMethod());
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Expr\MethodCall $node
     */
    private function shouldKeepForParentInterface(\Rector\Renaming\Contract\MethodCallRenameInterface $methodCallRename, $node, ?\PHPStan\Reflection\ClassReflection $classReflection) : bool
    {
        if (!$node instanceof \PhpParser\Node\Stmt\ClassMethod) {
            return \false;
        }
        if (!$classReflection instanceof \PHPStan\Reflection\ClassReflection) {
            return \false;
        }
        // interface can change current method, as parent contract is still valid
        if (!$classReflection->isInterface()) {
            return \false;
        }
        return $this->classManipulator->hasParentMethodOrInterface($methodCallRename->getObjectType(), $methodCallRename->getOldMethod(), $methodCallRename->getNewMethod());
    }
}
