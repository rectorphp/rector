<?php declare(strict_types=1);

namespace Rector\Rector;

use PhpParser\Node\Stmt\Class_;
use Rector\PhpParser\Node\Builder\VariableInfo;
use Rector\PhpParser\Node\Maintainer\ClassMaintainer;

/**
 * This could be part of @see AbstractRector, but decopuling to trait
 * makes clear what code has 1 purpose.
 */
trait ClassMaintainerTrait
{
    /**
     * @var ClassMaintainer
     */
    protected $classMaintainer;

    /**
     * @required
     */
    public function setClassMaintainerDependencies(ClassMaintainer $classMaintainer): void
    {
        $this->classMaintainer = $classMaintainer;
    }

    protected function addConstructorDependency(Class_ $classNode, VariableInfo $variableInfo): void
    {
        $this->classMaintainer->addConstructorDependency($classNode, $variableInfo);
    }

    protected function addPropertyToClass(Class_ $classNode, string $propertyType, string $propertyName): void
    {
        $variableInfo = new VariableInfo($propertyName, $propertyType);
        $this->addConstructorDependency($classNode, $variableInfo);
    }
}
