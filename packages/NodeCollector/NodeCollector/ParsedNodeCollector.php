<?php

declare(strict_types=1);

namespace Rector\NodeCollector\NodeCollector;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Trait_;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\NodeAnalyzer\ClassAnalyzer;
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
     * @var Class_[]
     */
    private array $classes = [];

    /**
     * @var Interface_[]
     */
    private array $interfaces = [];

    /**
     * @var Trait_[]
     */
    private array $traits = [];

    public function __construct(
        private NodeNameResolver $nodeNameResolver,
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

    public function collect(Node $node): void
    {
        if ($node instanceof Class_) {
            $this->addClass($node);
            return;
        }

        if ($node instanceof Interface_ || $node instanceof Trait_) {
            $this->collectInterfaceOrTrait($node);
        }
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
}
