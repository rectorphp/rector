<?php declare(strict_types=1);

namespace Rector\Configuration\Rector\Architecture\DependencyInjection;

use Rector\PhpParser\Node\Builder\VariableInfo;

final class VariablesToPropertyFetchCollection
{
    /**
     * @var VariableInfo[]
     */
    private $variableInfos = [];

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
