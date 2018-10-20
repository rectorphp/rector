<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Application;

use PhpParser\Node\Stmt\Class_;
use Rector\NodeTypeResolver\Node\Attribute;

final class ClassNodeCollector
{
    /**
     * @var Class_[]
     */
    private $classes = [];

    public function addClass(string $name, Class_ $classNode): void
    {
        $this->classes[$name] = $classNode;
    }

    public function findClass(string $name): ?Class_
    {
        return $this->classes[$name] ?? null;
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
