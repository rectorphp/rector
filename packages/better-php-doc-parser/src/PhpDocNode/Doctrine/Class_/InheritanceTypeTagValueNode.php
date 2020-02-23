<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Class_;

use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\AbstractDoctrineTagValueNode;

final class InheritanceTypeTagValueNode extends AbstractDoctrineTagValueNode
{
    /**
     * @var string|null
     */
    private $value;

    public function __construct(?string $value, ?string $originalContent)
    {
        $this->value = $value;
        $this->resolveOriginalContentSpacingAndOrder($originalContent);
    }

    public function __toString(): string
    {
        if ($this->value === null) {
            return '';
        }

        if ($this->originalContent && ! in_array('value', (array) $this->orderedVisibleItems, true)) {
            return '("' . $this->value . '")';
        }

        return '(value="' . $this->value . '")';
    }

    public function getShortName(): string
    {
        return '@ORM\InheritanceType';
    }
}
