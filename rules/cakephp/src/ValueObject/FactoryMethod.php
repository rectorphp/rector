<?php

declare(strict_types=1);

namespace Rector\CakePHP\ValueObject;

final class FactoryMethod
{
    /**
     * @var string
     */
    private $type;

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
    private $newClass;

    public function __construct(string $type, string $method, string $newClass, int $position)
    {
        $this->type = $type;
        $this->method = $method;
        $this->position = $position;
        $this->newClass = $newClass;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function getNewClass(): string
    {
        return $this->newClass;
    }
}
