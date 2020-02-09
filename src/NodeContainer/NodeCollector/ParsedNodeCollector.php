<?php

declare(strict_types=1);

namespace Rector\Core\NodeContainer\NodeCollector;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Trait_;
use Rector\Core\Exception\NotImplementedException;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Node\Resolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * All parsed nodes grouped type
 */
final class ParsedNodeCollector
{
    /**
     * @var string[]
     */
    private $collectableNodeTypes = [
        Class_::class,
        Interface_::class,
        ClassConst::class,
        ClassConstFetch::class,
        Trait_::class,
        ClassMethod::class,
        // simply collected
        New_::class,
        StaticCall::class,
        MethodCall::class,
        // for array callable - [$this, 'someCall']
        Array_::class,
        // for unused classes
        Param::class,
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
     * @var Node[][]
     */
    private $simpleParsedNodesByType = [];

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var Interface_[]
     */
    private $interfaces = [];

    /**
     * @var Trait_[]
     */
    private $traits = [];

    public function __construct(NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }

    /**
     * @template T of object
     * @param class-string<T> $type
     * @return Node[]|iterable<T>
     */
    public function getNodesByType(string $type): array
    {
        return $this->simpleParsedNodesByType[$type] ?? [];
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
            if (Strings::endsWith($className, '\\' . $shortName)) {
                return $classNode;
            }
        }

        return null;
    }

    /**
     * @return Class_|Interface_|null
     */
    public function findClassOrInterface(string $type): ?ClassLike
    {
        $class = $this->findClass($type);
        if ($class !== null) {
            return $class;
        }

        return $this->findInterface($type);
    }

    public function findClassConstant(string $className, string $constantName): ?ClassConst
    {
        if (Strings::contains($constantName, '\\')) {
            throw new ShouldNotHappenException(sprintf('Switched arguments in "%s"', __METHOD__));
        }

        return $this->constantsByType[$className][$constantName] ?? null;
    }

    public function isCollectableNode(Node $node): bool
    {
        foreach ($this->collectableNodeTypes as $collectableNodeType) {
            if (is_a($node, $collectableNodeType, true)) {
                return true;
            }
        }

        return false;
    }

    public function collect(Node $node): void
    {
        $nodeClass = get_class($node);

        if ($node instanceof Class_) {
            $this->addClass($node);
            return;
        }

        if ($node instanceof Interface_ || $node instanceof Trait_) {
            $name = $this->nodeNameResolver->getName($node);
            if ($name === null) {
                throw new ShouldNotHappenException();
            }

            if ($node instanceof Interface_) {
                $this->interfaces[$name] = $node;
            } elseif ($node instanceof Trait_) {
                $this->traits[$name] = $node;
            }

            return;
        }

        if ($node instanceof ClassConst) {
            $this->addClassConstant($node);
            return;
        }

        // simple collect
        $this->simpleParsedNodesByType[$nodeClass][] = $node;
    }

    /**
     * @return New_[]
     */
    public function findNewNodesByClass(string $className): array
    {
        $newNodesByClass = [];

        /** @var New_[] $news */
        $news = $this->getNodesByType(New_::class);

        foreach ($news as $new) {
            if (! $this->nodeNameResolver->isName($new->class, $className)) {
                continue;
            }

            $newNodesByClass[] = $new;
        }

        return $newNodesByClass;
    }

    public function findClassConstantByClassConstFetch(ClassConstFetch $classConstFetch): ?ClassConst
    {
        $class = $this->nodeNameResolver->getName($classConstFetch->class);

        if ($class === 'self') {
            /** @var string|null $class */
            $class = $classConstFetch->getAttribute(AttributeKey::CLASS_NAME);
        } elseif ($class === 'parent') {
            /** @var string|null $class */
            $class = $classConstFetch->getAttribute(AttributeKey::PARENT_CLASS_NAME);
        }

        if ($class === null) {
            throw new NotImplementedException();
        }

        /** @var string $constantName */
        $constantName = $this->nodeNameResolver->getName($classConstFetch->name);

        return $this->findClassConstant($class, $constantName);
    }

    private function addClass(Class_ $classNode): void
    {
        if ($this->isClassAnonymous($classNode)) {
            return;
        }

        $className = $classNode->getAttribute(AttributeKey::CLASS_NAME);
        if ($className === null) {
            throw new ShouldNotHappenException();
        }

        $this->classes[$className] = $classNode;
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

    private function isClassAnonymous(Class_ $classNode): bool
    {
        if ($classNode->isAnonymous() || $classNode->name === null) {
            return true;
        }

        // PHPStan polution
        return Strings::startsWith($classNode->name->toString(), 'AnonymousClass');
    }
}
