<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Property_;

use Doctrine\ORM\Mapping\CustomIdGenerator;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\AbstractDoctrineTagValueNode;

final class CustomIdGeneratorTagValueNode extends AbstractDoctrineTagValueNode
{
    /**
     * @var mixed[]
     */
    private $items = [];

    public function __construct(CustomIdGenerator $customIdGenerator, ?string $originalContent = null)
    {
        $this->items = get_object_vars($customIdGenerator);
        $this->resolveOriginalContentSpacingAndOrder($originalContent);
    }

    public function __toString(): string
    {
        $items = $this->completeItemsQuotes($this->items);
        $items = $this->makeKeysExplicit($items);

        return $this->printContentItems($items);
    }

    public function getShortName(): string
    {
        return '@ORM\CustomIdGenerator';
    }
}
