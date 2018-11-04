<?php declare(strict_types=1);

namespace Rector\Rector;

use PhpParser\Node\Stmt\Class_;
use Rector\PhpParser\Node\Builder\VariableInfo;
use Rector\PhpParser\Node\Maintainer\ClassDependencyMaintainer;

/**
 * This could be part of @see AbstractRector, but decopuling to trait
 * makes clear what code has 1 purpose.
 */
trait ClassDependencyTrait
{
    /**
     * @var ClassDependencyMaintainer
     */
    private $classDependencyMaintainer;

    /**
     * @required
     */
    public function setClassDependencyMaintainer(ClassDependencyMaintainer $classDependencyMaintainer): void
    {
        $this->classDependencyMaintainer = $classDependencyMaintainer;
    }

    protected function addConstructorDependency(Class_ $classNode, VariableInfo $variableInfo): void
    {
        $this->classDependencyMaintainer->addConstructorDependency($classNode, $variableInfo);
    }
}
