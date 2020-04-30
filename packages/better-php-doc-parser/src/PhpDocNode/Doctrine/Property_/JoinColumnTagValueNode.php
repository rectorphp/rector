<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Property_;

use Doctrine\ORM\Mapping\JoinColumn;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\TagAwareNodeInterface;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\AbstractDoctrineTagValueNode;
use Rector\PhpAttribute\Contract\PhpAttributableTagNodeInterface;
use Rector\PhpAttribute\PhpDocNode\PhpAttributePhpDocNodePrintTrait;

final class JoinColumnTagValueNode extends AbstractDoctrineTagValueNode implements TagAwareNodeInterface, PhpAttributableTagNodeInterface
{
    use PhpAttributePhpDocNodePrintTrait;

    /**
     * @var string|null
     */
    private $tag;

    /**
     * @var string
     */
    private $shortName = '@ORM\JoinColumn';

    /**
     * @var mixed[]
     */
    private $items = [];

    public function __construct(array $items, ?string $originalContent = null, ?string $originalTag = null)
    {
        $this->items = $items;

        $this->resolveOriginalContentSpacingAndOrder($originalContent);
        $this->tag = $originalTag;
    }

    public function __toString(): string
    {
        $items = $this->completeItemsQuotes($this->items);
        $items = $this->makeKeysExplicit($items);

        return $this->printContentItems($items);
    }

    public static function createFromAnnotationAndOriginalContent(
        JoinColumn $joinColumn,
        string $originalContent,
        ?string $originalTag = null
    ) {
        $items = get_object_vars($joinColumn);

        return new self($items, $originalContent, $originalTag);
    }

    public function isNullable(): ?bool
    {
        return $this->items['nullable'];
    }

    public function getTag(): ?string
    {
        return $this->tag ?: $this->shortName;
    }

    public function getUnique(): ?bool
    {
        return $this->items['unique'];
    }

    public function getShortName(): string
    {
        return $this->shortName;
    }

    public function changeShortName(string $shortName): void
    {
        $this->shortName = $shortName;
    }

    public function toAttributeString(): string
    {
        $items = $this->filterOutMissingItems($this->items);
        $items = $this->completeItemsQuotes($items);

        // specific for attributes
        foreach ($items as $key => $value) {
            if ($key !== 'unique') {
                continue;
            }
            if ($value !== true) {
                continue;
            }
            $items[$key] = 'ORM\JoinColumn::UNIQUE';
        }

        $content = $this->printPhpAttributeItems($items);

        return $this->printAttributeContent($content);
    }
}
