<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\Node\Manipulator;

use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\Type;
use Rector\Core\PhpParser\Node\NodeFactory;

final class ClassDependencyManipulator
{
    /**
     * @var ClassManipulator
     */
    private $classManipulator;

    /**
     * @var ClassMethodAssignManipulator
     */
    private $classMethodAssignManipulator;

    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    /**
     * @var ChildAndParentClassManipulator
     */
    private $childAndParentClassManipulator;

    public function __construct(
        ClassManipulator $classManipulator,
        ClassMethodAssignManipulator $classMethodAssignManipulator,
        NodeFactory $nodeFactory,
        ChildAndParentClassManipulator $childAndParentClassManipulator
    ) {
        $this->classManipulator = $classManipulator;
        $this->classMethodAssignManipulator = $classMethodAssignManipulator;
        $this->nodeFactory = $nodeFactory;
        $this->childAndParentClassManipulator = $childAndParentClassManipulator;
    }

    public function addConstructorDependency(Class_ $classNode, string $name, ?Type $type): void
    {
        // add property
        // @todo should be factory
        $this->classManipulator->addPropertyToClass($classNode, $name, $type);

        $propertyAssignNode = $this->nodeFactory->createPropertyAssignment($name);
        $this->addConstructorDependencyWithCustomAssign($classNode, $name, $type, $propertyAssignNode);
    }

    public function addConstructorDependencyWithCustomAssign(
        Class_ $classNode,
        string $name,
        ?Type $type,
        Assign $assign
    ): void {
        $constructorMethod = $classNode->getMethod('__construct');
        /** @var ClassMethod $constructorMethod */
        if ($constructorMethod !== null) {
            $this->classMethodAssignManipulator->addParameterAndAssignToMethod(
                $constructorMethod,
                $name,
                $type,
                $assign
            );
            return;
        }

        $constructorMethod = $this->nodeFactory->createPublicMethod('__construct');

        $this->classMethodAssignManipulator->addParameterAndAssignToMethod($constructorMethod, $name, $type, $assign);

        $this->childAndParentClassManipulator->completeParentConstructor($classNode, $constructorMethod);

        $this->classManipulator->addAsFirstMethod($classNode, $constructorMethod);

        $this->childAndParentClassManipulator->completeChildConstructors($classNode, $constructorMethod);
    }

    public function addSimplePropertyAssignToClass(Class_ $classNode, string $name, ?Type $type): void
    {
        $propertyAssignNode = $this->nodeFactory->createPropertyAssignment($name);
        $this->addConstructorDependencyWithCustomAssign($classNode, $name, $type, $propertyAssignNode);
    }
}
