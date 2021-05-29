<?php

declare(strict_types=1);

namespace Rector\NodeCollector\NodeCollector;

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
        Class_::class,
        Interface_::class,
        ClassConst::class,
        ClassConstFetch::class,
        Trait_::class,
        ClassMethod::class,
        // simply collected
        StaticCall::class,
        MethodCall::class,
        // for array callable - [$this, 'someCall']
        Array_::class,
    ];

    /**
     * @var Class_[]
     */
    private array $classes = [];

    /**
     * @var ClassConst[][]
     */
    private array $constantsByType = [];

    /**
     * @var Interface_[]
     */
    private array $interfaces = [];

    /**
     * @var Trait_[]
     */
    private array $traits = [];

    /**
     * @var StaticCall[]
     */
    private array $staticCalls = [];

    public function __construct(
        private NodeNameResolver $nodeNameResolver,
        private ParentClassScopeResolver $parentClassScopeResolver,
        private ClassAnalyzer $classAnalyzer
    ) {
    }

    /**
     * @return Interface_[]
     */
    public function getInterfaces(): array
    {
        return $this->interfaces;
    }

    /**
     * @return Class_[]
     */
    public function getClasses(): array
    {
        return $this->classes;
    }

    public function findClass(string $name): ?Class_
    {
        return $this->classes[$name] ?? null;
    }

    public function findInterface(string $name): ?Interface_
    {
        return $this->interfaces[$name] ?? null;
    }

    public function findTrait(string $name): ?Trait_
    {
        return $this->traits[$name] ?? null;
    }

    /**
     * Guessing the nearest neighboor.
     * Used e.g. for "XController"
     */
    public function findByShortName(string $shortName): ?Class_
    {
        foreach ($this->classes as $className => $classNode) {
            if (\str_ends_with($className, '\\' . $shortName)) {
                return $classNode;
            }
        }

        return null;
    }

    public function findClassConstant(string $className, string $constantName): ?ClassConst
    {
        if (\str_contains($constantName, '\\')) {
            throw new ShouldNotHappenException(sprintf('Switched arguments in "%s"', __METHOD__));
        }

        return $this->constantsByType[$className][$constantName] ?? null;
    }

    public function isCollectableNode(Node $node): bool
    {
        foreach (self::COLLECTABLE_NODE_TYPES as $collectableNodeType) {
            /** @var class-string<Node> $collectableNodeType */
            if (is_a($node, $collectableNodeType, true)) {
                return true;
            }
        }

        return false;
    }

    public function collect(Node $node): void
    {
        if ($node instanceof Class_) {
            $this->addClass($node);
            return;
        }

        if ($node instanceof Interface_ || $node instanceof Trait_) {
            $this->collectInterfaceOrTrait($node);
            return;
        }

        if ($node instanceof ClassConst) {
            $this->addClassConstant($node);
            return;
        }

        if ($node instanceof StaticCall) {
            $this->staticCalls[] = $node;
            return;
        }
    }

    public function findClassConstByClassConstFetch(ClassConstFetch $classConstFetch): ?ClassConst
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
    public function getStaticCalls(): array
    {
        return $this->staticCalls;
    }

    private function addClass(Class_ $class): void
    {
        if ($this->classAnalyzer->isAnonymousClass($class)) {
            return;
        }

        $className = $class->getAttribute(AttributeKey::CLASS_NAME);
        if ($className === null) {
            throw new ShouldNotHappenException();
        }

        $this->classes[$className] = $class;
    }

    /**
     * @param Interface_|Trait_ $classLike
     */
    private function collectInterfaceOrTrait(ClassLike $classLike): void
    {
        $name = $this->nodeNameResolver->getName($classLike);
        if ($name === null) {
            throw new ShouldNotHappenException();
        }

        if ($classLike instanceof Interface_) {
            $this->interfaces[$name] = $classLike;
        } elseif ($classLike instanceof Trait_) {
            $this->traits[$name] = $classLike;
        }
    }

    private function addClassConstant(ClassConst $classConst): void
    {
        $className = $classConst->getAttribute(AttributeKey::CLASS_NAME);
        if ($className === null) {
            // anonymous class constant
            return;
        }

        $constantName = $this->nodeNameResolver->getName($classConst);

        $this->constantsByType[$className][$constantName] = $classConst;
    }

    private function resolveClassConstant(ClassConstFetch $classConstFetch, string $className): ?string
    {
        if ($className === 'self') {
            return $classConstFetch->getAttribute(AttributeKey::CLASS_NAME);
        }

        if ($className === 'parent') {
            return $this->parentClassScopeResolver->resolveParentClassName($classConstFetch);
        }

        return $className;
    }
}
