<?php declare(strict_types=1);

namespace Rector\PhpParser\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\NodeVisitorAbstract;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\PhpParser\Node\Builder\ClassPropertyCollector;
use Rector\PhpParser\Node\Maintainer\ClassDependencyMaintainer;

final class ConstructorInjectionNodeVisitor extends NodeVisitorAbstract
{
    /**
     * @var ClassPropertyCollector
     */
    private $classPropertyCollector;

    /**
     * @var ClassDependencyMaintainer
     */
    private $classDependencyMaintainer;

    public function __construct(
        ClassPropertyCollector $classPropertyCollector,
        ClassDependencyMaintainer $classDependencyMaintainer
    ) {
        $this->classPropertyCollector = $classPropertyCollector;
        $this->classDependencyMaintainer = $classDependencyMaintainer;
    }

    public function enterNode(Node $node): ?Node
    {
        if (! $node instanceof Class_ || $node->isAnonymous()) {
            return null;
        }

        return $this->processClassNode($node);
    }

    private function processClassNode(Class_ $classNode): Class_
    {
        $className = (string) $classNode->getAttribute(Attribute::CLASS_NAME);

        $propertiesForClass = $this->classPropertyCollector->getPropertiesForClass($className);
        if (! count($propertiesForClass)) {
            return $classNode;
        }

        foreach ($propertiesForClass as $propertyInfo) {
            $this->classDependencyMaintainer->addConstructorDependency($classNode, $propertyInfo);
        }

        return $classNode;
    }
}
