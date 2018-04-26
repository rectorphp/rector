<?php declare(strict_types=1);

namespace Rector\Rector\Dynamic\Configuration;

final class ArgumentReplacerRecipe
{
    /**
     * @var string
     */
    public const TYPE_REMOVED = 'removed';

    /**
     * @var string
     */
    public const TYPE_CHANGED = 'changed';

    /**
     * @var string
     */
    public const TYPE_REPLACED_DEFAULT_VALUE = 'replace_default_value';

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
     * @var string
     */
    private $type;

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
        string $type,
        $defaultValue = null,
        array $replaceMap
    ) {
        $this->class = $class;
        $this->method = $method;
        $this->position = $position;
        $this->type = $type;
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

    public function getType(): string
    {
        return $this->type;
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
