<?php

declare(strict_types=1);

namespace Rector\ValueObject\Scan;

final class Argument
{
    /**
     * @var int
     */
    private $position;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $type;

    public function __construct(string $name, int $position, string $type = '')
    {
        $this->position = $position;
        $this->name = $name;
        $this->type = $type;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }
}
