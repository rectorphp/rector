<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Application;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\Node\Stmt\TraitUse;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\PhpParser\Node\Resolver\NameResolver;

final class ClassLikeNodeCollector
{
    /**
     * @var Class_[]
     */
    private $classes = [];

    /**
     * @var Interface_[]
     */
    private $interfaces = [];

    /**
     * @var Trait_[]
     */
    private $traits = [];

    /**
     * @var NameResolver
     */
    private $nameResolver;

    public function __construct(NameResolver $nameResolver)
    {
        $this->nameResolver = $nameResolver;
    }

    public function addClass(Class_ $classNode): void
    {
        $name = (string) $classNode->getAttribute(Attribute::CLASS_NAME);
        $this->classes[$name] = $classNode;
    }

    public function findClass(string $name): ?Class_
    {
        return $this->classes[$name] ?? null;
    }

    public function addInterface(Interface_ $interfaceNode): void
    {
        $name = (string) $interfaceNode->getAttribute(Attribute::CLASS_NAME);
        $this->interfaces[$name] = $interfaceNode;
    }

    public function addTrait(Trait_ $traitNode): void
    {
        $name = (string) $traitNode->getAttribute(Attribute::CLASS_NAME);
        $this->traits[$name] = $traitNode;
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
     * @return Class_[]|Interface_[]
     */
    public function findClassesAndInterfacesByType(string $type): array
    {
        return array_merge($this->findChildrenOfClass($type), $this->findImplementersOfInterface($type));
    }

    /**
     * @return Class_[]
     */
    public function findChildrenOfClass(string $class): array
    {
        $childrenClasses = [];
        foreach ($this->classes as $classNode) {
            if (! is_a($classNode->getAttribute(Attribute::CLASS_NAME), $class, true)) {
                continue;
            }

            if ($classNode->getAttribute(Attribute::CLASS_NAME) === $class) {
                continue;
            }

            $childrenClasses[] = $classNode;
        }

        return $childrenClasses;
    }

    /**
     * @return Interface_[]
     */
    public function findImplementersOfInterface(string $interface): array
    {
        $implementerInterfaces = [];
        foreach ($this->interfaces as $interfaceNode) {
            if (! is_a($interfaceNode->getAttribute(Attribute::CLASS_NAME), $interface, true)) {
                continue;
            }

            if ($interfaceNode->getAttribute(Attribute::CLASS_NAME) === $interface) {
                continue;
            }

            $implementerInterfaces[] = $interfaceNode;
        }

        return $implementerInterfaces;
    }

    /**
     * @return Trait_[]
     */
    public function findUsedTraitsInClass(ClassLike $classLikeNode): array
    {
        $traits = [];

        foreach ($classLikeNode->stmts as $stmt) {
            if (! $stmt instanceof TraitUse) {
                continue;
            }

            foreach ($stmt->traits as $trait) {
                $traitName = $this->nameResolver->resolve($trait);
                $foundTrait = $this->findTrait($traitName);
                if ($foundTrait) {
                    $traits[] = $foundTrait;
                }
            }
        }

        return $traits;
    }
}
