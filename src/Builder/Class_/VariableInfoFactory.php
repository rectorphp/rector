<?php declare(strict_types=1);

namespace Rector\Builder\Class_;

final class VariableInfoFactory
{
    /**
     * @param string[] $types
     */
    public function createFromNameAndTypes(string $name, array $types): VariableInfo
    {
        return new VariableInfo($name, $types);
    }
}
