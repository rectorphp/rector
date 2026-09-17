<?php

declare (strict_types=1);
namespace Rector\Symfony\DependencyInjection;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Class_;
use Rector\Naming\Naming\PropertyNaming;
use Rector\NodeManipulator\ClassDependencyManipulator;
use Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser;
use Rector\PhpParser\Node\NodeFactory;
use Rector\PostRector\ValueObject\PropertyMetadata;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
final class ContainerGetToConstructorInjectionReplacer
{
    /**
     * @readonly
     */
    private SimpleCallableNodeTraverser $simpleCallableNodeTraverser;
    /**
     * @readonly
     */
    private \Rector\Symfony\DependencyInjection\ThisGetTypeMatcher $thisGetTypeMatcher;
    /**
     * @readonly
     */
    private PropertyNaming $propertyNaming;
    /**
     * @readonly
     */
    private ClassDependencyManipulator $classDependencyManipulator;
    /**
     * @readonly
     */
    private NodeFactory $nodeFactory;
    public function __construct(SimpleCallableNodeTraverser $simpleCallableNodeTraverser, \Rector\Symfony\DependencyInjection\ThisGetTypeMatcher $thisGetTypeMatcher, PropertyNaming $propertyNaming, ClassDependencyManipulator $classDependencyManipulator, NodeFactory $nodeFactory)
    {
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->thisGetTypeMatcher = $thisGetTypeMatcher;
        $this->propertyNaming = $propertyNaming;
        $this->classDependencyManipulator = $classDependencyManipulator;
        $this->nodeFactory = $nodeFactory;
    }
    /**
     * Turns `$this->get(SomeType::class)` calls into constructor-injected property fetches.
     * Returns true when at least one dependency was injected.
     */
    public function replace(Class_ $class): bool
    {
        $propertyMetadatas = [];
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($class, function (Node $node) use (&$propertyMetadatas): ?Node {
            if (!$node instanceof MethodCall) {
                return null;
            }
            $className = $this->thisGetTypeMatcher->match($node);
            if (!is_string($className)) {
                return null;
            }
            $propertyName = $this->propertyNaming->fqnToVariableName($className);
            $propertyMetadata = new PropertyMetadata($propertyName, new FullyQualifiedObjectType($className));
            $propertyMetadatas[] = $propertyMetadata;
            return $this->nodeFactory->createPropertyFetch('this', $propertyMetadata->getName());
        });
        if ($propertyMetadatas === []) {
            return \false;
        }
        foreach ($propertyMetadatas as $propertyMetadata) {
            $this->classDependencyManipulator->addConstructorDependency($class, $propertyMetadata);
        }
        return \true;
    }
}
