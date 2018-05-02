<?php declare(strict_types=1);

namespace Rector\Configuration\Rector;

abstract class AbstractArgumentReplacerRecipe
{
    /**
     * @var string
     */
    protected $class;

    /**
     * @var string
     */
    protected $method;

    /**
     * @var int
     */
    protected $position;

    public function __construct(string $class, string $method, int $position)
    {
        $this->class = $class;
        $this->method = $method;
        $this->position = $position;
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
}
