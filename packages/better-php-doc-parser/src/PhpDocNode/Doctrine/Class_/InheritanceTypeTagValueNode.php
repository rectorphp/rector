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
        $this->resolveOriginalContentSpacingAndOrder($originalContent, 'value');
    }

    public function __toString(): string
    {
        if ($this->value === null) {
            return '';
        }

        $items['value'] = $this->value;
        $items = $this->completeItemsQuotes($items);
        $items = $this->makeKeysExplicit($items);

        return $this->printContentItems($items);
    }

    public function getShortName(): string
    {
        return '@ORM\InheritanceType';
    }
}
