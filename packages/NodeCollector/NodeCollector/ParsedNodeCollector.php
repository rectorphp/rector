<?php

declare (strict_types=1);
namespace Rector\NodeCollector\NodeCollector;

use RectorPrefix20210514\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Trait_;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\NodeAnalyzer\ClassAnalyzer;
use Rector\NodeCollector\ScopeResolver\ParentClassScopeResolver;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
/**
 * All parsed nodes grouped type
 * @see https://phpstan.org/blog/generics-in-php-using-phpdocs
 *
 * @internal To be used only in NodeRepository
 */
final class ParsedNodeCollector
{
    /**
     * @var array<class-string<Node>>
     */
    private const COLLECTABLE_NODE_TYPES = [
        \PhpParser\Node\Stmt\Class_::class,
        \PhpParser\Node\Stmt\Interface_::class,
        \PhpParser\Node\Stmt\ClassConst::class,
        \PhpParser\Node\Expr\ClassConstFetch::class,
        \PhpParser\Node\Stmt\Trait_::class,
        \PhpParser\Node\Stmt\ClassMethod::class,
        // simply collected
        \PhpParser\Node\Expr\StaticCall::class,
        \PhpParser\Node\Expr\MethodCall::class,
        // for array callable - [$this, 'someCall']
        \PhpParser\Node\Expr\Array_::class,
    ];
    /**
     * @var Class_[]
     */
    private $classes = [];
    /**
     * @var ClassConst[][]
     */
    private $constantsByType = [];
    /**
     * @var Interface_[]
     */
    private $interfaces = [];
    /**
     * @var Trait_[]
     */
    private $traits = [];
    /**
     * @var StaticCall[]
     */
    private $staticCalls = [];
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var \Rector\NodeCollector\ScopeResolver\ParentClassScopeResolver
     */
    private $parentClassScopeResolver;
    /**
     * @var \Rector\Core\NodeAnalyzer\ClassAnalyzer
     */
    private $classAnalyzer;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\NodeCollector\ScopeResolver\ParentClassScopeResolver $parentClassScopeResolver, \Rector\Core\NodeAnalyzer\ClassAnalyzer $classAnalyzer)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->parentClassScopeResolver = $parentClassScopeResolver;
        $this->classAnalyzer = $classAnalyzer;
    }
    /**
     * @return Interface_[]
     */
    public function getInterfaces() : array
    {
        return $this->interfaces;
    }
    /**
     * @return Class_[]
     */
    public function getClasses() : array
    {
        return $this->classes;
    }
    public function findClass(string $name) : ?\PhpParser\Node\Stmt\Class_
    {
        return $this->classes[$name] ?? null;
    }
    public function findInterface(string $name) : ?\PhpParser\Node\Stmt\Interface_
    {
        return $this->interfaces[$name] ?? null;
    }
    public function findTrait(string $name) : ?\PhpParser\Node\Stmt\Trait_
    {
        return $this->traits[$name] ?? null;
    }
    /**
     * Guessing the nearest neighboor.
     * Used e.g. for "XController"
     */
    public function findByShortName(string $shortName) : ?\PhpParser\Node\Stmt\Class_
    {
        foreach ($this->classes as $className => $classNode) {
            if (\RectorPrefix20210514\Nette\Utils\Strings::endsWith($className, '\\' . $shortName)) {
                return $classNode;
            }
        }
        return null;
    }
    public function findClassConstant(string $className, string $constantName) : ?\PhpParser\Node\Stmt\ClassConst
    {
        if (\RectorPrefix20210514\Nette\Utils\Strings::contains($constantName, '\\')) {
            throw new \Rector\Core\Exception\ShouldNotHappenException(\sprintf('Switched arguments in "%s"', __METHOD__));
        }
        return $this->constantsByType[$className][$constantName] ?? null;
    }
    public function isCollectableNode(\PhpParser\Node $node) : bool
    {
        foreach (self::COLLECTABLE_NODE_TYPES as $collectableNodeType) {
            /** @var class-string<Node> $collectableNodeType */
            if (\is_a($node, $collectableNodeType, \true)) {
                return \true;
            }
        }
        return \false;
    }
    public function collect(\PhpParser\Node $node) : void
    {
        if ($node instanceof \PhpParser\Node\Stmt\Class_) {
            $this->addClass($node);
            return;
        }
        if ($node instanceof \PhpParser\Node\Stmt\Interface_ || $node instanceof \PhpParser\Node\Stmt\Trait_) {
            $this->collectInterfaceOrTrait($node);
            return;
        }
        if ($node instanceof \PhpParser\Node\Stmt\ClassConst) {
            $this->addClassConstant($node);
            return;
        }
        if ($node instanceof \PhpParser\Node\Expr\StaticCall) {
            $this->staticCalls[] = $node;
            return;
        }
    }
    public function findClassConstByClassConstFetch(\PhpParser\Node\Expr\ClassConstFetch $classConstFetch) : ?\PhpParser\Node\Stmt\ClassConst
    {
        $className = $this->nodeNameResolver->getName($classConstFetch->class);
        if ($className === null) {
            return null;
        }
        $class = $this->resolveClassConstant($classConstFetch, $className);
        if ($class === null) {
            return null;
        }
        /** @var string $constantName */
        $constantName = $this->nodeNameResolver->getName($classConstFetch->name);
        return $this->findClassConstant($class, $constantName);
    }
    /**
     * @return StaticCall[]
     */
    public function getStaticCalls() : array
    {
        return $this->staticCalls;
    }
    private function addClass(\PhpParser\Node\Stmt\Class_ $class) : void
    {
        if ($this->classAnalyzer->isAnonymousClass($class)) {
            return;
        }
        $className = $class->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NAME);
        if ($className === null) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        $this->classes[$className] = $class;
    }
    /**
     * @param Interface_|Trait_ $classLike
     */
    private function collectInterfaceOrTrait(\PhpParser\Node\Stmt\ClassLike $classLike) : void
    {
        $name = $this->nodeNameResolver->getName($classLike);
        if ($name === null) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        if ($classLike instanceof \PhpParser\Node\Stmt\Interface_) {
            $this->interfaces[$name] = $classLike;
        } elseif ($classLike instanceof \PhpParser\Node\Stmt\Trait_) {
            $this->traits[$name] = $classLike;
        }
    }
    private function addClassConstant(\PhpParser\Node\Stmt\ClassConst $classConst) : void
    {
        $className = $classConst->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NAME);
        if ($className === null) {
            // anonymous class constant
            return;
        }
        $constantName = $this->nodeNameResolver->getName($classConst);
        $this->constantsByType[$className][$constantName] = $classConst;
    }
    private function resolveClassConstant(\PhpParser\Node\Expr\ClassConstFetch $classConstFetch, string $className) : ?string
    {
        if ($className === 'self') {
            return $classConstFetch->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NAME);
        }
        if ($className === 'parent') {
            return $this->parentClassScopeResolver->resolveParentClassName($classConstFetch);
        }
        return $className;
    }
}
