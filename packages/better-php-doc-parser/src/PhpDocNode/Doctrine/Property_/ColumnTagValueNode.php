<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Property_;

use Doctrine\ORM\Mapping\Column;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\AbstractDoctrineTagValueNode;
use Rector\PhpAttribute\Contract\PhpAttributableTagNodeInterface;
use Rector\PhpAttribute\PhpDocNode\PhpAttributePhpDocNodePrintTrait;

final class ColumnTagValueNode extends AbstractDoctrineTagValueNode implements PhpAttributableTagNodeInterface
{
    use PhpAttributePhpDocNodePrintTrait;

    /**
     * @var mixed[]
     */
    private $items = [];

    public function __construct($items, ?string $originalContent = null)
    {
        $this->items = $items;
        $this->resolveOriginalContentSpacingAndOrder($originalContent);
    }

    public function __toString(): string
    {
        $items = $this->completeItemsQuotes($this->items);
        $items = $this->makeKeysExplicit($items);

        return $this->printContentItems($items);
    }

    public static function fromColumnAndOriginalContent(Column $column, string $originalContent): self
    {
        $items = get_object_vars($column);

        return new self($items, $originalContent);
    }

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

    public function toAttributeString(): string
    {
        $items = $this->filterOutMissingItems($this->items);
        $items = $this->completeItemsQuotes($items);

        foreach ($items as $key => $value) {
            if ($key !== 'unique') {
                continue;
            }

            if ($value !== true) {
                continue;
            }

            $items[$key] = 'ORM\Column::UNIQUE';
        }

        $content = $this->printPhpAttributeItems($items);

        return $this->printAttributeContent($content);
    }
}
