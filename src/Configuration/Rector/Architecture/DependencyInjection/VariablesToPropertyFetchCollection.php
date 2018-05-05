<?php declare(strict_types=1);

namespace Rector\Configuration\Rector\Architecture\DependencyInjection;

use Rector\Builder\Class_\VariableInfo;

final class VariablesToPropertyFetchCollection
{
    /**
     * @var VariableInfo[]
     */
    private $variableInfos = [];

    /**
     * @param string[] $types
     */
    public function addVariableInfo(VariableInfo $variableInfo): void
    {
        $this->variableInfos[] = $variableInfo;
    }

    /**
     * @return VariableInfo[]
     */
    public function getVariableInfos(): array
    {
        return $this->variableInfos;
    }
}
