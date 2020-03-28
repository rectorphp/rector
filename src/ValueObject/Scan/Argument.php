<?php

declare(strict_types=1);

namespace Rector\Core\ValueObject\Scan;

final class Argument
{
    /**
     * @var int
     */
    private $position;

    /**
     * @var string
     */
    private $type;

    public function __construct(int $position, string $type = '')
    {
        $this->position = $position;
        $this->type = $type;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function getType(): string
    {
        return $this->type;
    }
}
