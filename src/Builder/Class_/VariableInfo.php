<?php declare(strict_types=1);

namespace Rector\Builder\Class_;

final class VariableInfo
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string[]
     */
    private $types = [];

    /**
     * @param string[] $types
     */
    public function __construct(string $name, array $types)
    {
        $this->name = $name;
        $this->types = $types;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string[]
     */
    public function getTypes(): array
    {
        return $this->types;
    }
}
