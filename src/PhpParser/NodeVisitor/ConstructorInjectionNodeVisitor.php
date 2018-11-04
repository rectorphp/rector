<?php declare(strict_types=1);

namespace Rector\PhpParser\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\NodeVisitorAbstract;
use Rector\PhpParser\Node\Maintainer\ClassMaintainer;
use Rector\PhpParser\Node\Maintainer\Storage\ClassWithPropertiesObjectStorage;

final class ConstructorInjectionNodeVisitor extends NodeVisitorAbstract
{
    /**
     * @var ClassMaintainer
     */
    private $classMaintainer;

    /**
     * @var ClassWithPropertiesObjectStorage
     */
    private $classWithPropertiesObjectStorage;

    public function __construct(
        ClassMaintainer $classMaintainer,
        ClassWithPropertiesObjectStorage $classWithPropertiesObjectStorage
    ) {
        $this->classMaintainer = $classMaintainer;
        $this->classWithPropertiesObjectStorage = $classWithPropertiesObjectStorage;
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
        $propertiesForClass = $this->classWithPropertiesObjectStorage[$classNode] ?? [];
        foreach ($propertiesForClass as $propertyInfo) {
            $this->classMaintainer->addConstructorDependency($classNode, $propertyInfo);
        }

        return $classNode;
    }
}
