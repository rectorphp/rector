<?php

declare(strict_types=1);

namespace Rector\PostRector\ValueObject;

use PHPStan\Type\Type;

final class PropertyMetadata
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var Type|null
     */
    private $type;

    /**
     * @var int
     */
    private $flags;

    public function __construct(string $name, ?Type $type, int $falgs)
    {
        $this->name = $name;
        $this->type = $type;
        $this->flags = $falgs;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): ?Type
    {
        return $this->type;
    }

    public function getFlags(): int
    {
        return $this->flags;
    }
}
