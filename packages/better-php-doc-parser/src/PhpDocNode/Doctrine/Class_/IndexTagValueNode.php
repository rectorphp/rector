<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Class_;

use Doctrine\ORM\Mapping\Index;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\TagAwareNodeInterface;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\AbstractDoctrineTagValueNode;

final class IndexTagValueNode extends AbstractDoctrineTagValueNode implements TagAwareNodeInterface
{
    /**
     * @var string|null
     */
    private $tag;

    /**
     * @var mixed[]
     */
    private $items = [];

    public function __construct(Index $index, ?string $originalContent = null, ?string $originalTag = null)
    {
        $this->items = get_object_vars($index);

        if ($originalContent !== null) {
            $this->resolveOriginalContentSpacingAndOrder($originalContent);
        }

        $this->tag = $originalTag;
    }

    public function __toString(): string
    {
        $items = $this->completeItemsQuotes($this->items);
        $items = $this->makeKeysExplicit($items);
        return $this->printContentItems($items);
    }

    public function getTag(): ?string
    {
        return $this->tag ?: $this->getShortName();
    }

    public function getShortName(): string
    {
        return '@ORM\Index';
    }
}
