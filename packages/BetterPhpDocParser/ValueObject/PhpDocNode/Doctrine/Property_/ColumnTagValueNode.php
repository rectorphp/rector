<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_;

use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\AbstractDoctrineTagValueNode;

final class ColumnTagValueNode extends AbstractDoctrineTagValueNode
{
    public function changeType(string $type): void
    {
        $this->items['type'] = $type;
    }

    public function getType(): ?string
    {
        return $this->items['type'];
    }

    public function isNullable(): ?bool
    {
        return $this->items['nullable'];
    }

    public function getShortName(): string
    {
        return '@ORM\Column';
    }

    /**
     * @return array<string, mixed>
     */
    public function getOptions(): array
    {
        return $this->items['options'] ?? [];
    }
}
