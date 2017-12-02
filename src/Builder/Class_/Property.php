<?php declare(strict_types=1);

namespace Rector\Builder\Class_;

final class Property
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
    private function __construct(string $name, array $types)
    {
        $this->name = $name;
        $this->types = $types;
    }

    /**
     * @param string[] $propertyTypes
     */
    public static function createFromNameAndTypes(string $propertyName, array $propertyTypes): self
    {
        return new self($propertyName, $propertyTypes);
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
