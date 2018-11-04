<?php declare(strict_types=1);

namespace Rector\PhpParser\Node\Maintainer;

use PhpParser\Node\Stmt\Class_;
use Rector\PhpParser\Node\Builder\ConstructorMethodBuilder;
use Rector\PhpParser\Node\Builder\PropertyBuilder;
use Rector\PhpParser\Node\Builder\VariableInfo;

final class ClassDependencyMaintainer
{
    /**
     * @var PropertyBuilder
     */
    private $propertyBuilder;

    /**
     * @var ConstructorMethodBuilder
     */
    private $constructorMethodBuilder;

    public function __construct(PropertyBuilder $propertyBuilder, ConstructorMethodBuilder $constructorMethodBuilder)
    {
        $this->propertyBuilder = $propertyBuilder;
        $this->constructorMethodBuilder = $constructorMethodBuilder;
    }

    public function addConstructorDependency(Class_ $classNode, VariableInfo $variableInfo): void
    {
        // add property
        // @todo should be factory
        $this->propertyBuilder->addPropertyToClass($classNode, $variableInfo);

        // pass via constructor
        $this->constructorMethodBuilder->addSimplePropertyAssignToClass($classNode, $variableInfo);
    }
}
