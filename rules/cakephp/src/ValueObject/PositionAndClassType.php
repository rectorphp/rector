<?php

declare(strict_types=1);

namespace Rector\CakePHP\ValueObject;

final class PositionAndClassType
{
    /**
     * @var int
     */
    private $position;

    /**
     * @var string
     */
    private $classType;

    public function __construct(int $position, string $classType)
    {
        $this->position = $position;
        $this->classType = $classType;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function getClassType(): string
    {
        return $this->classType;
    }
}
