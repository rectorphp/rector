<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Application;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Interface_;
use Rector\NodeTypeResolver\Node\Attribute;

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

    public function addClass(string $name, Class_ $classNode): void
    {
        $this->classes[$name] = $classNode;
    }

    public function findClass(string $name): ?Class_
    {
        return $this->classes[$name] ?? null;
    }

    public function addInterface(string $name, Interface_ $interfaceNode): void
    {
        $this->interfaces[$name] = $interfaceNode;
    }

    public function findInterface(string $name): ?Interface_
    {
        return $this->interfaces[$name] ?? null;
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
}
