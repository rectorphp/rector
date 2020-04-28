<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Class_;

use Doctrine\ORM\Mapping\InheritanceType;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\AbstractDoctrineTagValueNode;

final class InheritanceTypeTagValueNode extends AbstractDoctrineTagValueNode
{
    /**
     * @var mixed[]
     */
    private $items = [];

    public function __construct(InheritanceType $inheritanceType, ?string $originalContent)
    {
        $this->items = get_object_vars($inheritanceType);

        $this->resolveOriginalContentSpacingAndOrder($originalContent, 'value');
    }

    public function __toString(): string
    {
        $items = $this->completeItemsQuotes($this->items);
        $items = $this->makeKeysExplicit($items);

        return $this->printContentItems($items);
    }

    public function getShortName(): string
    {
        return '@ORM\InheritanceType';
    }
}
