<?php declare(strict_types=1);

namespace Rector\Configuration\Rector;

final class ArgumentReplacerRecipe
{
    /**
     * @var string
     */
    private $class;

    /**
     * @var string
     */
    private $method;

    /**
     * @var int
     */
    private $position;

    /**
     * @var mixed
     */
    private $defaultValue;

    /**
     * @var string[]
     */
    private $replaceMap = [];

    /**
     * @param mixed $defaultValue
     * @param string[] $replaceMap
     */
    public function __construct(
        string $class,
        string $method,
        int $position,
        $defaultValue = null,
        array $replaceMap
    ) {
        $this->class = $class;
        $this->method = $method;
        $this->position = $position;
        $this->defaultValue = $defaultValue;
        $this->replaceMap = $replaceMap;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * @return mixed|null
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * @return string[]
     */
    public function getReplaceMap(): array
    {
        return $this->replaceMap;
    }
}
