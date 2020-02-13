<?php

declare(strict_types=1);

namespace Rector\Core\Configuration\Collector;

use PHPStan\Type\Type;

final class VariablesToPropertyFetchCollection
{
    /**
     * @var Type[]
     */
    private $variableNameAndType = [];

    public function addVariableNameAndType(string $name, Type $type): void
    {
        $this->variableNameAndType[$name] = $type;
    }

    /**
     * @return Type[]
     */
    public function getVariableNamesAndTypes(): array
    {
        return $this->variableNameAndType;
    }
}
